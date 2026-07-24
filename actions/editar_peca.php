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
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$id = (int)($_POST["id"] ?? 0);

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

/*
|--------------------------------------------------------------------------
| VALIDAR ID
|--------------------------------------------------------------------------
*/

if ($id <= 0) {
    header("Location: ../index.php?page=pecas&erro=id");
    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDAR CAMPOS
|--------------------------------------------------------------------------
*/

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
| BUSCAR PEÇA ATUAL
|--------------------------------------------------------------------------
*/

$sqlAtual = $conn->prepare("
    SELECT *
    FROM pecas
    WHERE id_peca = ?
    LIMIT 1
");

$sqlAtual->execute([$id]);

$pecaAtual = $sqlAtual->fetch(PDO::FETCH_ASSOC);

if (!$pecaAtual) {
    header("Location: ../index.php?page=pecas&erro=nao_encontrada");
    exit;
}

/*
|--------------------------------------------------------------------------
| FOTO ATUAL
|--------------------------------------------------------------------------
*/

$fotoAntiga = $pecaAtual["foto"] ?? "";
$fotoNova = $fotoAntiga;

/*
|--------------------------------------------------------------------------
| VERIFICAR CÓDIGO DUPLICADO
|--------------------------------------------------------------------------
*/

$verificar = $conn->prepare("
    SELECT id_peca
    FROM pecas
    WHERE codigo = ?
    AND id_peca <> ?
    LIMIT 1
");

$verificar->execute([
    $codigo,
    $id
]);

if ($verificar->fetch()) {
    header("Location: ../index.php?page=pecas&erro=codigo");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPLOAD DE NOVA FOTO
|--------------------------------------------------------------------------
*/

if (
    isset($_FILES["foto"]) &&
    $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR ERRO DE UPLOAD
    |--------------------------------------------------------------------------
    */

    if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        header("Location: ../index.php?page=pecas&erro=foto");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | LIMITE 5 MB
    |--------------------------------------------------------------------------
    */

    $limite = 5 * 1024 * 1024;

    if ($_FILES["foto"]["size"] > $limite) {
        header("Location: ../index.php?page=pecas&erro=tamanho_foto");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR TIPO REAL
    |--------------------------------------------------------------------------
    */

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->file(
        $_FILES["foto"]["tmp_name"]
    );

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
    | DIRETÓRIO
    |--------------------------------------------------------------------------
    */

    $diretorio = "../uploads/pecas/";

    if (!is_dir($diretorio)) {

        if (!mkdir($diretorio, 0755, true)) {
            header("Location: ../index.php?page=pecas&erro=pasta");
            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GERAR NOME SEGURO
    |--------------------------------------------------------------------------
    */

    $extensao = $tiposPermitidos[$mime];

    try {

        $nomeArquivo = bin2hex(
            random_bytes(16)
        );

    } catch (Exception $e) {

        $nomeArquivo = uniqid(
            "peca_",
            true
        );
    }

    $fotoNova = $nomeArquivo . "." . $extensao;

    $destino = $diretorio . $fotoNova;

    /*
    |--------------------------------------------------------------------------
    | ENVIAR NOVA FOTO
    |--------------------------------------------------------------------------
    */

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
| ATUALIZAR PEÇA
|--------------------------------------------------------------------------
*/

try {

    $sql = $conn->prepare("
        UPDATE pecas
        SET
            codigo = ?,
            peca = ?,
            marca = ?,
            fabricante = ?,
            codigo_barras = ?,
            estoque = ?,
            estoque_minimo = ?,
            preco_compra = ?,
            venda = ?,
            localizacao = ?,
            descricao = ?,
            foto = ?
        WHERE id_peca = ?
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
        $fotoNova !== "" ? $fotoNova : null,
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | APAGAR FOTO ANTIGA SOMENTE DEPOIS DO UPDATE
    |--------------------------------------------------------------------------
    */

    if (
        $fotoNova !== $fotoAntiga &&
        !empty($fotoAntiga)
    ) {

        $arquivoAntigo = "../uploads/pecas/" . basename($fotoAntiga);

        if (is_file($arquivoAntigo)) {
            unlink($arquivoAntigo);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../index.php?page=pecas&sucesso=editado"
    );

    exit;

} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | SE O UPDATE FALHAR, APAGAR SOMENTE A NOVA FOTO
    |--------------------------------------------------------------------------
    */

    if (
        $fotoNova !== $fotoAntiga &&
        !empty($fotoNova)
    ) {

        $arquivoNovo = "../uploads/pecas/" . basename($fotoNova);

        if (is_file($arquivoNovo)) {
            unlink($arquivoNovo);
        }
    }

    header(
        "Location: ../index.php?page=pecas&erro=banco"
    );

    exit;
}