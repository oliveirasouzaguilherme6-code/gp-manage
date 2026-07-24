<?php

require_once __DIR__ . "/../config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| USUÁRIO LOGADO
|--------------------------------------------------------------------------
*/

$nivelLogado = $_SESSION["usuario"]["nivel"] ?? "";
$idLogado = (int)($_SESSION["usuario"]["id_usuario"] ?? 0);

$ehAdministradorGeral =
    $nivelLogado === "Administrador Geral";

$ehAdministrador =
    in_array(
        $nivelLogado,
        ["Administrador Geral", "Administrador"],
        true
    );

/*
|--------------------------------------------------------------------------
| PROTEÇÃO EXTRA DA PÁGINA
|--------------------------------------------------------------------------
*/

if (!$ehAdministrador) {

    echo '
        <div class="alert alert-danger">
            <i class="bi bi-shield-lock me-2"></i>
            Você não possui permissão para acessar o gerenciamento de usuários.
        </div>
    ';

    return;
}

/*
|--------------------------------------------------------------------------
| FUNÇÃO PARA ESCAPAR HTML
|--------------------------------------------------------------------------
*/

function e($valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

/*
|--------------------------------------------------------------------------
| BUSCAR USUÁRIOS
|--------------------------------------------------------------------------
*/

$sql = $conn->query("
    SELECT
        id_usuario,
        nome,
        email,
        nivel,
        foto,
        ativo
    FROM usuarios
    ORDER BY
        CASE nivel
            WHEN 'Administrador Geral' THEN 1
            WHEN 'Administrador' THEN 2
            WHEN 'Financeiro' THEN 3
            WHEN 'Recepção' THEN 4
            WHEN 'Funilaria' THEN 5
            WHEN 'Pintura' THEN 6
            ELSE 7
        END,
        nome ASC
");

$usuarios = $sql->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| CONTADORES
|--------------------------------------------------------------------------
*/

$totalUsuarios = count($usuarios);

$totalAtivos = 0;
$totalInativos = 0;
$totalAdministradores = 0;

foreach ($usuarios as $usuario) {

    if ((int)$usuario["ativo"] === 1) {

        $totalAtivos++;

    } else {

        $totalInativos++;
    }

    if (
        in_array(
            $usuario["nivel"],
            ["Administrador Geral", "Administrador"],
            true
        )
    ) {
        $totalAdministradores++;
    }
}

/*
|--------------------------------------------------------------------------
| MENSAGENS
|--------------------------------------------------------------------------
*/

$mensagemSucesso = null;
$mensagemErro = null;

if (isset($_GET["sucesso"])) {

    switch ($_GET["sucesso"]) {

        case "cadastrado":
            $mensagemSucesso =
                "Usuário cadastrado com sucesso.";
            break;

        case "editado":
            $mensagemSucesso =
                "Usuário atualizado com sucesso.";
            break;

        case "senha":
            $mensagemSucesso =
                "Senha redefinida com sucesso.";
            break;

        case "ativado":
            $mensagemSucesso =
                "Usuário ativado com sucesso.";
            break;

        case "desativado":
            $mensagemSucesso =
                "Usuário desativado com sucesso.";
            break;

        default:
            $mensagemSucesso =
                "Operação realizada com sucesso.";
            break;
    }
}

if (isset($_GET["erro"])) {

    switch ($_GET["erro"]) {

        case "email_existente":
            $mensagemErro =
                "Este e-mail já está sendo utilizado.";
            break;

        case "senha_curta":
            $mensagemErro =
                "A senha precisa ter pelo menos 8 caracteres.";
            break;

        case "senhas_diferentes":
            $mensagemErro =
                "As senhas informadas não são iguais.";
            break;

        case "permissao":
        case "acesso":
            $mensagemErro =
                "Você não possui permissão para realizar esta operação.";
            break;

        case "protegido":
            $mensagemErro =
                "Este usuário possui proteção administrativa.";
            break;

        case "proprio_usuario":
            $mensagemErro =
                "Você não pode desativar sua própria conta.";
            break;

        case "usuario":
            $mensagemErro =
                "Usuário não encontrado.";
            break;

        case "email":
            $mensagemErro =
                "Informe um endereço de e-mail válido.";
            break;

        case "nivel":
            $mensagemErro =
                "O nível de acesso informado é inválido.";
            break;

        case "dados":
        case "campos":
            $mensagemErro =
                "Preencha corretamente todos os campos obrigatórios.";
            break;

        case "acao":
            $mensagemErro =
                "Ação inválida.";
            break;

        case "banco":
            $mensagemErro =
                "Não foi possível concluir a operação no banco de dados.";
            break;

        default:
            $mensagemErro =
                "Não foi possível realizar a operação.";
            break;
    }
}

?>

<!-- CABEÇALHO -->

<div
    class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

    <div>

        <h2 class="mb-1">
            Usuários
        </h2>

        <div class="text-muted">
            Gerenciamento de usuários, acessos e permissões
        </div>

    </div>

    <button
        type="button"
        class="btn btn-warning"
        data-bs-toggle="modal"
        data-bs-target="#modalUsuario">

        <i class="bi bi-person-plus me-1"></i>

        Novo Usuário

    </button>

</div>


<!-- MENSAGEM DE SUCESSO -->

<?php if ($mensagemSucesso): ?>

    <div
        class="alert alert-success alert-dismissible fade show shadow-sm"
        role="alert">

        <i class="bi bi-check-circle-fill me-2"></i>

        <?= e($mensagemSucesso) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Fechar">
        </button>

    </div>

<?php endif; ?>


<!-- MENSAGEM DE ERRO -->

<?php if ($mensagemErro): ?>

    <div
        class="alert alert-danger alert-dismissible fade show shadow-sm"
        role="alert">

        <i class="bi bi-exclamation-triangle-fill me-2"></i>

        <?= e($mensagemErro) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Fechar">
        </button>

    </div>

<?php endif; ?>


<!-- RESUMO -->

<div class="row g-3 mb-4">

    <!-- TOTAL -->

    <div class="col-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div
                    class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-2">
                            Total de usuários
                        </div>

                        <h3 class="mb-0">
                            <?= $totalUsuarios ?>
                        </h3>

                    </div>

                    <div
                        class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-people fs-4 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ATIVOS -->

    <div class="col-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div
                    class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-2">
                            Usuários ativos
                        </div>

                        <h3 class="mb-0 text-success">
                            <?= $totalAtivos ?>
                        </h3>

                    </div>

                    <div
                        class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-person-check fs-4 text-success"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- INATIVOS -->

    <div class="col-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div
                    class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-2">
                            Usuários inativos
                        </div>

                        <h3 class="mb-0 text-danger">
                            <?= $totalInativos ?>
                        </h3>

                    </div>

                    <div
                        class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-person-x fs-4 text-danger"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ADMINISTRADORES -->

    <div class="col-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div
                    class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-2">
                            Administradores
                        </div>

                        <h3 class="mb-0 text-warning">
                            <?= $totalAdministradores ?>
                        </h3>

                    </div>

                    <div
                        class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-shield-check fs-4 text-warning"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- TABELA -->

<div class="card border-0 shadow-sm">

    <div
        class="card-header bg-white border-0 pt-4 px-4 pb-0">

        <div
            class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>

                <h5 class="mb-1">
                    Usuários cadastrados
                </h5>

                <small class="text-muted">
                    <?= $totalUsuarios ?>
                    <?= $totalUsuarios === 1 ? "usuário" : "usuários" ?>
                    no sistema
                </small>

            </div>

        </div>

    </div>


    <div class="card-body p-4">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>Usuário</th>

                        <th>E-mail</th>

                        <th>Nível</th>

                        <th>Status</th>

                        <th
                            class="text-end"
                            style="min-width:150px;">

                            Ações

                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($usuarios)): ?>

                    <?php foreach ($usuarios as $u): ?>

                        <?php

                        $idUsuario = (int)$u["id_usuario"];

                        $ehProprioUsuario =
                            $idUsuario === $idLogado;

                        $usuarioEhAdmGeral =
                            $u["nivel"] === "Administrador Geral";

                        /*
                        |--------------------------------------------------------------------------
                        | PERMISSÃO SOBRE O USUÁRIO
                        |--------------------------------------------------------------------------
                        |
                        | Administrador Geral pode administrar todos.
                        |
                        | Administrador comum não pode administrar
                        | Administrador Geral.
                        |
                        */

                        $podeAdministrar =
                            $ehAdministradorGeral ||
                            !$usuarioEhAdmGeral;

                        ?>

                        <tr>

                            <!-- USUÁRIO -->

                            <td>

                                <div
                                    class="d-flex align-items-center gap-3">

                                    <?php if (!empty($u["foto"])): ?>

                                        <img
                                            src="uploads/usuarios/<?= e($u["foto"]) ?>"
                                            width="42"
                                            height="42"
                                            class="rounded-circle border"
                                            style="object-fit:cover;"
                                            alt="Foto de <?= e($u["nome"]) ?>">

                                    <?php else: ?>

                                        <div
                                            class="rounded-circle bg-light border d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width:42px;height:42px;">

                                            <i class="bi bi-person fs-5 text-secondary"></i>

                                        </div>

                                    <?php endif; ?>


                                    <div>

                                        <div class="fw-semibold">

                                            <?= e($u["nome"]) ?>

                                        </div>

                                        <?php if ($ehProprioUsuario): ?>

                                            <small class="text-primary">

                                                <i class="bi bi-person-check me-1"></i>

                                                Você

                                            </small>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </td>


                            <!-- E-MAIL -->

                            <td>

                                <span class="text-muted">

                                    <?= e($u["email"]) ?>

                                </span>

                            </td>


                            <!-- NÍVEL -->

                            <td>

                                <?php if ($usuarioEhAdmGeral): ?>

                                    <span class="badge text-bg-dark">

                                        <i class="bi bi-shield-lock me-1"></i>

                                        Administrador Geral

                                    </span>

                                <?php elseif ($u["nivel"] === "Administrador"): ?>

                                    <span class="badge text-bg-primary">

                                        <i class="bi bi-shield-check me-1"></i>

                                        Administrador

                                    </span>

                                <?php elseif ($u["nivel"] === "Financeiro"): ?>

                                    <span class="badge text-bg-success">

                                        <i class="bi bi-cash-stack me-1"></i>

                                        Financeiro

                                    </span>

                                <?php elseif ($u["nivel"] === "Recepção"): ?>

                                    <span class="badge text-bg-info">

                                        <i class="bi bi-headset me-1"></i>

                                        Recepção

                                    </span>

                                <?php elseif ($u["nivel"] === "Funilaria"): ?>

                                    <span class="badge text-bg-secondary">

                                        <i class="bi bi-tools me-1"></i>

                                        Funilaria

                                    </span>

                                <?php elseif ($u["nivel"] === "Pintura"): ?>

                                    <span class="badge text-bg-warning">

                                        <i class="bi bi-palette me-1"></i>

                                        Pintura

                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-secondary">

                                        <?= e($u["nivel"]) ?>

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ((int)$u["ativo"] === 1): ?>

                                    <span
                                        class="badge rounded-pill text-bg-success">

                                        <i class="bi bi-check-circle me-1"></i>

                                        Ativo

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="badge rounded-pill text-bg-danger">

                                        <i class="bi bi-x-circle me-1"></i>

                                        Inativo

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- AÇÕES -->

                            <td class="text-end">

                                <?php if ($podeAdministrar): ?>

                                    <div
                                        class="d-inline-flex gap-1">

                                        <!-- EDITAR -->

                                        <a
                                            href="actions/editar_usuario.php?id=<?= $idUsuario ?>"
                                            class="btn btn-outline-primary btn-sm"
                                            title="Editar usuário">

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <!-- SENHA -->

                                        <button
                                            type="button"
                                            class="btn btn-outline-dark btn-sm"
                                            title="Redefinir senha"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalSenha<?= $idUsuario ?>">

                                            <i class="bi bi-key"></i>

                                        </button>


                                        <!-- STATUS -->

                                        <?php if (!$ehProprioUsuario): ?>

                                            <?php if ((int)$u["ativo"] === 1): ?>

                                                <a
                                                    href="actions/status_usuario.php?id=<?= $idUsuario ?>&acao=desativar"
                                                    class="btn btn-outline-danger btn-sm"
                                                    title="Desativar usuário"
                                                    onclick="return confirm('Deseja realmente desativar <?= e($u["nome"]) ?>?');">

                                                    <i class="bi bi-person-x"></i>

                                                </a>

                                            <?php else: ?>

                                                <a
                                                    href="actions/status_usuario.php?id=<?= $idUsuario ?>&acao=ativar"
                                                    class="btn btn-outline-success btn-sm"
                                                    title="Ativar usuário"
                                                    onclick="return confirm('Deseja ativar <?= e($u["nome"]) ?>?');">

                                                    <i class="bi bi-person-check"></i>

                                                </a>

                                            <?php endif; ?>

                                        <?php endif; ?>

                                    </div>

                                <?php else: ?>

                                    <span
                                        class="badge bg-light text-secondary border">

                                        <i class="bi bi-lock me-1"></i>

                                        Protegido

                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="5"
                            class="text-center text-muted py-5">

                            <i
                                class="bi bi-people fs-1 d-block mb-3">
                            </i>

                            <strong>
                                Nenhum usuário cadastrado
                            </strong>

                            <div class="small mt-1">
                                Cadastre o primeiro usuário para começar.
                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!--
|--------------------------------------------------------------------------
| MODAIS DE REDEFINIÇÃO DE SENHA
|--------------------------------------------------------------------------
|
| Mantidos fora da tabela para evitar HTML inválido dentro do tbody.
|
-->

<?php foreach ($usuarios as $u): ?>

    <?php

    $idUsuario = (int)$u["id_usuario"];

    $usuarioEhAdmGeral =
        $u["nivel"] === "Administrador Geral";

    $podeAdministrar =
        $ehAdministradorGeral ||
        !$usuarioEhAdmGeral;

    if (!$podeAdministrar) {
        continue;
    }

    ?>

    <div
        class="modal fade"
        id="modalSenha<?= $idUsuario ?>"
        tabindex="-1"
        aria-hidden="true">

        <div
            class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="actions/redefinir_senha_usuario.php"
                    method="POST">

                    <input
                        type="hidden"
                        name="id_usuario"
                        value="<?= $idUsuario ?>">


                    <div class="modal-header">

                        <div>

                            <h5 class="modal-title mb-1">

                                <i class="bi bi-key me-2"></i>

                                Redefinir Senha

                            </h5>

                            <small class="text-muted">

                                <?= e($u["nome"]) ?>

                            </small>

                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div
                            class="alert alert-light border small">

                            <i class="bi bi-info-circle me-1"></i>

                            A senha atual não será exibida.
                            Uma nova senha será criada para este usuário.

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Nova senha
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="nova_senha"
                                    id="novaSenha<?= $idUsuario ?>"
                                    class="form-control"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required>

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="alternarSenha('novaSenha<?= $idUsuario ?>', this)"
                                    title="Mostrar senha">

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                            <div class="form-text">
                                Utilize pelo menos 8 caracteres.
                            </div>

                        </div>


                        <div>

                            <label class="form-label">
                                Confirmar nova senha
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="confirmar_senha"
                                    id="confirmarSenha<?= $idUsuario ?>"
                                    class="form-control"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required>

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="alternarSenha('confirmarSenha<?= $idUsuario ?>', this)"
                                    title="Mostrar senha">

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-light border"
                            data-bs-dismiss="modal">

                            Cancelar

                        </button>

                        <button
                            type="submit"
                            class="btn btn-warning">

                            <i class="bi bi-key me-1"></i>

                            Redefinir Senha

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

<?php endforeach; ?>


<!--
|--------------------------------------------------------------------------
| MODAL NOVO USUÁRIO
|--------------------------------------------------------------------------
-->

<div
    class="modal fade"
    id="modalUsuario"
    tabindex="-1"
    aria-hidden="true">

    <div
        class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                action="actions/salvar_usuario.php"
                method="POST">

                <div class="modal-header">

                    <div>

                        <h5 class="modal-title mb-1">

                            <i class="bi bi-person-plus me-2"></i>

                            Novo Usuário

                        </h5>

                        <small class="text-muted">
                            Cadastre um novo acesso ao GP Manager
                        </small>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar">
                    </button>

                </div>


                <div class="modal-body">

                    <!-- NOME -->

                    <div class="mb-3">

                        <label class="form-label">
                            Nome completo *
                        </label>

                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            maxlength="150"
                            autocomplete="name"
                            required>

                    </div>


                    <!-- EMAIL -->

                    <div class="mb-3">

                        <label class="form-label">
                            E-mail *
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            maxlength="150"
                            autocomplete="email"
                            required>

                    </div>


                    <!-- SENHA -->

                    <div class="mb-3">

                        <label class="form-label">
                            Senha inicial *
                        </label>

                        <div class="input-group">

                            <input
                                type="password"
                                name="senha"
                                id="senhaNovoUsuario"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required>

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="alternarSenha('senhaNovoUsuario', this)"
                                title="Mostrar senha">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                        <div class="form-text">
                            A senha deve possuir pelo menos 8 caracteres.
                        </div>

                    </div>


                    <!-- NÍVEL -->

                    <div>

                        <label class="form-label">
                            Nível de acesso *
                        </label>

                        <select
                            name="nivel"
                            class="form-select"
                            required>

                            <option value="">
                                Selecione um nível
                            </option>

                            <?php if ($ehAdministradorGeral): ?>

                                <option value="Administrador Geral">
                                    Administrador Geral
                                </option>

                            <?php endif; ?>

                            <option value="Administrador">
                                Administrador
                            </option>

                            <option value="Recepção">
                                Recepção
                            </option>

                            <option value="Funilaria">
                                Funilaria
                            </option>

                            <option value="Pintura">
                                Pintura
                            </option>

                            <option value="Financeiro">
                                Financeiro
                            </option>

                        </select>

                        <div class="form-text">
                            O nível determina quais áreas do sistema o usuário poderá acessar.
                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="bi bi-check-lg me-1"></i>

                        Criar Usuário

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| MOSTRAR / OCULTAR SENHA
|--------------------------------------------------------------------------
*/

function alternarSenha(idCampo, botao) {

    const campo = document.getElementById(idCampo);

    if (!campo) {
        return;
    }

    const icone = botao.querySelector("i");

    if (campo.type === "password") {

        campo.type = "text";

        if (icone) {
            icone.className = "bi bi-eye-slash";
        }

        botao.title = "Ocultar senha";

    } else {

        campo.type = "password";

        if (icone) {
            icone.className = "bi bi-eye";
        }

        botao.title = "Mostrar senha";
    }
}

</script>