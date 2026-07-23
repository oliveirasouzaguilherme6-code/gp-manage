<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

if (!isset($_GET['id'])) {
    die("Ordem de Serviço não encontrada.");
}

$id = intval($_GET['id']);

$sql = $conn->prepare("
SELECT
    os.*,
 c.nome,
c.telefone,
c.cpf,
c.email,
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

WHERE os.id_os=?
");

$sql->execute([$id]);

$os = $sql->fetch(PDO::FETCH_ASSOC);

if(!$os){
    die("Ordem de Serviço não encontrada.");
}

/* PEÇAS */

$sqlPecas = $conn->prepare("

SELECT

op.*,
p.peca

FROM os_pecas op

INNER JOIN pecas p
ON p.id_peca=op.id_peca

WHERE op.id_os=?

");

$sqlPecas->execute([$id]);

$pecas = $sqlPecas->fetchAll(PDO::FETCH_ASSOC);

/* SERVIÇOS */

$sqlServicos = $conn->prepare("

SELECT *

FROM os_servicos

WHERE id_os=?

ORDER BY id DESC

");

$sqlServicos->execute([$id]);

$servicos = $sqlServicos->fetchAll(PDO::FETCH_ASSOC);

/* FOTOS */

$sqlFotos = $conn->prepare("

SELECT *

FROM fotos_os

WHERE id_os=?

ORDER BY id_foto DESC

");

$sqlFotos->execute([$id]);

$fotos = $sqlFotos->fetchAll(PDO::FETCH_ASSOC);


/* HISTÓRICO */

$sqlHistorico = $conn->prepare("
SELECT *
FROM historico_os
WHERE id_os = ?
ORDER BY data_hora DESC
");

$sqlHistorico->execute([$id]);

$historico = $sqlHistorico->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="fw-bold">

<?= $os['numero_os']; ?>

</h2>

<small class="text-muted">

Detalhes da Ordem de Serviço

</small>

</div>

<div>

<a
href="actions/imprimir_os.php?id=<?= $os['id_os']; ?>"
target="_blank"
class="btn btn-success">

<i class="bi bi-printer"></i>

Imprimir

</a>

<a
href="index.php?page=editar_os&id=<?= $os['id_os']; ?>"
class="btn btn-warning">

<i class="bi bi-pencil-square"></i>

Editar O.S.

</a>

<a
href="index.php?page=os"
class="btn btn-secondary">

Voltar

</a>

</div>

</div>

<div class="row">

<div class="col-lg-8">

<div class="card-dashboard p-4 mb-4">

<h4 class="mb-3">

Dados da Ordem

</h4>


<?php

$etapas = [
    "Recebido" => 25,
    "Em Serviço" => 50,
    "Finalizado" => 75,
    "Entregue" => 100
];

$progresso = $etapas[$os['status']] ?? 0;

?>

<div class="mb-4">

    <div class="d-flex justify-content-between mb-2">

        <strong>Progresso da Ordem</strong>

        <strong><?= $progresso ?>%</strong>

    </div>

    <div class="progress" style="height:22px;">

        <div
        class="progress-bar bg-warning progress-bar-striped progress-bar-animated"
        style="width:<?= $progresso ?>%">

            <?= htmlspecialchars($os['status']) ?>

        </div>

    </div>

</div>

<table class="table">

<tr>

<th width="220">

Status

</th>

<td>

<?= $os['status']; ?>

</td>

</tr>

<tr>

<th>

Etapa

</th>

<td>

<?= $os['etapa']; ?>

</td>

</tr>

<tr>

<th>

Prioridade

</th>

<td>

<?= $os['prioridade']; ?>

</td>

</tr>

<tr>

<th>

Entrada

</th>

<td>

<?= date("d/m/Y",strtotime($os['entrada'])); ?>

</td>

</tr>

<tr>

<th>

Previsão

</th>

<td>

<?= date("d/m/Y",strtotime($os['previsao'])); ?>

</td>

</tr>

<tr>

<th>

Observações

</th>

<td>

<?= nl2br(htmlspecialchars($os['observacoes'])); ?>

</td>

</tr>

</table>

</div>





<div class="card-dashboard p-4 mb-4">

    <h4 class="mb-3">
        Cliente
    </h4>

    <table class="table">

        <tr>
            <th width="220">Nome</th>
            <td><?= htmlspecialchars($os['nome']); ?></td>
        </tr>

        <tr>
            <th>Telefone</th>
            <td><?= htmlspecialchars($os['telefone']); ?></td>
        </tr>

        <tr>
            <th>CPF</th>
            <td><?= htmlspecialchars($os['cpf']); ?></td>
        </tr>

        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($os['email']); ?></td>
        </tr>

        <tr>
            <th>Endereço</th>
            <td><?= htmlspecialchars($os['endereco']); ?></td>
        </tr>

    </table>

</div>

<div class="card-dashboard p-4 mb-4">

    <h4 class="mb-3">
        Veículo
    </h4>

    <table class="table">

        <tr>
            <th width="220">Modelo</th>
            <td><?= htmlspecialchars($os['modelo']); ?></td>
        </tr>

        <tr>
            <th>Marca</th>
            <td><?= htmlspecialchars($os['marca']); ?></td>
        </tr>

        <tr>
            <th>Placa</th>
            <td><?= htmlspecialchars($os['placa']); ?></td>
        </tr>

        <tr>
            <th>Ano</th>
            <td><?= htmlspecialchars($os['ano']); ?></td>
        </tr>

        <tr>
            <th>Cor</th>
            <td><?= htmlspecialchars($os['cor']); ?></td>
        </tr>

    </table>

</div>

<div class="card-dashboard p-4 mb-4">

    <h4 class="mb-3">
        Peças Utilizadas
    </h4>

    <table class="table table-bordered table-hover">

        <thead class="table-light">

            <tr>

                <th>Peça</th>
                <th width="120">Quantidade</th>

            </tr>

        </thead>

        <tbody>

        <?php if(count($pecas)>0): ?>

            <?php foreach($pecas as $peca): ?>

                <tr>

                    <td>

                        <?= htmlspecialchars($peca['peca']); ?>

                    </td>

                    <td>

                        <?= $peca['quantidade']; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="2" class="text-center text-muted">

                    Nenhuma peça cadastrada.

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

<div class="card-dashboard p-4">

    <h4 class="mb-3">

        Serviços Executados

    </h4>

    <table class="table table-hover table-bordered">

        <thead class="table-light">

            <tr>

                <th>Descrição</th>

                <th>Funcionário</th>

                <th width="90">Horas</th>

                <th width="140">Valor</th>

                <th width="140">Status</th>

            </tr>

        </thead>

        <tbody>

        <?php if(count($servicos)>0): ?>

            <?php foreach($servicos as $servico): ?>

                <tr>

                    <td><?= htmlspecialchars($servico['descricao']); ?></td>

                    <td><?= htmlspecialchars($servico['funcionario']); ?></td>

                    <td><?= $servico['horas']; ?></td>

                    <td>

                        R$
                        <?= number_format($servico['valor'],2,",","."); ?>

                    </td>

                    <td>

                        <?= $servico['status']; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="5" class="text-center text-muted">

                    Nenhum serviço cadastrado.

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</div>

<div class="col-lg-4">



<div class="card-dashboard p-4 mb-4">

    <h4 class="mb-3">

        Fotos da Ordem

    </h4>

    <div class="row">

    <?php if(count($fotos)>0): ?>

        <?php foreach($fotos as $foto): ?>

        <div class="col-md-6 mb-3">

            <a href="<?= $foto['foto']; ?>" target="_blank">

                <img
                src="<?= $foto['foto']; ?>"
                class="img-fluid rounded shadow-sm border">

            </a>

        </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="col-12">

            <div class="alert alert-light border">

                Nenhuma foto cadastrada.

            </div>

        </div>

    <?php endif; ?>

    </div>

</div>

<div class="card-dashboard p-4 mb-4">

    <h4 class="mb-3">

        Resumo Financeiro

    </h4>

    <table class="table">

        <tr>

            <th>Valor das Peças</th>

            <td class="text-end">

                R$ <?= number_format($os['valor_pecas'],2,",","."); ?>

            </td>

        </tr>

        <tr>

            <th>Mão de Obra</th>

            <td class="text-end">

                R$ <?= number_format($os['valor_mao_obra'],2,",","."); ?>

            </td>

        </tr>

        <tr class="table-warning">

            <th>Total</th>

            <th class="text-end">

                R$ <?= number_format($os['valor_total'],2,",","."); ?>

            </th>

        </tr>

    </table>

</div>

<div class="card-dashboard p-4">

    <h4 class="mb-4">

        Ações

    </h4>




<div class="card-dashboard p-4 mb-4">

    <h4 class="mb-4">

        Histórico da Ordem

    </h4>

    <?php if(count($historico)>0): ?>

        <div class="timeline">

        <?php foreach($historico as $item): ?>

            <div class="border-start border-3 border-warning ps-3 mb-4">

                <small class="text-muted">

                    <?= date("d/m/Y H:i",strtotime($item['data_hora'])); ?>

                </small>

                <h6 class="mb-1 mt-1">

                    <?= htmlspecialchars($item['acao']); ?>

                </h6>

                <p class="mb-1">

                    <?= htmlspecialchars($item['descricao']); ?>

                </p>

                <small class="text-secondary">

                    <?= htmlspecialchars($item['usuario']); ?>

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



    <div class="d-grid gap-2">

        <a
        href="actions/imprimir_os.php?id=<?= $os['id_os']; ?>"
        target="_blank"
        class="btn btn-success">

            <i class="bi bi-printer"></i>

            Imprimir Ordem

        </a>

        <a
        href="index.php?page=upload_foto_os&id=<?= $os['id_os']; ?>"
        class="btn btn-primary">

            <i class="bi bi-camera"></i>

            Adicionar Foto

        </a>

        <a
        href="index.php?page=adicionar_servico_os&id=<?= $os['id_os']; ?>"
        class="btn btn-info text-white">

            <i class="bi bi-tools"></i>

            Adicionar Serviço

        </a>

        <a
        href="actions/finalizar_os.php?id=<?= $os['id_os']; ?>"
        class="btn btn-warning">

            <i class="bi bi-check-circle"></i>

            Finalizar Ordem

        </a>

        <a
        href="actions/entregar_os.php?id=<?= $os['id_os']; ?>"
        class="btn btn-dark">

            <i class="bi bi-car-front-fill"></i>

            Entregar Veículo

        </a>

    </div>

</div>

</div>

</div>