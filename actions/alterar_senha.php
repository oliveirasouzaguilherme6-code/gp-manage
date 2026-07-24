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
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$id = (int) $_SESSION["usuario"]["id_usuario"];

$senhaAtual = $_POST["senha_atual"] ?? "";
$novaSenha = $_POST["nova_senha"] ?? "";
$confirmarSenha = $_POST["confirmar_senha"] ?? "";

/*
|--------------------------------------------------------------------------
| VALIDAR CAMPOS
|--------------------------------------------------------------------------
*/

if (
    $senhaAtual === "" ||
    $novaSenha === "" ||
    $confirmarSenha === ""
) {
    header("Location: ../index.php?page=perfil&erro=campos");
    exit;
}

/*
|--------------------------------------------------------------------------
| CONFIRMAR NOVA SENHA
|--------------------------------------------------------------------------
*/

if ($novaSenha !== $confirmarSenha) {
    header("Location: ../index.php?page=perfil&erro=senhas_diferentes");
    exit;
}

/*
|--------------------------------------------------------------------------
| TAMANHO MÍNIMO
|--------------------------------------------------------------------------
*/

if (strlen($novaSenha) < 8) {
    header("Location: ../index.php?page=perfil&erro=senha_curta");
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
    | BUSCAR SENHA ATUAL
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT senha
        FROM usuarios
        WHERE id_usuario = ?
        LIMIT 1
    ");

    $sql->execute([$id]);

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header("Location: ../login.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CONFERIR SENHA ATUAL
    |--------------------------------------------------------------------------
    */

    if (!password_verify(
        $senhaAtual,
        $usuario["senha"]
    )) {
        header("Location: ../index.php?page=perfil&erro=senha_atual");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | NÃO ACEITAR A MESMA SENHA
    |--------------------------------------------------------------------------
    */

    if (password_verify(
        $novaSenha,
        $usuario["senha"]
    )) {
        header("Location: ../index.php?page=perfil&erro=mesma_senha");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | GERAR NOVO HASH
    |--------------------------------------------------------------------------
    */

    $novaSenhaHash = password_hash(
        $novaSenha,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        UPDATE usuarios
        SET senha = ?
        WHERE id_usuario = ?
    ");

    $sql->execute([
        $novaSenhaHash,
        $id
    ]);

    header("Location: ../index.php?page=perfil&senha=1");
    exit;

} catch (PDOException $e) {

    header("Location: ../index.php?page=perfil&erro=banco");
    exit;
}