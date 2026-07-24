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

$nivelLogado = $_SESSION["usuario"]["nivel"] ?? "";

/*
|--------------------------------------------------------------------------
| SOMENTE ADMINISTRADORES
|--------------------------------------------------------------------------
*/

if (
    $nivelLogado !== "Administrador Geral" &&
    $nivelLogado !== "Administrador"
) {
    header("Location: ../index.php?page=dashboard&erro=acesso");
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
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";
$nivel = trim($_POST["nivel"] ?? "");

/*
|--------------------------------------------------------------------------
| CAMPOS OBRIGATÓRIOS
|--------------------------------------------------------------------------
*/

if (
    $nome === "" ||
    $email === "" ||
    $senha === "" ||
    $nivel === ""
) {
    header("Location: ../index.php?page=usuarios&erro=campos");
    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDAR E-MAIL
|--------------------------------------------------------------------------
*/

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../index.php?page=usuarios&erro=email");
    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDAR SENHA
|--------------------------------------------------------------------------
*/

if (strlen($senha) < 8) {
    header("Location: ../index.php?page=usuarios&erro=senha_curta");
    exit;
}

/*
|--------------------------------------------------------------------------
| NÍVEIS PERMITIDOS
|--------------------------------------------------------------------------
*/

$niveisPermitidos = [
    "Administrador Geral",
    "Administrador",
    "Recepção",
    "Funilaria",
    "Pintura",
    "Financeiro"
];

if (!in_array($nivel, $niveisPermitidos, true)) {
    header("Location: ../index.php?page=usuarios&erro=nivel");
    exit;
}

/*
|--------------------------------------------------------------------------
| REGRA DO ADMINISTRADOR GERAL
|--------------------------------------------------------------------------
|
| Administrador comum NÃO pode criar outro Administrador Geral.
|
*/

if (
    $nivel === "Administrador Geral" &&
    $nivelLogado !== "Administrador Geral"
) {
    header("Location: ../index.php?page=usuarios&erro=permissao");
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
    | E-MAIL JÁ CADASTRADO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT id_usuario
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    $sql->execute([$email]);

    if ($sql->fetch()) {
        header("Location: ../index.php?page=usuarios&erro=email_existente");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CRIAR HASH DA SENHA
    |--------------------------------------------------------------------------
    */

    $senhaHash = password_hash(
        $senha,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | CADASTRAR USUÁRIO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        INSERT INTO usuarios
        (
            nome,
            email,
            senha,
            nivel,
            ativo
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            1
        )
    ");

    $sql->execute([
        $nome,
        $email,
        $senhaHash,
        $nivel
    ]);

    header("Location: ../index.php?page=usuarios&sucesso=cadastrado");
    exit;

} catch (PDOException $e) {

    header("Location: ../index.php?page=usuarios&erro=banco");
    exit;
}