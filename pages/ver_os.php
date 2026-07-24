<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

/* =========================================
   VALIDAR ID
========================================= */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Ordem de Serviço não encontrada.");
}

/* =========================================
   BUSCAR ORDEM
========================================= */

$sql = $conn->prepare("
    SELECT
        os.*,

        c.nome,
        c.telefone,
        c.cpf,
        c.email,
        c.endereco,

        v.modelo,
        v.marca,
        v.placa,
        v.ano,
        v.cor

    FROM ordens_servico os

    INNER JOIN clientes c
        ON c.id_cliente = os.id_cliente

    INNER JOIN veiculos v
        ON v.id_veiculo = os.id_veiculo

    WHERE os.id_os = ?
");

$sql->execute([$id]);

$os = $sql->fetch(PDO::FETCH_ASSOC);

if (!$os) {
    die("Ordem de Serviço não encontrada.");
}

/* =========================================
   PEÇAS
========================================= */

$sqlPecas = $conn->prepare("
    SELECT
        op.*,
        p.peca

    FROM os_pecas op

    INNER JOIN pecas p
        ON p.id_peca = op.id_peca

    WHERE op.id_os = ?
");

$sqlPecas->execute([$id]);

$pecas = $sqlPecas->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   SERVIÇOS
========================================= */

$sqlServicos = $conn->prepare("
    SELECT *
    FROM os_servicos
    WHERE id_os = ?
    ORDER BY id DESC
");

$sqlServicos->execute([$id]);

$servicos = $sqlServicos->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   FOTOS
========================================= */

$sqlFotos = $conn->prepare("
    SELECT *
    FROM fotos_os
    WHERE id_os = ?
    ORDER BY id_foto DESC
");

$sqlFotos->execute([$id]);

$fotos = $sqlFotos->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   HISTÓRICO
========================================= */

$sqlHistorico = $conn->prepare("
    SELECT *
    FROM historico_os
    WHERE id_os = ?
    ORDER BY data_hora DESC
");

$sqlHistorico->execute([$id]);

$historico = $sqlHistorico->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   FUNÇÕES
========================================= */

function e($valor)
{
    return htmlspecialchars(
        (string)($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function dataBR($data)
{
    if (empty($data) || $data === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($data);

    if (!$timestamp) {
        return '-';
    }

    return date('d/m/Y', $timestamp);
}

function moeda($valor)
{
    return 'R$ ' . number_format(
        (float)($valor ?? 0),
        2,
        ',',
        '.'
    );
}

/* =========================================
   PROGRESSO
========================================= */

$etapas = [
    'Recebido'   => 25,
    'Em Serviço' => 50,
    'Finalizado' => 75,
    'Entregue'   => 100
];

$progresso = $etapas[$os['status']] ?? 0;

?>

<!-- ========================================
     CABEÇALHO
========================================= -->

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

    <div>

        <h2 class="fw-bold mb-1">
            <?= e($os['numero_os']); ?>
        </h2>

        <small class="text-muted">
            Detalhes da Ordem de Serviço
        </small>

    </div>

    <div class="d-flex gap-2 flex-wrap">

        <a
            href="actions/imprimir_os.php?id=<?= (int)$os['id_os']; ?>"
            target="_blank"
            class="btn btn-success"
        >
            <i class="bi bi-printer"></i>
            Imprimir
        </a>

        <a
            href="index.php?page=editar_os&id=<?= (int)$os['id_os']; ?>"
            class="btn btn-warning"
        >
            <i class="bi bi-pencil-square"></i>
            Editar O.S.
        </a>

        <a
            href="index.php?page=os"
            class="btn btn-secondary"
        >
            Voltar
        </a>

    </div>

</div>


<div class="row">

<!-- ========================================
     COLUNA PRINCIPAL
========================================= -->

<div class="col-lg-8">

    <!-- DADOS DA ORDEM -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-3">
            Dados da Ordem
        </h4>

        <div class="mb-4">

            <div class="d-flex justify-content-between mb-2">

                <strong>Progresso da Ordem</strong>

                <strong>
                    <?= $progresso; ?>%
                </strong>

            </div>

            <div class="progress" style="height:22px;">

                <div
                    class="progress-bar bg-warning progress-bar-striped progress-bar-animated"
                    role="progressbar"
                    style="width: <?= $progresso; ?>%;"
                    aria-valuenow="<?= $progresso; ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <?= e($os['status']); ?>
                </div>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table">

                <tr>
                    <th width="220">Status</th>
                    <td><?= e($os['status']); ?></td>
                </tr>

                <tr>
                    <th>Etapa</th>
                    <td><?= e($os['etapa']); ?></td>
                </tr>

                <tr>
                    <th>Prioridade</th>
                    <td><?= e($os['prioridade']); ?></td>
                </tr>

                <tr>
                    <th>Entrada</th>
                    <td><?= dataBR($os['entrada']); ?></td>
                </tr>

                <tr>
                    <th>Previsão</th>
                    <td><?= dataBR($os['previsao']); ?></td>
                </tr>

                <tr>
                    <th>Observações</th>

                    <td>
                        <?php if (!empty($os['observacoes'])): ?>

                            <?= nl2br(e($os['observacoes'])); ?>

                        <?php else: ?>

                            <span class="text-muted">
                                Nenhuma observação.
                            </span>

                        <?php endif; ?>
                    </td>

                </tr>

            </table>

        </div>

    </div>


    <!-- CLIENTE -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-3">
            Cliente
        </h4>

        <div class="table-responsive">

            <table class="table">

                <tr>
                    <th width="220">Nome</th>
                    <td><?= e($os['nome']); ?></td>
                </tr>

                <tr>
                    <th>Telefone</th>
                    <td><?= e($os['telefone']); ?></td>
                </tr>

                <tr>
                    <th>CPF</th>
                    <td><?= e($os['cpf']); ?></td>
                </tr>

                <tr>
                    <th>E-mail</th>
                    <td><?= e($os['email']); ?></td>
                </tr>

                <tr>
                    <th>Endereço</th>
                    <td><?= e($os['endereco']); ?></td>
                </tr>

            </table>

        </div>

    </div>


    <!-- VEÍCULO -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-3">
            Veículo
        </h4>

        <div class="table-responsive">

            <table class="table">

                <tr>
                    <th width="220">Modelo</th>
                    <td><?= e($os['modelo']); ?></td>
                </tr>

                <tr>
                    <th>Marca</th>
                    <td><?= e($os['marca']); ?></td>
                </tr>

                <tr>
                    <th>Placa</th>
                    <td><?= e($os['placa']); ?></td>
                </tr>

                <tr>
                    <th>Ano</th>
                    <td><?= e($os['ano']); ?></td>
                </tr>

                <tr>
                    <th>Cor</th>
                    <td><?= e($os['cor']); ?></td>
                </tr>

            </table>

        </div>

    </div>


    <!-- PEÇAS -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-3">
            Peças Utilizadas
        </h4>

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-light">

                    <tr>

                        <th>Peça</th>

                        <th width="120">
                            Quantidade
                        </th>

                        <th width="150">
                            Valor Unitário
                        </th>

                        <th width="150">
                            Total
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($pecas)): ?>

                    <?php foreach ($pecas as $peca): ?>

                        <tr>

                            <td>
                                <?= e($peca['peca']); ?>
                            </td>

                            <td>
                                <?= e($peca['quantidade']); ?>
                            </td>

                            <td>
                                <?= moeda($peca['valor_unitario'] ?? 0); ?>
                            </td>

                            <td>
                                <?= moeda($peca['valor_total'] ?? 0); ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="4"
                            class="text-center text-muted"
                        >
                            Nenhuma peça cadastrada.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- SERVIÇOS -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-3">
            Serviços Executados
        </h4>

        <div class="table-responsive">

            <table class="table table-hover table-bordered">

                <thead class="table-light">

                    <tr>

                        <th>Descrição</th>

                        <th>Funcionário</th>

                        <th width="90">
                            Horas
                        </th>

                        <th width="140">
                            Valor
                        </th>

                        <th width="140">
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($servicos)): ?>

                    <?php foreach ($servicos as $servico): ?>

                        <tr>

                            <td>
                                <?= e($servico['descricao']); ?>
                            </td>

                            <td>
                                <?= e($servico['funcionario']); ?>
                            </td>

                            <td>
                                <?= e($servico['horas']); ?>
                            </td>

                            <td>
                                <?= moeda($servico['valor']); ?>
                            </td>

                            <td>
                                <?= e($servico['status']); ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="5"
                            class="text-center text-muted"
                        >
                            Nenhum serviço cadastrado.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- ========================================
     COLUNA DIREITA
========================================= -->

<div class="col-lg-4">

    <!-- RESUMO FINANCEIRO -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-3">
            Resumo Financeiro
        </h4>

        <div class="table-responsive">

            <table class="table">

                <tr>

                    <th>
                        Valor das Peças
                    </th>

                    <td class="text-end">
                        <?= moeda($os['valor_pecas']); ?>
                    </td>

                </tr>

                <tr>

                    <th>
                        Mão de Obra
                    </th>

                    <td class="text-end">
                        <?= moeda($os['valor_mao_obra']); ?>
                    </td>

                </tr>

                <tr class="table-warning">

                    <th>
                        Total
                    </th>

                    <th class="text-end">
                        <?= moeda($os['valor_total']); ?>
                    </th>

                </tr>

            </table>

        </div>

    </div>


    <!-- FOTOS -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-3">
            Fotos da Ordem
        </h4>

        <div class="row">

        <?php if (!empty($fotos)): ?>

            <?php foreach ($fotos as $foto): ?>

                <div class="col-md-6 col-lg-12 col-xl-6 mb-3">

                    <a
                        href="<?= e($foto['foto']); ?>"
                        target="_blank"
                    >

                        <img
                            src="<?= e($foto['foto']); ?>"
                            class="img-fluid rounded shadow-sm border"
                            alt="Foto da ordem de serviço"
                        >

                    </a>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="alert alert-light border mb-0">

                    Nenhuma foto cadastrada.

                </div>

            </div>

        <?php endif; ?>

        </div>

    </div>


    <!-- HISTÓRICO -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-4">
            Histórico da Ordem
        </h4>

        <?php if (!empty($historico)): ?>

            <div class="timeline">

                <?php foreach ($historico as $item): ?>

                    <div class="border-start border-3 border-warning ps-3 mb-4">

                        <small class="text-muted">

                            <?php
                            if (!empty($item['data_hora'])) {
                                echo date(
                                    'd/m/Y H:i',
                                    strtotime($item['data_hora'])
                                );
                            } else {
                                echo '-';
                            }
                            ?>

                        </small>

                        <h6 class="mb-1 mt-1">
                            <?= e($item['acao']); ?>
                        </h6>

                        <p class="mb-1">
                            <?= e($item['descricao']); ?>
                        </p>

                        <small class="text-secondary">
                            <?= e($item['usuario']); ?>
                        </small>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="alert alert-light border">

                Nenhum histórico encontrado.

            </div>

        <?php endif; ?>

    </div>


    <!-- AÇÕES -->

    <div class="card-dashboard p-4 mb-4">

        <h4 class="mb-4">
            Ações
        </h4>

        <div class="d-grid gap-2">

            <a
                href="actions/imprimir_os.php?id=<?= (int)$os['id_os']; ?>"
                target="_blank"
                class="btn btn-success"
            >
                <i class="bi bi-printer"></i>
                Imprimir Ordem
            </a>

            <a
                href="index.php?page=upload_foto_os&id=<?= (int)$os['id_os']; ?>"
                class="btn btn-primary"
            >
                <i class="bi bi-camera"></i>
                Adicionar Foto
            </a>

            <a
                href="index.php?page=adicionar_servico_os&id=<?= (int)$os['id_os']; ?>"
                class="btn btn-info text-white"
            >
                <i class="bi bi-tools"></i>
                Adicionar Serviço
            </a>

            <?php if (($os['status'] ?? '') !== 'Finalizado' && ($os['status'] ?? '') !== 'Entregue'): ?>

                <a
                    href="actions/finalizar_os.php?id=<?= (int)$os['id_os']; ?>"
                    class="btn btn-warning"
                >
                    <i class="bi bi-check-circle"></i>
                    Finalizar Ordem
                </a>

            <?php endif; ?>


            <?php if (($os['status'] ?? '') === 'Finalizado'): ?>

                <a
                    href="actions/entregar_os.php?id=<?= (int)$os['id_os']; ?>"
                    class="btn btn-dark"
                >
                    <i class="bi bi-car-front-fill"></i>
                    Entregar Veículo
                </a>

            <?php endif; ?>

        </div>

    </div>

</div>

</div>