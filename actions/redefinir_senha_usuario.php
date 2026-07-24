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

$idLogado = (int)$_SESSION["usuario"]["id_usuario"];
$nivelLogado = $_SESSION["usuario"]["nivel"] ?? "";

/*
|--------------------------------------------------------------------------
| SOMENTE ADMINISTRADORES
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
| SOMENTE POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php?page=usuarios");
    exit;
}

/*
|--------------------------------------------------------------------------
| DADOS
|--------------------------------------------------------------------------
*/

$idUsuario = filter_input(
    INPUT_POST,
    "id_usuario",
    FILTER_VALIDATE_INT
);

$novaSenha = $_POST["nova_senha"] ?? "";
$confirmarSenha = $_POST["confirmar_senha"] ?? "";

/*
|--------------------------------------------------------------------------
| VALIDAÇÕES
|--------------------------------------------------------------------------
*/

if (!$idUsuario) {
    header("Location: ../index.php?page=usuarios&erro=usuario");
    exit;
}

if (strlen($novaSenha) < 8) {
    header("Location: ../index.php?page=usuarios&erro=senha_curta");
    exit;
}

if ($novaSenha !== $confirmarSenha) {
    header("Location: ../index.php?page=usuarios&erro=senhas_diferentes");
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
    | BUSCAR USUÁRIO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT
            id_usuario,
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
    |
    | Administrador comum não pode redefinir a senha
    | de um Administrador Geral.
    |
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
    | GERAR HASH
    |--------------------------------------------------------------------------
    */

    $senhaHash = password_hash(
        $novaSenha,
        PASSWORD_DEFAULT
    );

    if ($senhaHash === false) {
        header("Location: ../index.php?page=usuarios&erro=senha");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR SENHA
    |--------------------------------------------------------------------------
    */

    $update = $conn->prepare("
        UPDATE usuarios
        SET senha = ?
        WHERE id_usuario = ?
    ");

    $update->execute([
        $senhaHash,
        $idUsuario
    ]);

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../index.php?page=usuarios&sucesso=senha"
    );

    exit;

} catch (PDOException $e) {

    header(
        "Location: ../index.php?page=usuarios&erro=banco"
    );

    exit;
}