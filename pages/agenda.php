<?php

require_once "config/database.php";

$db=new Database();
$conn=$db->connect();

$sql=$conn->query("

SELECT

agenda.*,

clientes.nome,

veiculos.modelo,

veiculos.placa

FROM agenda

INNER JOIN clientes

ON clientes.id_cliente=agenda.id_cliente

INNER JOIN veiculos

ON veiculos.id_veiculo=agenda.id_veiculo

ORDER BY data,hora

");

$agenda=$sql->fetchAll(PDO::FETCH_ASSOC);

?>

<h2 class="fw-bold mb-4">

Agenda

</h2>

<div class="card-dashboard bg-white p-4">

<div class="d-flex justify-content-between mb-4">

<h5>

Agenda de Serviços

</h5>

<button
class="btn btn-warning"
data-bs-toggle="modal"
data-bs-target="#novaAgenda">

Novo Agendamento

</button>

</div>

<table class="table table-hover">

<thead>

<tr>

<th>Data</th>

<th>Hora</th>

<th>Cliente</th>

<th>Veículo</th>

<th>Título</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($agenda as $a): ?>

<tr>

<td><?=date('d/m/Y',strtotime($a['data']))?></td>

<td><?=substr($a['hora'],0,5)?></td>

<td><?=$a['nome']?></td>

<td><?=$a['modelo']?> - <?=$a['placa']?></td>

<td><?=$a['titulo']?></td>

<td>

<span class="badge bg-success">

<?=$a['status']?>

</span>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>