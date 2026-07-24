<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim    = $_GET['data_fim'] ?? date('Y-m-d');

/*
|--------------------------------------------------------------------------
| VALIDAR DATAS
|--------------------------------------------------------------------------
*/

if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicio) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)
) {
    $dataInicio = date('Y-m-01');
    $dataFim    = date('Y-m-d');
}

if ($dataInicio > $dataFim) {
    $temporaria = $dataInicio;
    $dataInicio = $dataFim;
    $dataFim    = $temporaria;
}

/*
|--------------------------------------------------------------------------
| RECEITAS PAGAS NO PERÍODO
|--------------------------------------------------------------------------
*/

$sql = $conn->prepare("
    SELECT COALESCE(SUM(valor), 0)
    FROM financeiro
    WHERE tipo = 'Receita'
      AND status = 'Pago'
      AND data_pagamento BETWEEN ? AND ?
");

$sql->execute([
    $dataInicio,
    $dataFim
]);

$totalReceitas = (float) $sql->fetchColumn();

/*
|--------------------------------------------------------------------------
| DESPESAS PAGAS NO PERÍODO
|--------------------------------------------------------------------------
*/

$sql = $conn->prepare("
    SELECT COALESCE(SUM(valor), 0)
    FROM financeiro
    WHERE tipo = 'Despesa'
      AND status = 'Pago'
      AND data_pagamento BETWEEN ? AND ?
");

$sql->execute([
    $dataInicio,
    $dataFim
]);

$totalDespesas = (float) $sql->fetchColumn();

/*
|--------------------------------------------------------------------------
| SALDO
|--------------------------------------------------------------------------
*/

$saldo = $totalReceitas - $totalDespesas;

/*
|--------------------------------------------------------------------------
| ORDENS DE SERVIÇO NO PERÍODO
|--------------------------------------------------------------------------
|
| IMPORTANTE:
| Na tabela ordens_servico a coluna correta é "entrada".
|
|--------------------------------------------------------------------------
*/

$sql = $conn->prepare("
    SELECT COUNT(*)
    FROM ordens_servico
    WHERE entrada BETWEEN ? AND ?
");

$sql->execute([
    $dataInicio,
    $dataFim
]);

$totalOS = (int) $sql->fetchColumn();

/*
|--------------------------------------------------------------------------
| ORDENS DE SERVIÇO FINALIZADAS
|--------------------------------------------------------------------------
*/

$sql = $conn->prepare("
    SELECT COUNT(*)
    FROM ordens_servico
    WHERE status = 'Finalizado'
      AND entrada BETWEEN ? AND ?
");

$sql->execute([
    $dataInicio,
    $dataFim
]);

$osFinalizadas = (int) $sql->fetchColumn();

/*
|--------------------------------------------------------------------------
| LANÇAMENTOS FINANCEIROS DO PERÍODO
|--------------------------------------------------------------------------
*/

$sql = $conn->prepare("
    SELECT *
    FROM financeiro
    WHERE (
        data_pagamento BETWEEN ? AND ?
        OR
        (
            data_pagamento IS NULL
            AND data_vencimento BETWEEN ? AND ?
        )
    )
    ORDER BY COALESCE(data_pagamento, data_vencimento) DESC
");

$sql->execute([
    $dataInicio,
    $dataFim,
    $dataInicio,
    $dataFim
]);

$lancamentos = $sql->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| FUNÇÃO PARA FORMATAR DINHEIRO
|--------------------------------------------------------------------------
*/

function moedaRelatorio($valor)
{
    return 'R$ ' . number_format(
        (float) $valor,
        2,
        ',',
        '.'
    );
}

?>

<style>

.relatorio-page {
    padding-bottom: 40px;
}

.relatorio-header {
    margin-bottom: 25px;
}

.relatorio-header h2 {
    font-weight: 600;
    margin-bottom: 4px;
}

.relatorio-header p {
    color: #6c757d;
    margin: 0;
}

/* FILTRO */

.filtro-relatorio {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

/* CARDS */

.relatorio-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    height: 100%;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

.relatorio-card .titulo {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 8px;
}

.relatorio-card .valor {
    font-size: 24px;
    font-weight: 600;
}

.valor-receita {
    color: #198754;
}

.valor-despesa {
    color: #dc3545;
}

.valor-saldo-positivo {
    color: #0d6efd;
}

.valor-saldo-negativo {
    color: #dc3545;
}

.relatorio-icon {
    font-size: 28px;
}

/* TABELA */

.tabela-relatorio {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    margin-top: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

.tabela-relatorio table {
    margin-bottom: 0;
}

.tabela-relatorio thead th {
    white-space: nowrap;
}

/* RESPONSIVO */

@media (max-width: 768px) {

    .filtro-relatorio .btn {
        width: 100%;
    }

    .relatorio-card .valor {
        font-size: 21px;
    }

    .tabela-relatorio {
        padding: 15px;
    }

}

</style>


<div class="relatorio-page">

    <!-- CABEÇALHO -->

    <div class="relatorio-header">

        <h2>Relatórios</h2>

        <p>
            Visão geral dos resultados da oficina
        </p>

    </div>


    <!-- FILTRO -->

    <div class="filtro-relatorio">

        <form method="GET">

            <input
                type="hidden"
                name="page"
                value="relatorios"
            >

            <div class="row align-items-end g-3">

                <div class="col-md-4">

                    <label class="form-label">
                        Data inicial
                    </label>

                    <input
                        type="date"
                        name="data_inicio"
                        class="form-control"
                        value="<?= htmlspecialchars($dataInicio) ?>"
                        required
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Data final
                    </label>

                    <input
                        type="date"
                        name="data_fim"
                        class="form-control"
                        value="<?= htmlspecialchars($dataFim) ?>"
                        required
                    >

                </div>

                <div class="col-md-4">

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >

                        <i class="bi bi-funnel"></i>

                        Filtrar relatório

                    </button>

                </div>

            </div>

        </form>

    </div>


    <!-- CARDS -->

    <div class="row g-3">

        <!-- RECEITAS -->

        <div class="col-xl-3 col-md-6">

            <div class="relatorio-card">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="titulo">
                            Receitas
                        </div>

                        <div class="valor valor-receita">

                            <?= moedaRelatorio($totalReceitas) ?>

                        </div>

                    </div>

                    <div class="relatorio-icon text-success">

                        <i class="bi bi-arrow-up-circle"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- DESPESAS -->

        <div class="col-xl-3 col-md-6">

            <div class="relatorio-card">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="titulo">
                            Despesas
                        </div>

                        <div class="valor valor-despesa">

                            <?= moedaRelatorio($totalDespesas) ?>

                        </div>

                    </div>

                    <div class="relatorio-icon text-danger">

                        <i class="bi bi-arrow-down-circle"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- SALDO -->

        <div class="col-xl-3 col-md-6">

            <div class="relatorio-card">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="titulo">
                            Saldo
                        </div>

                        <div
                            class="valor <?= $saldo >= 0
                                ? 'valor-saldo-positivo'
                                : 'valor-saldo-negativo'
                            ?>"
                        >

                            <?= moedaRelatorio($saldo) ?>

                        </div>

                    </div>

                    <div class="relatorio-icon text-primary">

                        <i class="bi bi-wallet2"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- ORDENS DE SERVIÇO -->

        <div class="col-xl-3 col-md-6">

            <div class="relatorio-card">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="titulo">
                            Ordens de Serviço
                        </div>

                        <div class="valor">

                            <?= $totalOS ?>

                        </div>

                        <small class="text-muted">

                            <?= $osFinalizadas ?>
                            finalizada<?= $osFinalizadas == 1 ? '' : 's' ?>

                        </small>

                    </div>

                    <div class="relatorio-icon text-warning">

                        <i class="bi bi-tools"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- MOVIMENTAÇÕES -->

    <div class="tabela-relatorio">

        <div
            class="d-flex
                   justify-content-between
                   align-items-center
                   flex-wrap
                   gap-2
                   mb-3"
        >

            <div>

                <h5 class="mb-1">
                    Movimentações Financeiras
                </h5>

                <small class="text-muted">

                    <?= date(
                        'd/m/Y',
                        strtotime($dataInicio)
                    ) ?>

                    até

                    <?= date(
                        'd/m/Y',
                        strtotime($dataFim)
                    ) ?>

                </small>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>Tipo</th>

                        <th>Categoria</th>

                        <th>Descrição</th>

                        <th>Valor</th>

                        <th>Vencimento</th>

                        <th>Pagamento</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($lancamentos)): ?>

                    <?php foreach ($lancamentos as $f): ?>

                        <tr>

                            <!-- TIPO -->

                            <td>

                                <?php if ($f['tipo'] === 'Receita'): ?>

                                    <span class="badge bg-success">

                                        ↑ Receita

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-danger">

                                        ↓ Despesa

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- CATEGORIA -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['categoria'] ?? '-'
                                ) ?>

                            </td>


                            <!-- DESCRIÇÃO -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['descricao'] ?? '-'
                                ) ?>

                            </td>


                            <!-- VALOR -->

                            <td>

                                <strong>

                                    <?= moedaRelatorio(
                                        $f['valor']
                                    ) ?>

                                </strong>

                            </td>


                            <!-- VENCIMENTO -->

                            <td>

                                <?php if (!empty(
                                    $f['data_vencimento']
                                )): ?>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $f['data_vencimento']
                                        )
                                    ) ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </td>


                            <!-- PAGAMENTO -->

                            <td>

                                <?php if (!empty(
                                    $f['data_pagamento']
                                )): ?>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $f['data_pagamento']
                                        )
                                    ) ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php

                                if ($f['status'] === 'Pago') {

                                    echo "
                                        <span class='badge bg-success'>
                                            Pago
                                        </span>
                                    ";

                                } elseif ($f['status'] === 'Cancelado') {

                                    echo "
                                        <span class='badge bg-danger'>
                                            Cancelado
                                        </span>
                                    ";

                                } else {

                                    echo "
                                        <span class='badge bg-warning text-dark'>
                                            Pendente
                                        </span>
                                    ";

                                }

                                ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            class="text-center text-muted py-5"
                        >

                            <i
                                class="bi bi-bar-chart"
                                style="font-size:32px;"
                            ></i>

                            <div class="mt-2">

                                Nenhuma movimentação encontrada
                                neste período.

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>