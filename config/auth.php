<?php

/*
|--------------------------------------------------------------------------
| GP MANAGER - AUTENTICAÇÃO E PERMISSÕES
|--------------------------------------------------------------------------
|
| Centraliza a proteção dos arquivos internos e actions do sistema.
|
*/

/*
|--------------------------------------------------------------------------
| INICIAR SESSÃO
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| NÍVEIS VÁLIDOS
|--------------------------------------------------------------------------
*/

const NIVEIS_GP_MANAGER = [

    "Administrador Geral",
    "Administrador",
    "Recepção",
    "Funilaria",
    "Pintura",
    "Financeiro"

];

/*
|--------------------------------------------------------------------------
| VERIFICAR SE ESTÁ LOGADO
|--------------------------------------------------------------------------
*/

function usuarioEstaLogado(): bool
{
    return (
        isset($_SESSION["usuario"]) &&
        is_array($_SESSION["usuario"]) &&
        !empty($_SESSION["usuario"]["id_usuario"])
    );
}

/*
|--------------------------------------------------------------------------
| EXIGIR LOGIN
|--------------------------------------------------------------------------
*/

function exigirLogin(): void
{
    if (!usuarioEstaLogado()) {

        header("Location: ../login.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR NÍVEL DA SESSÃO
    |--------------------------------------------------------------------------
    */

    $nivel = $_SESSION["usuario"]["nivel"] ?? "";

    if (!in_array($nivel, NIVEIS_GP_MANAGER, true)) {

        encerrarSessao();

        header("Location: ../login.php?erro=sessao");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| OBTER USUÁRIO LOGADO
|--------------------------------------------------------------------------
*/

function usuarioLogado(): array
{
    exigirLogin();

    return $_SESSION["usuario"];
}

/*
|--------------------------------------------------------------------------
| ID DO USUÁRIO
|--------------------------------------------------------------------------
*/

function idUsuarioLogado(): int
{
    return (int)(
        $_SESSION["usuario"]["id_usuario"] ?? 0
    );
}

/*
|--------------------------------------------------------------------------
| NOME DO USUÁRIO
|--------------------------------------------------------------------------
*/

function nomeUsuarioLogado(): string
{
    return trim(
        (string)(
            $_SESSION["usuario"]["nome"] ?? "Usuário"
        )
    );
}

/*
|--------------------------------------------------------------------------
| NÍVEL DO USUÁRIO
|--------------------------------------------------------------------------
*/

function nivelUsuarioLogado(): string
{
    return trim(
        (string)(
            $_SESSION["usuario"]["nivel"] ?? ""
        )
    );
}

/*
|--------------------------------------------------------------------------
| VERIFICAR NÍVEL
|--------------------------------------------------------------------------
*/

function usuarioTemNivel(array $niveis): bool
{
    if (!usuarioEstaLogado()) {
        return false;
    }

    return in_array(
        nivelUsuarioLogado(),
        $niveis,
        true
    );
}

/*
|--------------------------------------------------------------------------
| EXIGIR NÍVEL
|--------------------------------------------------------------------------
|
| Exemplo:
|
| exigirNivel([
|     "Administrador Geral",
|     "Administrador"
| ]);
|
*/

function exigirNivel(array $niveis): void
{
    exigirLogin();

    if (!usuarioTemNivel($niveis)) {

        header(
            "Location: ../index.php?page=dashboard&erro=acesso"
        );

        exit;
    }
}

/*
|--------------------------------------------------------------------------
| ADMINISTRADOR GERAL
|--------------------------------------------------------------------------
*/

function ehAdministradorGeral(): bool
{
    return nivelUsuarioLogado()
        === "Administrador Geral";
}

/*
|--------------------------------------------------------------------------
| ADMINISTRADOR
|--------------------------------------------------------------------------
*/

function ehAdministrador(): bool
{
    return usuarioTemNivel([
        "Administrador Geral",
        "Administrador"
    ]);
}

/*
|--------------------------------------------------------------------------
| RECEPÇÃO / ADMINISTRAÇÃO
|--------------------------------------------------------------------------
*/

function podeAcessarRecepcao(): bool
{
    return usuarioTemNivel([
        "Administrador Geral",
        "Administrador",
        "Recepção"
    ]);
}

/*
|--------------------------------------------------------------------------
| OPERAÇÃO
|--------------------------------------------------------------------------
*/

function podeAcessarOperacao(): bool
{
    return usuarioTemNivel([
        "Administrador Geral",
        "Administrador",
        "Funilaria",
        "Pintura"
    ]);
}

/*
|--------------------------------------------------------------------------
| FINANCEIRO
|--------------------------------------------------------------------------
*/

function podeAcessarFinanceiro(): bool
{
    return usuarioTemNivel([
        "Administrador Geral",
        "Administrador",
        "Financeiro"
    ]);
}

/*
|--------------------------------------------------------------------------
| EXIGIR MÉTODO POST
|--------------------------------------------------------------------------
|
| Para actions que alteram dados.
|
*/

function exigirPost(): void
{
    if (
        ($_SERVER["REQUEST_METHOD"] ?? "") !== "POST"
    ) {

        http_response_code(405);

        exit("Método não permitido.");
    }
}

/*
|--------------------------------------------------------------------------
| REDIRECIONAMENTO
|--------------------------------------------------------------------------
*/

function redirecionar(
    string $pagina,
    array $parametros = []
): never {

    $url = "../index.php?page=" .
        rawurlencode($pagina);

    if (!empty($parametros)) {

        $url .= "&" .
            http_build_query($parametros);
    }

    header("Location: " . $url);

    exit;
}

/*
|--------------------------------------------------------------------------
| ENCERRAR SESSÃO
|--------------------------------------------------------------------------
*/

function encerrarSessao(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {

        $params =
            session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    if (
        session_status()
        === PHP_SESSION_ACTIVE
    ) {

        session_destroy();
    }
}