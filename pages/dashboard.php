<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

/* Cards */

$totalClientes = $conn->query("
SELECT COUNT(*) FROM clientes
")->fetchColumn();

$totalVeiculos = $conn->query("
SELECT COUNT(*) FROM veiculos
")->fetchColumn();

$totalOS = $conn->query("
SELECT COUNT(*) FROM ordens_servico
WHERE status NOT IN ('Entregue','Cancelado')
")->fetchColumn();

$totalOrcamentos = $conn->query("
SELECT COUNT(*) FROM orcamentos
")->fetchColumn();

$estoqueBaixo = $conn->query("
SELECT COUNT(*)
FROM pecas
WHERE estoque<=5
")->fetchColumn();

/* Últimas OS */

$ultimasOS = $conn->query("
SELECT
os.numero_os,
os.status,
c.nome,
v.modelo
FROM ordens_servico os
INNER JOIN clientes c
ON c.id_cliente=os.id_cliente
INNER JOIN veiculos v
ON v.id_veiculo=os.id_veiculo
ORDER BY os.id_os DESC
LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

/* Últimos Orçamentos */

$ultimosOrcamentos = $conn->query("
SELECT
o.id_orcamento,
c.nome,
o.status
FROM orcamentos o
INNER JOIN clientes c
ON c.id_cliente=o.id_cliente
ORDER BY o.id_orcamento DESC
LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);


/* Gráfico O.S. */

$graficoOS = $conn->query("
SELECT
status,
COUNT(*) total
FROM ordens_servico
GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

$labels=[];

$dados=[];

foreach($graficoOS as $g){

    $labels[]=$g['status'];

    $dados[]=$g['total'];

}

?>



<h1 class="fw-bold mb-4">
    Dashboard
</h1>





<!-- Cards -->
<div class="row g-4 mb-4">

    <div class="col-xl-3 col-md-6">
        <div class="card-dashboard">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Veículos em Serviço</small>
                    <h2 class="mt-2 fw-bold"><?= $totalOS ?></h2>
                </div>

                <div class="icon orange">
                    <i class="bi bi-car-front-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card-dashboard">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Serviços Agendados</small>
                    <h2 class="mt-2 fw-bold"><?= $totalVeiculos ?></h2>
                </div>

                <div class="icon blue">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card-dashboard">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Orçamentos</small>
                    <h2 class="mt-2 fw-bold"><?= $totalOrcamentos ?></h2>
                </div>

                <div class="icon green">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card-dashboard">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Peças Pendentes</small>
                    <h2 class="mt-2 fw-bold"><?= $estoqueBaixo ?></h2>
                </div>

                <div class="icon red">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Linha 2 -->
<div class="row g-4">

    <div class="col-lg-8">

        <div class="card-dashboard">

            <h4 class="mb-4">
                Serviços do Mês
            </h4>

            <canvas id="graficoServicos" height="120"></canvas>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card-dashboard">

            <h4 class="mb-4">
                Agenda de Hoje
            </h4>

            <table class="table table-hover">

                <tr>
                    <td>08:00</td>
                    <td>HB20</td>
                    <td>Pintura</td>
                </tr>

                <tr>
                    <td>10:30</td>
                    <td>Corolla</td>
                    <td>Funilaria</td>
                </tr>

                <tr>
                    <td>14:00</td>
                    <td>Onix</td>
                    <td>Polimento</td>
                </tr>

                <tr>
                    <td>16:00</td>
                    <td>Hilux</td>
                    <td>Entrega</td>
                </tr>

            </table>

        </div>

    </div>

</div>

<!-- Linha 3 -->
<div class="row mt-4">

    <div class="col-lg-6">

        <div class="card-dashboard">

            <h4 class="mb-4">
                Últimos Orçamentos
            </h4>

            <table class="table table-hover">

                <thead>

                <tr>

                    <th>Cliente</th>
                    <th>Status</th>

                </tr>

                </thead>

                <tbody>

                <tr>

                    <td>João Pedro</td>

                    <td>
                        <span class="badge bg-warning">
                            Retorno
                        </span>
                    </td>

                </tr>

                <tr>

                    <td>Carlos Henrique</td>

                    <td>
                        <span class="badge bg-success">
                            Aprovado
                        </span>
                    </td>

                </tr>

                <tr>

                    <td>Pedro Alves</td>

                    <td>
                        <span class="badge bg-danger">
                            Negado
                        </span>
                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card-dashboard">

            <h4 class="mb-4">
                Últimos Orçamentos

            </h4>

            <table class="table table-hover">

<thead>

<tr>

<th>Cliente</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($ultimosOrcamentos as $orc): ?>

<tr>

<td>

<?= htmlspecialchars($orc['nome']); ?>

</td>

<td>

<span class="badge bg-warning">

<?= $orc['status']; ?>

</span>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

        </div>

    </div>

</div>



<script>

const ctx=document.getElementById('graficoServicos');

if(ctx){

new Chart(ctx,{

type:'doughnut',

data:{

labels:<?= json_encode($labels) ?>,

datasets:[{

data:<?= json_encode($dados) ?>

}]

},

options:{

responsive:true,

plugins:{

legend:{

position:'bottom'

}

}

}

});

}

</script>