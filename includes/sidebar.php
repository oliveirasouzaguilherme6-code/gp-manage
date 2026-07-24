<?php

/*
|--------------------------------------------------------------------------
| GP MANAGER - SIDEBAR
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| DADOS DO USUÁRIO
|--------------------------------------------------------------------------
*/

$nivelSidebar = $_SESSION["usuario"]["nivel"] ?? "";

$paginaAtual = $_GET["page"] ?? "dashboard";

/*
|--------------------------------------------------------------------------
| FUNÇÃO PARA VERIFICAR ACESSO
|--------------------------------------------------------------------------
*/

function podeVerMenu(array $niveis): bool
{
    $nivel = $_SESSION["usuario"]["nivel"] ?? "";

    return in_array($nivel, $niveis, true);
}

/*
|--------------------------------------------------------------------------
| FUNÇÃO PARA ITEM ATIVO
|--------------------------------------------------------------------------
*/

function menuAtivo(array $paginas): string
{
    $paginaAtual = $_GET["page"] ?? "dashboard";

    return in_array($paginaAtual, $paginas, true)
        ? "active"
        : "";
}

?>

<div class="sidebar">

    <!-- MARCA -->

    <div class="sidebar-brand">

        <h3>
            GP Manager
        </h3>

        <small>
            Gestão Automotiva
        </small>

    </div>


    <!-- PRINCIPAL -->

    <div class="sidebar-section">

        <span class="sidebar-label">
            PRINCIPAL
        </span>


        <!-- DASHBOARD -->

        <a
            href="index.php?page=dashboard"
            class="<?= menuAtivo(["dashboard"]) ?>">

            <i class="bi bi-grid"></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- AGENDA -->

        <?php if (podeVerMenu([
            "Administrador Geral",
            "Administrador",
            "Recepção"
        ])): ?>

            <a
                href="index.php?page=agenda"
                class="<?= menuAtivo([
                    "agenda",
                    "editar_agendamento"
                ]) ?>">

                <i class="bi bi-calendar-event"></i>

                <span>
                    Agenda
                </span>

            </a>

        <?php endif; ?>


        <!-- CLIENTES -->

        <?php if (podeVerMenu([
            "Administrador Geral",
            "Administrador",
            "Recepção"
        ])): ?>

            <a
                href="index.php?page=clientes"
                class="<?= menuAtivo(["clientes"]) ?>">

                <i class="bi bi-people"></i>

                <span>
                    Clientes
                </span>

            </a>

        <?php endif; ?>


        <!-- VEÍCULOS -->

        <?php if (podeVerMenu([
            "Administrador Geral",
            "Administrador",
            "Recepção",
            "Funilaria",
            "Pintura"
        ])): ?>

            <a
                href="index.php?page=veiculos"
                class="<?= menuAtivo(["veiculos"]) ?>">

                <i class="bi bi-car-front"></i>

                <span>
                    Veículos
                </span>

            </a>

        <?php endif; ?>

    </div>


    <!-- OPERAÇÃO -->

    <?php if (podeVerMenu([
        "Administrador Geral",
        "Administrador",
        "Recepção",
        "Funilaria",
        "Pintura"
    ])): ?>

        <div class="sidebar-section">

            <span class="sidebar-label">
                OPERAÇÃO
            </span>


            <!-- ORÇAMENTOS -->

            <?php if (podeVerMenu([
                "Administrador Geral",
                "Administrador",
                "Recepção"
            ])): ?>

                <a
                    href="index.php?page=orcamentos"
                    class="<?= menuAtivo(["orcamentos"]) ?>">

                    <i class="bi bi-receipt"></i>

                    <span>
                        Orçamentos
                    </span>

                </a>

            <?php endif; ?>


            <!-- ORDENS DE SERVIÇO -->

            <a
                href="index.php?page=os"
                class="<?= menuAtivo([
                    "os",
                    "editar_os",
                    "ver_os"
                ]) ?>">

                <i class="bi bi-tools"></i>

                <span>
                    Ordens de Serviço
                </span>

            </a>


            <!-- PEÇAS -->

            <?php if (podeVerMenu([
                "Administrador Geral",
                "Administrador",
                "Funilaria",
                "Pintura"
            ])): ?>

                <a
                    href="index.php?page=pecas"
                    class="<?= menuAtivo(["pecas"]) ?>">

                    <i class="bi bi-box-seam"></i>

                    <span>
                        Peças / Estoque
                    </span>

                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <!-- GESTÃO -->

    <?php if (podeVerMenu([
        "Administrador Geral",
        "Administrador",
        "Financeiro"
    ])): ?>

        <div class="sidebar-section">

            <span class="sidebar-label">
                GESTÃO
            </span>


            <!-- RELATÓRIOS -->

            <a
                href="index.php?page=relatorios"
                class="<?= menuAtivo(["relatorios"]) ?>">

                <i class="bi bi-bar-chart"></i>

                <span>
                    Relatórios
                </span>

            </a>


            <!-- FINANCEIRO -->

            <a
                href="index.php?page=financeiro"
                class="<?= menuAtivo(["financeiro"]) ?>">

                <i class="bi bi-cash-stack"></i>

                <span>
                    Financeiro
                </span>

            </a>

        </div>

    <?php endif; ?>


    <!-- ADMINISTRAÇÃO -->

    <?php if (podeVerMenu([
        "Administrador Geral",
        "Administrador"
    ])): ?>

        <div class="sidebar-section">

            <span class="sidebar-label">
                ADMINISTRAÇÃO
            </span>


            <!-- USUÁRIOS -->

            <a
                href="index.php?page=usuarios"
                class="<?= menuAtivo(["usuarios"]) ?>">

                <i class="bi bi-person-badge"></i>

                <span>
                    Usuários
                </span>

            </a>


            <!-- CONFIGURAÇÕES -->

            <?php if ($nivelSidebar === "Administrador Geral"): ?>

                <a
                    href="index.php?page=configuracoes"
                    class="<?= menuAtivo(["configuracoes"]) ?>">

                    <i class="bi bi-gear"></i>

                    <span>
                        Configurações
                    </span>

                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <!-- CONTA -->

    <div class="sidebar-section">

        <span class="sidebar-label">
            CONTA
        </span>


        <a
            href="index.php?page=perfil"
            class="<?= menuAtivo(["perfil"]) ?>">

            <i class="bi bi-person-circle"></i>

            <span>
                Meu Perfil
            </span>

        </a>


        <a
            href="actions/logout.php"
            class="sidebar-logout">

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Sair
            </span>

        </a>

    </div>

</div>

<div class="main">