<?php

session_start();

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

/*
|--------------------------------------------------------------------------
| SOMENTE POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php?page=perfil");
    exit;
}

/*
|--------------------------------------------------------------------------
| DADOS
|--------------------------------------------------------------------------
*/

$id = (int) $_SESSION["usuario"]["id_usuario"];

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");

/*
|--------------------------------------------------------------------------
| VALIDAR
|--------------------------------------------------------------------------
*/

if ($nome === "" || $email === "") {
    header("Location: ../index.php?page=perfil&erro=campos");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../index.php?page=perfil&erro=email");
    exit;
}

if (mb_strlen($nome) > 150 || mb_strlen($email) > 150) {
    header("Location: ../index.php?page=perfil&erro=campos");
    exit;
}

/*
|--------------------------------------------------------------------------
| CONEXÃO
|--------------------------------------------------------------------------
*/

$db = new Database();
$conn = $db->connect();

try {

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR E-MAIL DUPLICADO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT id_usuario
        FROM usuarios
        WHERE email = ?
          AND id_usuario <> ?
        LIMIT 1
    ");

    $sql->execute([
        $email,
        $id
    ]);

    if ($sql->fetch()) {
        header("Location: ../index.php?page=perfil&erro=email_existente");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        UPDATE usuarios
        SET
            nome = ?,
            email = ?
        WHERE id_usuario = ?
    ");

    $sql->execute([
        $nome,
        $email,
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR SESSÃO
    |--------------------------------------------------------------------------
    */

    $_SESSION["usuario"]["nome"] = $nome;
    $_SESSION["usuario"]["email"] = $email;

    header("Location: ../index.php?page=perfil&sucesso=1");
    exit;

} catch (PDOException $e) {

    header("Location: ../index.php?page=perfil&erro=banco");
    exit;
}