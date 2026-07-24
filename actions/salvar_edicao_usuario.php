<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| VERIFICAR LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["usuario"]) ||
    empty($_SESSION["usuario"]["id_usuario"])
) {
    header("Location: ../login.php");
    exit;
}

$usuarioLogado = $_SESSION["usuario"];

$idLogado    = (int)$usuarioLogado["id_usuario"];
$nivelLogado = $usuarioLogado["nivel"] ?? "";

/*
|--------------------------------------------------------------------------
| PERMISSÃO
|--------------------------------------------------------------------------
*/

if (!in_array(
    $nivelLogado,
    ["Administrador Geral", "Administrador"],
    true
)) {
    header("Location: ../index.php?page=usuarios&erro=permissao");
    exit;
}

/*
|--------------------------------------------------------------------------
| ACEITAR SOMENTE POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php?page=usuarios");
    exit;
}

/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$idUsuario = filter_input(
    INPUT_POST,
    "id_usuario",
    FILTER_VALIDATE_INT
);

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$nivel = trim($_POST["nivel"] ?? "");

$ativo = isset($_POST["ativo"])
    ? (int)$_POST["ativo"]
    : 1;

/*
|--------------------------------------------------------------------------
| VALIDAR DADOS
|--------------------------------------------------------------------------
*/

if (
    !$idUsuario ||
    $nome === "" ||
    $email === "" ||
    $nivel === ""
) {
    header("Location: ../index.php?page=usuarios&erro=dados");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../index.php?page=usuarios&erro=email");
    exit;
}

if (!in_array($ativo, [0, 1], true)) {
    $ativo = 1;
}

/*
|--------------------------------------------------------------------------
| NÍVEIS PERMITIDOS
|--------------------------------------------------------------------------
*/

$niveisPermitidos = [
    "Administrador",
    "Recepção",
    "Funilaria",
    "Pintura",
    "Financeiro"
];

if ($nivelLogado === "Administrador Geral") {
    $niveisPermitidos[] = "Administrador Geral";
}

if (!in_array($nivel, $niveisPermitidos, true)) {
    header("Location: ../index.php?page=usuarios&erro=permissao");
    exit;
}

/*
|--------------------------------------------------------------------------
| CONEXÃO
|--------------------------------------------------------------------------
*/

try {

    $db = new Database();
    $conn = $db->connect();

    /*
    |--------------------------------------------------------------------------
    | BUSCAR USUÁRIO QUE SERÁ EDITADO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT
            id_usuario,
            nome,
            email,
            nivel,
            ativo
        FROM usuarios
        WHERE id_usuario = ?
        LIMIT 1
    ");

    $sql->execute([$idUsuario]);

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: ../index.php?page=usuarios&erro=usuario");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PROTEGER ADMINISTRADOR GERAL
    |--------------------------------------------------------------------------
    */

    if (
        $nivelLogado !== "Administrador Geral" &&
        $usuario["nivel"] === "Administrador Geral"
    ) {
        header("Location: ../index.php?page=usuarios&erro=protegido");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | NÃO DESATIVAR A PRÓPRIA CONTA
    |--------------------------------------------------------------------------
    */

    if ($idUsuario === $idLogado) {
        $ativo = 1;
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR E-MAIL DUPLICADO
    |--------------------------------------------------------------------------
    */

    $sqlEmail = $conn->prepare("
        SELECT id_usuario
        FROM usuarios
        WHERE email = ?
        AND id_usuario <> ?
        LIMIT 1
    ");

    $sqlEmail->execute([
        $email,
        $idUsuario
    ]);

    if ($sqlEmail->fetch()) {
        header("Location: ../index.php?page=usuarios&erro=email_existente");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR
    |--------------------------------------------------------------------------
    */

    $sqlUpdate = $conn->prepare("
        UPDATE usuarios
        SET
            nome = ?,
            email = ?,
            nivel = ?,
            ativo = ?
        WHERE id_usuario = ?
    ");

    $sqlUpdate->execute([
        $nome,
        $email,
        $nivel,
        $ativo,
        $idUsuario
    ]);

    /*
    |--------------------------------------------------------------------------
    | SE EDITOU A PRÓPRIA CONTA, ATUALIZAR A SESSÃO
    |--------------------------------------------------------------------------
    */

    if ($idUsuario === $idLogado) {

        $_SESSION["usuario"]["nome"] = $nome;
        $_SESSION["usuario"]["email"] = $email;
        $_SESSION["usuario"]["nivel"] = $nivel;
    }

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../index.php?page=usuarios&sucesso=editado"
    );

    exit;

} catch (PDOException $e) {

    header(
        "Location: ../index.php?page=usuarios&erro=banco"
    );

    exit;
}