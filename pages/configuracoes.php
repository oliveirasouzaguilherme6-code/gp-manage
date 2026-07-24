<?php

/*
|--------------------------------------------------------------------------
| PROTEÇÃO
|--------------------------------------------------------------------------
*/

$nivelLogado = $_SESSION["usuario"]["nivel"] ?? "";

if ($nivelLogado !== "Administrador Geral") {

    echo '
        <div class="alert alert-danger">
            <i class="bi bi-shield-lock me-2"></i>
            Apenas o Administrador Geral pode acessar as configurações.
        </div>
    ';

    return;
}

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="mb-1">
            Configurações
        </h2>

        <span class="text-muted">
            Configurações gerais do GP Manager
        </span>

    </div>

</div>


<div class="row g-4">

    <!-- DADOS DA EMPRESA -->

    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-4">

                    <div
                        class="d-flex align-items-center justify-content-center bg-light rounded"
                        style="width:50px;height:50px;">

                        <i class="bi bi-building fs-4"></i>

                    </div>

                    <div>

                        <h5 class="mb-1">
                            Dados da Empresa
                        </h5>

                        <small class="text-muted">
                            Informações utilizadas pelo sistema
                        </small>

                    </div>

                </div>

                <p class="text-muted">

                    Configure nome da oficina, CNPJ, telefone,
                    e-mail e endereço.

                </p>

                <button
                    type="button"
                    class="btn btn-warning"
                    disabled>

                    Configurar

                </button>

            </div>

        </div>

    </div>


    <!-- IDENTIDADE -->

    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-4">

                    <div
                        class="d-flex align-items-center justify-content-center bg-light rounded"
                        style="width:50px;height:50px;">

                        <i class="bi bi-image fs-4"></i>

                    </div>

                    <div>

                        <h5 class="mb-1">
                            Identidade da Empresa
                        </h5>

                        <small class="text-muted">
                            Logo e informações visuais
                        </small>

                    </div>

                </div>

                <p class="text-muted">

                    Defina a logo que futuramente aparecerá
                    nas Ordens de Serviço e relatórios.

                </p>

                <button
                    type="button"
                    class="btn btn-warning"
                    disabled>

                    Configurar

                </button>

            </div>

        </div>

    </div>


    <!-- SISTEMA -->

    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-4">

                    <div
                        class="d-flex align-items-center justify-content-center bg-light rounded"
                        style="width:50px;height:50px;">

                        <i class="bi bi-gear fs-4"></i>

                    </div>

                    <div>

                        <h5 class="mb-1">
                            Sistema
                        </h5>

                        <small class="text-muted">
                            Preferências gerais
                        </small>

                    </div>

                </div>

                <p class="text-muted">

                    Configurações internas e preferências
                    do GP Manager.

                </p>

                <button
                    type="button"
                    class="btn btn-warning"
                    disabled>

                    Configurar

                </button>

            </div>

        </div>

    </div>


    <!-- SEGURANÇA -->

    <div class="col-lg-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-4">

                    <div
                        class="d-flex align-items-center justify-content-center bg-light rounded"
                        style="width:50px;height:50px;">

                        <i class="bi bi-shield-lock fs-4"></i>

                    </div>

                    <div>

                        <h5 class="mb-1">
                            Segurança
                        </h5>

                        <small class="text-muted">
                            Usuários e controle de acesso
                        </small>

                    </div>

                </div>

                <p class="text-muted">

                    Gerencie usuários e níveis de acesso
                    ao sistema.

                </p>

                <a
                    href="index.php?page=usuarios"
                    class="btn btn-dark">

                    <i class="bi bi-people me-1"></i>

                    Gerenciar Usuários

                </a>

            </div>

        </div>

    </div>

</div>