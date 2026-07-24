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
    header("Location: ../index.php?page=dashboard&erro=acesso");
    exit;
}

/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$idUsuario = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

$acao = trim($_GET["acao"] ?? "");

/*
|--------------------------------------------------------------------------
| VALIDAR DADOS
|--------------------------------------------------------------------------
*/

if (!$idUsuario) {
    header("Location: ../index.php?page=usuarios&erro=usuario");
    exit;
}

if (!in_array(
    $acao,
    ["ativar", "desativar"],
    true
)) {
    header("Location: ../index.php?page=usuarios&erro=acao");
    exit;
}

/*
|--------------------------------------------------------------------------
| NÃO DESATIVAR A PRÓPRIA CONTA
|--------------------------------------------------------------------------
*/

if (
    $idUsuario === $idLogado &&
    $acao === "desativar"
) {
    header("Location: ../index.php?page=usuarios&erro=proprio_usuario");
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
            nome,
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
    | Administrador comum não pode ativar/desativar
    | um Administrador Geral.
    |
    */

    if (
        $usuario["nivel"] === "Administrador Geral" &&
        $nivelLogado !== "Administrador Geral"
    ) {
        header("Location: ../index.php?page=usuarios&erro=protegido");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINIR NOVO STATUS
    |--------------------------------------------------------------------------
    */

    $novoStatus = ($acao === "ativar") ? 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | EVITAR ALTERAÇÃO DESNECESSÁRIA
    |--------------------------------------------------------------------------
    */

    if ((int)$usuario["ativo"] === $novoStatus) {

        header("Location: ../index.php?page=usuarios");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | ALTERAR STATUS
    |--------------------------------------------------------------------------
    */

    $update = $conn->prepare("
        UPDATE usuarios
        SET ativo = ?
        WHERE id_usuario = ?
    ");

    $update->execute([
        $novoStatus,
        $idUsuario
    ]);

    /*
    |--------------------------------------------------------------------------
    | REDIRECIONAR
    |--------------------------------------------------------------------------
    */

    $resultado = $novoStatus === 1
        ? "ativado"
        : "desativado";

    header(
        "Location: ../index.php?page=usuarios&sucesso=" . $resultado
    );

    exit;

} catch (PDOException $e) {

    header(
        "Location: ../index.php?page=usuarios&erro=banco"
    );

    exit;
}