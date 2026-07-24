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

$idLogado = (int)$usuarioLogado["id_usuario"];
$nivelLogado = $usuarioLogado["nivel"] ?? "";

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
| VALIDAR ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: ../index.php?page=usuarios&erro=usuario");
    exit;
}

/*
|--------------------------------------------------------------------------
| CONEXÃO
|--------------------------------------------------------------------------
*/

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
        email,
        nivel,
        ativo
    FROM usuarios
    WHERE id_usuario = ?
    LIMIT 1
");

$sql->execute([$id]);

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
| Administrador comum nunca pode editar um Administrador Geral.
|
*/

if (
    $nivelLogado !== "Administrador Geral" &&
    $usuario["nivel"] === "Administrador Geral"
) {
    header("Location: ../index.php?page=usuarios&erro=protegido");
    exit;
}

$ehProprioUsuario = ($id === $idLogado);

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Editar Usuário - GP Manager</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body {
            background: #f5f6f8;
        }

        .editar-container {
            max-width: 750px;
            margin: 60px auto;
        }

        .card {
            border: 0;
            border-radius: 16px;
        }

        .usuario-icon {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="editar-container">

        <div class="card shadow-sm">

            <div class="card-body p-4 p-md-5">

                <!-- CABEÇALHO -->

                <div class="d-flex align-items-center gap-3 mb-4">

                    <div class="usuario-icon">

                        <i class="bi bi-person"></i>

                    </div>

                    <div>

                        <h3 class="mb-1">
                            Editar Usuário
                        </h3>

                        <div class="text-muted">

                            <?= htmlspecialchars($usuario["nome"]) ?>

                            <?php if ($ehProprioUsuario): ?>

                                <span class="badge bg-secondary ms-1">
                                    Você
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <hr>

                <!-- FORMULÁRIO -->

                <form
                    action="salvar_edicao_usuario.php"
                    method="POST">

                    <input
                        type="hidden"
                        name="id_usuario"
                        value="<?= (int)$usuario["id_usuario"] ?>">


                    <!-- NOME -->

                    <div class="mb-3">

                        <label class="form-label">
                            Nome
                        </label>

                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            maxlength="120"
                            value="<?= htmlspecialchars($usuario["nome"]) ?>"
                            required>

                    </div>


                    <!-- EMAIL -->

                    <div class="mb-3">

                        <label class="form-label">
                            E-mail
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            maxlength="150"
                            value="<?= htmlspecialchars($usuario["email"]) ?>"
                            required>

                    </div>


                    <!-- NÍVEL -->

                    <div class="mb-3">

                        <label class="form-label">
                            Nível de acesso
                        </label>

                        <select
                            name="nivel"
                            class="form-select"
                            required>

                            <?php if ($nivelLogado === "Administrador Geral"): ?>

                                <option
                                    value="Administrador Geral"
                                    <?= $usuario["nivel"] === "Administrador Geral" ? "selected" : "" ?>>

                                    Administrador Geral

                                </option>

                            <?php endif; ?>


                            <option
                                value="Administrador"
                                <?= $usuario["nivel"] === "Administrador" ? "selected" : "" ?>>

                                Administrador

                            </option>


                            <option
                                value="Recepção"
                                <?= $usuario["nivel"] === "Recepção" ? "selected" : "" ?>>

                                Recepção

                            </option>


                            <option
                                value="Funilaria"
                                <?= $usuario["nivel"] === "Funilaria" ? "selected" : "" ?>>

                                Funilaria

                            </option>


                            <option
                                value="Pintura"
                                <?= $usuario["nivel"] === "Pintura" ? "selected" : "" ?>>

                                Pintura

                            </option>


                            <option
                                value="Financeiro"
                                <?= $usuario["nivel"] === "Financeiro" ? "selected" : "" ?>>

                                Financeiro

                            </option>

                        </select>

                    </div>


                    <!-- STATUS -->

                    <div class="mb-4">

                        <label class="form-label">
                            Status
                        </label>

                        <?php if ($ehProprioUsuario): ?>

                            <input
                                type="text"
                                class="form-control"
                                value="Ativo"
                                disabled>

                            <input
                                type="hidden"
                                name="ativo"
                                value="1">

                            <div class="form-text">
                                Você não pode desativar sua própria conta.
                            </div>

                        <?php else: ?>

                            <select
                                name="ativo"
                                class="form-select">

                                <option
                                    value="1"
                                    <?= (int)$usuario["ativo"] === 1 ? "selected" : "" ?>>

                                    Ativo

                                </option>

                                <option
                                    value="0"
                                    <?= (int)$usuario["ativo"] === 0 ? "selected" : "" ?>>

                                    Inativo

                                </option>

                            </select>

                        <?php endif; ?>

                    </div>


                    <hr>


                    <!-- BOTÕES -->

                    <div class="d-flex justify-content-end gap-2 mt-4">

                        <a
                            href="../index.php?page=usuarios"
                            class="btn btn-secondary">

                            <i class="bi bi-arrow-left me-1"></i>

                            Cancelar

                        </a>

                        <button
                            type="submit"
                            class="btn btn-warning">

                            <i class="bi bi-check-lg me-1"></i>

                            Salvar Alterações

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>