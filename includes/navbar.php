<?php

/*
|--------------------------------------------------------------------------
| USUÁRIO LOGADO
|--------------------------------------------------------------------------
*/

$usuarioNavbar = $_SESSION["usuario"] ?? [];

$nomeNavbar  = $usuarioNavbar["nome"] ?? "Usuário";
$nivelNavbar = $usuarioNavbar["nivel"] ?? "";
$fotoNavbar  = $usuarioNavbar["foto"] ?? "";

?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">

    <div class="container-fluid">

        <!-- BOTÃO MENU -->
        <button
            type="button"
            class="btn btn-light me-3"
            id="btnSidebar"
            aria-label="Abrir ou fechar menu">

            <i class="bi bi-list fs-3"></i>

        </button>


        <!-- PESQUISA GLOBAL -->
        <div class="position-relative navbar-search">

            <input
                type="text"
                id="pesquisaGlobal"
                class="form-control"
                placeholder="Pesquisar cliente, placa, veículo, orçamento ou O.S..."
                autocomplete="off">

            <div
                id="resultadoPesquisa"
                class="bg-white shadow rounded position-absolute mt-1 w-100"
                style="
                    display:none;
                    max-height:350px;
                    overflow:auto;
                    z-index:9999;
                ">
            </div>

        </div>


        <!-- LADO DIREITO -->
        <div class="d-flex align-items-center ms-auto navbar-right">

            <!-- DATA -->
            <div class="text-center me-4 navbar-date">

                <small class="text-muted">
                    <?= date("d/m/Y") ?>
                </small>

            </div>


            <!-- HORA -->
            <div class="text-center me-4 navbar-clock">

                <strong id="relogio"></strong>

            </div>


            <!-- NOTIFICAÇÕES -->
            <button
                type="button"
                class="btn btn-light position-relative me-3"
                title="Notificações">

                <i class="bi bi-bell-fill fs-5"></i>

                <span
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                    3

                </span>

            </button>


            <!-- PERFIL -->
            <div class="dropdown">

                <button
                    type="button"
                    class="btn btn-light dropdown-toggle d-flex align-items-center gap-2"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                    <?php if (!empty($fotoNavbar)): ?>

                        <img
                            src="uploads/usuarios/<?= htmlspecialchars($fotoNavbar) ?>"
                            alt="Foto do usuário"
                            width="32"
                            height="32"
                            class="rounded-circle"
                            style="object-fit:cover;">

                    <?php else: ?>

                        <i class="bi bi-person-circle fs-4"></i>

                    <?php endif; ?>


                    <span class="navbar-user-name">

                        <?= htmlspecialchars($nomeNavbar) ?>

                    </span>

                </button>


                <ul
                    class="dropdown-menu dropdown-menu-end shadow border-0"
                    style="min-width:240px;">


                    <!-- INFORMAÇÕES DO USUÁRIO -->
                    <li>

                        <div class="px-3 py-2">

                            <strong class="d-block">

                                <?= htmlspecialchars($nomeNavbar) ?>

                            </strong>

                            <small class="text-muted">

                                <?= htmlspecialchars($nivelNavbar) ?>

                            </small>

                        </div>

                    </li>


                    <li>
                        <hr class="dropdown-divider">
                    </li>


                    <!-- MEU PERFIL -->
                    <li>

                        <a
                            class="dropdown-item"
                            href="index.php?page=perfil">

                            <i class="bi bi-person me-2"></i>

                            Meu Perfil

                        </a>

                    </li>


                    <!-- USUÁRIOS -->
                    <?php if (
                        $nivelNavbar === "Administrador Geral" ||
                        $nivelNavbar === "Administrador"
                    ): ?>

                        <li>

                            <a
                                class="dropdown-item"
                                href="index.php?page=usuarios">

                                <i class="bi bi-people me-2"></i>

                                Usuários

                            </a>

                        </li>

                    <?php endif; ?>


                    <!-- CONFIGURAÇÕES -->
                    <?php if ($nivelNavbar === "Administrador Geral"): ?>

                        <li>

                            <a
                                class="dropdown-item"
                                href="index.php?page=configuracoes">

                                <i class="bi bi-gear me-2"></i>

                                Configurações

                            </a>

                        </li>

                    <?php endif; ?>


                    <li>
                        <hr class="dropdown-divider">
                    </li>


                    <!-- SAIR -->
                    <li>

                        <a
                            class="dropdown-item text-danger"
                            href="actions/logout.php">

                            <i class="bi bi-box-arrow-right me-2"></i>

                            Sair

                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>


<script>

/*
|--------------------------------------------------------------------------
| RELÓGIO
|--------------------------------------------------------------------------
*/

function atualizarRelogio() {

    const agora = new Date();

    const horas = String(agora.getHours()).padStart(2, "0");
    const minutos = String(agora.getMinutes()).padStart(2, "0");
    const segundos = String(agora.getSeconds()).padStart(2, "0");

    const relogio = document.getElementById("relogio");

    if (relogio) {

        relogio.textContent =
            horas + ":" +
            minutos + ":" +
            segundos;

    }

}

atualizarRelogio();

setInterval(atualizarRelogio, 1000);

</script>