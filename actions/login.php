<?php

/*
|--------------------------------------------------------------------------
| GP MANAGER - PROCESSAMENTO DE LOGIN
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";

/*
|--------------------------------------------------------------------------
| ACEITAR SOMENTE POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$email = trim((string)($_POST["email"] ?? ""));
$senha = (string)($_POST["senha"] ?? "");

/*
|--------------------------------------------------------------------------
| VALIDAR CAMPOS
|--------------------------------------------------------------------------
*/

if ($email === "" || $senha === "") {

    header("Location: ../login.php?erro=campos");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header("Location: ../login.php?erro=email");
    exit;
}

/*
|--------------------------------------------------------------------------
| CONEXÃO COM BANCO
|--------------------------------------------------------------------------
*/

try {

    $db = new Database();
    $conn = $db->connect();

    /*
    |--------------------------------------------------------------------------
    | BUSCAR USUÁRIO PELO E-MAIL
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT
            id_usuario,
            nome,
            email,
            senha,
            nivel,
            foto,
            ativo
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    $sql->execute([$email]);

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | USUÁRIO NÃO ENCONTRADO
    |--------------------------------------------------------------------------
    */

    if (!$usuario) {

        header("Location: ../login.php?erro=login");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE ESTÁ ATIVO
    |--------------------------------------------------------------------------
    */

    if ((int)$usuario["ativo"] !== 1) {

        header("Location: ../login.php?erro=inativo");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR SENHA
    |--------------------------------------------------------------------------
    */

    $senhaBanco = (string)($usuario["senha"] ?? "");

    if (
        $senhaBanco === "" ||
        !password_verify($senha, $senhaBanco)
    ) {

        header("Location: ../login.php?erro=login");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR NÍVEL
    |--------------------------------------------------------------------------
    */

    $niveisValidos = [
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura",
        "Financeiro"
    ];

    if (!in_array($usuario["nivel"], $niveisValidos, true)) {

        header("Location: ../login.php?erro=nivel");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR HASH DA SENHA SE NECESSÁRIO
    |--------------------------------------------------------------------------
    */

    if (
        password_needs_rehash(
            $senhaBanco,
            PASSWORD_DEFAULT
        )
    ) {

        $novoHash = password_hash(
            $senha,
            PASSWORD_DEFAULT
        );

        $update = $conn->prepare("
            UPDATE usuarios
            SET senha = ?
            WHERE id_usuario = ?
        ");

        $update->execute([
            $novoHash,
            (int)$usuario["id_usuario"]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | REGENERAR ID DA SESSÃO
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    /*
    |--------------------------------------------------------------------------
    | CRIAR SESSÃO DO USUÁRIO
    |--------------------------------------------------------------------------
    */

    $_SESSION["usuario"] = [

        "id_usuario" => (int)$usuario["id_usuario"],

        "nome" => (string)$usuario["nome"],

        "email" => (string)$usuario["email"],

        "nivel" => (string)$usuario["nivel"],

        "foto" => (string)($usuario["foto"] ?? "")

    ];

    /*
    |--------------------------------------------------------------------------
    | REDIRECIONAR PARA O DASHBOARD
    |--------------------------------------------------------------------------
    */

    header("Location: ../index.php?page=dashboard");
    exit;

} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | ERRO DE BANCO
    |--------------------------------------------------------------------------
    */

    header("Location: ../login.php?erro=banco");
    exit;
}