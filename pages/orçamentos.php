<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->query("
SELECT
o.*,
c.nome,
v.marca,
v.modelo,
v.placa

FROM orcamentos o

INNER JOIN clientes c
ON c.id_cliente = o.id_cliente

INNER JOIN veiculos v
ON v.id_veiculo = o.id_veiculo

ORDER BY o.id_orcamento DESC
");

$orcamentos = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<h2 class="fw-bold mb-4">Orçamentos</h2>

<div class="card-dashboard">

<div class="d-flex justify-content-between mb-4">

<h4>Lista de Orçamentos</h4>

<button
class="btn btn-warning"
data-bs-toggle="modal"
data-bs-target="#novoOrcamento">

Novo Orçamento

</button>

</div>

<table class="table table-hover">

<thead>

<tr>

<th>Nº</th>
<th>Cliente</th>
<th>Veículo</th>
<th>Valor</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($orcamentos as $o): ?>

<tr>

<td><?= $o['numero']; ?></td>

<td><?= $o['nome']; ?></td>

<td><?= $o['marca']." ".$o['modelo']; ?></td>

<td>
R$
<?= number_format($o['valor_total'],2,",","."); ?>
</td>

<td>

<span class="badge bg-success">

<?= $o['status']; ?>

</span>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<div class="modal fade" id="novoOrcamento">

<div class="modal-dialog modal-xl">

<div class="modal-content">

<form
action="actions/salvar_orcamento.php"
method="POST">

<div class="modal-header">

<h5>Novo Orçamento</h5>

</div>

<div class="modal-body">

<div class="row">

<div class="col-md-3">

<label>Número</label>

<input

type="text"

name="numero"

class="form-control"

value="<?= date('YmdHis')?>"

readonly>

</div>

<div class="col-md-5">

<label>Cliente</label>

<select
name="id_cliente"
class="form-select">

<?php

$clientes = $conn->query("SELECT * FROM clientes");

foreach($clientes as $c){

?>

<option value="<?=$c['id_cliente']?>">

<?=$c['nome']?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-4">

<label>Veículo</label>

<select
name="id_veiculo"
class="form-select">

<?php

$veiculos = $conn->query("SELECT * FROM veiculos");

foreach($veiculos as $v){

?>

<option value="<?=$v['id_veiculo']?>">

<?=$v['marca']?> <?=$v['modelo']?> - <?=$v['placa']?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-4 mt-3">

<label>Valor</label>

<input
type="number"
step="0.01"
name="valor_total"
class="form-control">

</div>

<div class="col-md-4 mt-3">

<label>Status</label>

<select
name="status"
class="form-select">

<option>Novo</option>
<option>Enviado</option>
<option>Negociação</option>
<option>Aprovado</option>
<option>Reprovado</option>
<option>Convertido</option>

</select>

</div>

<div class="col-md-4 mt-3">

<label>Seguradora</label>

<input
type="text"
name="seguradora"
class="form-control">

</div>

<div class="col-12 mt-3">

<label>Observações</label>

<textarea
name="observacoes"
rows="4"
class="form-control"></textarea>

</div>