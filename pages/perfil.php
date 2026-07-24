<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$idUsuarioPerfil = (int)($_SESSION["usuario"]["id_usuario"] ?? 0);

$sql = $conn->prepare("
    SELECT
        id_usuario,
        nome,
        email,
        nivel,
        foto,
        ativo
    FROM usuarios
    WHERE id_usuario = ?
    LIMIT 1
");

$sql->execute([$idUsuarioPerfil]);

$usuarioPerfil = $sql->fetch(PDO::FETCH_ASSOC);

if (!$usuarioPerfil) {
    echo "
        <div class='alert alert-danger'>
            Usuário não encontrado.
        </div>
    ";
    return;
}

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="mb-1">Meu Perfil</h2>

        <span class="text-muted">
            Gerencie seus dados de acesso
        </span>
    </div>

</div>


<?php if (isset($_GET["sucesso"])): ?>

    <div class="alert alert-success alert-dismissible fade show">

        <i class="bi bi-check-circle me-2"></i>

        Dados atualizados com sucesso.

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

<?php endif; ?>


<?php if (isset($_GET["senha"])): ?>

    <div class="alert alert-success alert-dismissible fade show">

        <i class="bi bi-shield-check me-2"></i>

        Senha alterada com sucesso.

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

<?php endif; ?>


<?php if (isset($_GET["erro"])): ?>

    <div class="alert alert-danger alert-dismissible fade show">

        <i class="bi bi-exclamation-triangle me-2"></i>

        Não foi possível realizar a operação.

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

<?php endif; ?>


<div class="row g-4">

    <!-- PERFIL -->

    <div class="col-lg-7">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <h5 class="mb-0">

                    <i class="bi bi-person me-2"></i>

                    Dados pessoais

                </h5>

            </div>

            <div class="card-body p-4">

                <form
                    action="actions/atualizar_perfil.php"
                    method="POST">

                    <div class="mb-3">

                        <label class="form-label">
                            Nome
                        </label>

                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            maxlength="150"
                            value="<?= htmlspecialchars(
                                $usuarioPerfil["nome"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            required>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            E-mail
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            maxlength="150"
                            value="<?= htmlspecialchars(
                                $usuarioPerfil["email"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            required>

                    </div>


                    <div class="mb-4">

                        <label class="form-label">
                            Nível de acesso
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $usuarioPerfil["nivel"],
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            disabled>

                        <div class="form-text">

                            O nível de acesso não pode ser alterado
                            pelo próprio usuário.

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="bi bi-check-lg me-1"></i>

                        Salvar Dados

                    </button>

                </form>

            </div>

        </div>

    </div>


    <!-- ALTERAR SENHA -->

    <div class="col-lg-5">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">

                <h5 class="mb-0">

                    <i class="bi bi-shield-lock me-2"></i>

                    Alterar Senha

                </h5>

            </div>

            <div class="card-body p-4">

                <form
                    action="actions/alterar_senha.php"
                    method="POST">

                    <div class="mb-3">

                        <label class="form-label">
                            Senha atual
                        </label>

                        <input
                            type="password"
                            name="senha_atual"
                            class="form-control"
                            autocomplete="current-password"
                            required>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Nova senha
                        </label>

                        <input
                            type="password"
                            name="nova_senha"
                            class="form-control"
                            minlength="8"
                            autocomplete="new-password"
                            required>

                    </div>


                    <div class="mb-4">

                        <label class="form-label">
                            Confirmar nova senha
                        </label>

                        <input
                            type="password"
                            name="confirmar_senha"
                            class="form-control"
                            minlength="8"
                            autocomplete="new-password"
                            required>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-dark">

                        <i class="bi bi-key me-1"></i>

                        Alterar Senha

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>