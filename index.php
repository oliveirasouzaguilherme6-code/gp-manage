<?php

/*
|--------------------------------------------------------------------------
| GP MANAGER - CONTROLADOR PRINCIPAL
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["usuario"]) ||
    !is_array($_SESSION["usuario"]) ||
    empty($_SESSION["usuario"]["id_usuario"])
) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| DADOS DO USUÁRIO
|--------------------------------------------------------------------------
*/

$usuarioLogado = $_SESSION["usuario"];

$idUsuario = (int)($usuarioLogado["id_usuario"] ?? 0);
$nomeUsuario = trim((string)($usuarioLogado["nome"] ?? "Usuário"));
$emailUsuario = trim((string)($usuarioLogado["email"] ?? ""));
$nivelUsuario = trim((string)($usuarioLogado["nivel"] ?? ""));
$fotoUsuario = trim((string)($usuarioLogado["foto"] ?? ""));

/*
|--------------------------------------------------------------------------
| NÍVEIS DO SISTEMA
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

/*
|--------------------------------------------------------------------------
| VALIDAR SESSÃO
|--------------------------------------------------------------------------
*/

if (
    $idUsuario <= 0 ||
    !in_array($nivelUsuario, $niveisValidos, true)
) {

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {

        $params = session_get_cookie_params();

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

    session_destroy();

    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| IDENTIFICADORES DE PERMISSÃO
|--------------------------------------------------------------------------
*/

$ehAdministradorGeral =
    $nivelUsuario === "Administrador Geral";

$ehAdministrador =
    in_array(
        $nivelUsuario,
        [
            "Administrador Geral",
            "Administrador"
        ],
        true
    );

$ehRecepcao =
    in_array(
        $nivelUsuario,
        [
            "Administrador Geral",
            "Administrador",
            "Recepção"
        ],
        true
    );

$ehOperacional =
    in_array(
        $nivelUsuario,
        [
            "Administrador Geral",
            "Administrador",
            "Funilaria",
            "Pintura"
        ],
        true
    );

$ehFinanceiro =
    in_array(
        $nivelUsuario,
        [
            "Administrador Geral",
            "Administrador",
            "Financeiro"
        ],
        true
    );

/*
|--------------------------------------------------------------------------
| PÁGINA SOLICITADA
|--------------------------------------------------------------------------
*/

$page = trim(
    (string)($_GET["page"] ?? "dashboard")
);

if ($page === "") {
    $page = "dashboard";
}

/*
|--------------------------------------------------------------------------
| PÁGINAS EXISTENTES
|--------------------------------------------------------------------------
*/

$paginasPermitidas = [

    "dashboard",

    "agenda",
    "editar_agendamento",

    "clientes",

    "veiculos",

    "orcamentos",

    "os",
    "editar_os",
    "ver_os",

    "pecas",

    "relatorios",

    "financeiro",

    "usuarios",

    "perfil",

    "configuracoes"
];

/*
|--------------------------------------------------------------------------
| VALIDAR NOME DA PÁGINA
|--------------------------------------------------------------------------
*/

if (!in_array($page, $paginasPermitidas, true)) {

    header(
        "Location: index.php?page=dashboard&erro=pagina"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| MATRIZ DE PERMISSÕES
|--------------------------------------------------------------------------
|
| Aqui está a segurança das páginas do GP Manager.
|
| Mesmo que alguém digite a URL manualmente, o sistema verificará
| se o nível daquele usuário pode acessar a página.
|
*/

$permissoes = [

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    "dashboard" => [
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura",
        "Financeiro"
    ],

    /*
    |--------------------------------------------------------------------------
    | AGENDA
    |--------------------------------------------------------------------------
    */

    "agenda" => [
        "Administrador Geral",
        "Administrador",
        "Recepção"
    ],

    "editar_agendamento" => [
        "Administrador Geral",
        "Administrador",
        "Recepção"
    ],

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */

    "clientes" => [
        "Administrador Geral",
        "Administrador",
        "Recepção"
    ],

    /*
    |--------------------------------------------------------------------------
    | VEÍCULOS
    |--------------------------------------------------------------------------
    */

    "veiculos" => [
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura"
    ],

    /*
    |--------------------------------------------------------------------------
    | ORÇAMENTOS
    |--------------------------------------------------------------------------
    */

    "orcamentos" => [
        "Administrador Geral",
        "Administrador",
        "Recepção"
    ],

    /*
    |--------------------------------------------------------------------------
    | ORDENS DE SERVIÇO
    |--------------------------------------------------------------------------
    */

    "os" => [
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura"
    ],

    "editar_os" => [
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura"
    ],

    "ver_os" => [
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura"
    ],

    /*
    |--------------------------------------------------------------------------
    | PEÇAS / ESTOQUE
    |--------------------------------------------------------------------------
    */

    "pecas" => [
        "Administrador Geral",
        "Administrador",
        "Funilaria",
        "Pintura"
    ],

    /*
    |--------------------------------------------------------------------------
    | RELATÓRIOS
    |--------------------------------------------------------------------------
    */

    "relatorios" => [
        "Administrador Geral",
        "Administrador",
        "Financeiro"
    ],

    /*
    |--------------------------------------------------------------------------
    | FINANCEIRO
    |--------------------------------------------------------------------------
    */

    "financeiro" => [
        "Administrador Geral",
        "Administrador",
        "Financeiro"
    ],

    /*
    |--------------------------------------------------------------------------
    | USUÁRIOS
    |--------------------------------------------------------------------------
    */

    "usuarios" => [
        "Administrador Geral",
        "Administrador"
    ],

    /*
    |--------------------------------------------------------------------------
    | PERFIL
    |--------------------------------------------------------------------------
    |
    | Todos podem acessar o próprio perfil.
    |
    */

    "perfil" => [
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura",
        "Financeiro"
    ],

    /*
    |--------------------------------------------------------------------------
    | CONFIGURAÇÕES
    |--------------------------------------------------------------------------
    |
    | Exclusivo do Administrador Geral.
    |
    */

    "configuracoes" => [
        "Administrador Geral"
    ]
];

/*
|--------------------------------------------------------------------------
| VERIFICAR PERMISSÃO
|--------------------------------------------------------------------------
*/

if (
    !isset($permissoes[$page]) ||
    !in_array(
        $nivelUsuario,
        $permissoes[$page],
        true
    )
) {

    header(
        "Location: index.php?page=dashboard&erro=acesso"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| DEFINIR ARQUIVO
|--------------------------------------------------------------------------
*/

$pageFile =
    __DIR__ .
    "/pages/" .
    $page .
    ".php";

/*
|--------------------------------------------------------------------------
| VERIFICAR EXISTÊNCIA DO ARQUIVO
|--------------------------------------------------------------------------
*/

if (!is_file($pageFile)) {

    header(
        "Location: index.php?page=dashboard&erro=pagina"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| CARREGAR INTERFACE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/includes/header.php";

require_once __DIR__ . "/includes/navbar.php";

require_once __DIR__ . "/includes/sidebar.php";

/*
|--------------------------------------------------------------------------
| CONTEÚDO
|--------------------------------------------------------------------------
*/

require $pageFile;

/*
|--------------------------------------------------------------------------
| RODAPÉ
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/includes/footer.php";