<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">

    <div class="container-fluid">

        <!-- Botão Menu -->
        <button class="btn btn-light me-3">
            <i class="bi bi-list fs-3"></i>
        </button>

        <!-- Pesquisa -->
        <div class="position-relative w-50">

            <input
                type="text"
                id="pesquisaGlobal"
                class="form-control"
                placeholder="Pesquisar cliente, placa, veículo, orçamento ou O.S...">

            <div id="resultadoPesquisa"
                 class="bg-white shadow rounded position-absolute mt-1 w-100"
                 style="display:none;max-height:350px;overflow:auto;z-index:9999;">
            </div>

        </div>

        <!-- Lado Direito -->
        <div class="d-flex align-items-center ms-auto">

            <!-- Data -->
            <div class="text-center me-4">

                <small class="text-muted">
                    <?= date('d/m/Y') ?>
                </small>

            </div>

            <!-- Hora -->
            <div class="text-center me-4">

                <strong id="relogio"></strong>

            </div>

            <!-- Notificações -->
            <button class="btn btn-light position-relative me-3">

                <i class="bi bi-bell-fill fs-5"></i>

                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                    3

                </span>

            </button>

            <!-- Perfil -->
            <div class="dropdown">

                <button
                    class="btn btn-light dropdown-toggle"
                    data-bs-toggle="dropdown">

                    <i class="bi bi-person-circle fs-4"></i>

                    Guilherme

                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>

                        <a class="dropdown-item" href="#">

                            Meu Perfil

                        </a>

                    </li>



<div class="dropdown">

<button
class="btn btn-light dropdown-toggle"
data-bs-toggle="dropdown">

<i class="bi bi-person-circle"></i>

<?= $_SESSION['nome']; ?>

</button>

<ul class="dropdown-menu dropdown-menu-end">

<li>

<a
class="dropdown-item"
href="index.php?page=usuarios">

<i class="bi bi-people"></i>

Usuários

</a>

</li>

<li>

<hr class="dropdown-divider">

</li>

<li>

<a
class="dropdown-item text-danger"
href="actions/logout.php">

<i class="bi bi-box-arrow-right"></i>

Sair

</a>

</li>

</ul>

</div>


                    <li>

                        <a class="dropdown-item" href="#">

                            Configurações

                        </a>

                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>

                        <a class="dropdown-item text-danger" href="#">

                            Sair

                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>