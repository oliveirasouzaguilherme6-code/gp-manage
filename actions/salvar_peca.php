<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| ACEITAR SOMENTE POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php?page=pecas");
    exit;
}

/*
|--------------------------------------------------------------------------
| RECEBER E VALIDAR DADOS
|--------------------------------------------------------------------------
*/

$codigo = trim($_POST["codigo"] ?? "");
$peca = trim($_POST["peca"] ?? "");
$marca = trim($_POST["marca"] ?? "");
$fabricante = trim($_POST["fabricante"] ?? "");
$codigoBarras = trim($_POST["codigo_barras"] ?? "");

$estoque = max(0, (int)($_POST["estoque"] ?? 0));
$estoqueMinimo = max(0, (int)($_POST["estoque_minimo"] ?? 0));

$precoCompra = (float)($_POST["preco_compra"] ?? 0);
$venda = (float)($_POST["venda"] ?? 0);

$localizacao = trim($_POST["localizacao"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");

if ($codigo === "" || $peca === "") {
    header("Location: ../index.php?page=pecas&erro=campos");
    exit;
}

if ($precoCompra < 0 || $venda < 0) {
    header("Location: ../index.php?page=pecas&erro=valor");
    exit;
}

/*
|--------------------------------------------------------------------------
| VERIFICAR CÓDIGO DUPLICADO
|--------------------------------------------------------------------------
*/

$verificar = $conn->prepare("
    SELECT id_peca
    FROM pecas
    WHERE codigo = ?
    LIMIT 1
");

$verificar->execute([$codigo]);

if ($verificar->fetch()) {
    header("Location: ../index.php?page=pecas&erro=codigo");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPLOAD DA FOTO
|--------------------------------------------------------------------------
*/

$foto = "";

if (
    isset($_FILES["foto"]) &&
    $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        header("Location: ../index.php?page=pecas&erro=foto");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | LIMITE DE 5 MB
    |--------------------------------------------------------------------------
    */

    $limite = 5 * 1024 * 1024;

    if ($_FILES["foto"]["size"] > $limite) {
        header("Location: ../index.php?page=pecas&erro=tamanho_foto");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR TIPO REAL DO ARQUIVO
    |--------------------------------------------------------------------------
    */

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->file($_FILES["foto"]["tmp_name"]);

    $tiposPermitidos = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($tiposPermitidos[$mime])) {
        header("Location: ../index.php?page=pecas&erro=tipo_foto");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CRIAR PASTA CASO NÃO EXISTA
    |--------------------------------------------------------------------------
    */

    $diretorio = "../uploads/pecas/";

    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0755, true);
    }

    /*
    |--------------------------------------------------------------------------
    | GERAR NOME SEGURO
    |--------------------------------------------------------------------------
    */

    $extensao = $tiposPermitidos[$mime];

    try {
        $nomeArquivo = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $nomeArquivo = uniqid("peca_", true);
    }

    $foto = $nomeArquivo . "." . $extensao;

    $destino = $diretorio . $foto;

    if (!move_uploaded_file(
        $_FILES["foto"]["tmp_name"],
        $destino
    )) {

        header("Location: ../index.php?page=pecas&erro=upload");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CADASTRAR PEÇA
|--------------------------------------------------------------------------
*/

try {

    $sql = $conn->prepare("
        INSERT INTO pecas (
            codigo,
            peca,
            marca,
            fabricante,
            codigo_barras,
            estoque,
            estoque_minimo,
            preco_compra,
            venda,
            localizacao,
            descricao,
            foto
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $sql->execute([
        $codigo,
        $peca,
        $marca !== "" ? $marca : null,
        $fabricante !== "" ? $fabricante : null,
        $codigoBarras !== "" ? $codigoBarras : null,
        $estoque,
        $estoqueMinimo,
        $precoCompra,
        $venda,
        $localizacao !== "" ? $localizacao : null,
        $descricao !== "" ? $descricao : null,
        $foto !== "" ? $foto : null
    ]);

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    header("Location: ../index.php?page=pecas&sucesso=1");
    exit;

} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | REMOVER FOTO CASO O INSERT FALHE
    |--------------------------------------------------------------------------
    */

    if ($foto !== "") {

        $arquivo = "../uploads/pecas/" . $foto;

        if (file_exists($arquivo)) {
            unlink($arquivo);
        }
    }

    header("Location: ../index.php?page=pecas&erro=banco");
    exit;
}