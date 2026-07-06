<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->query("
SELECT
v.*,
c.nome
FROM veiculos v
INNER JOIN clientes c
ON c.id_cliente = v.id_cliente
ORDER BY id_veiculo DESC
");

$veiculos = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between mb-4">

<h2>

Veículos

</h2>

<button
class="btn btn-warning"
data-bs-toggle="modal"
data-bs-target="#novoVeiculo">

Novo Veículo

</button>

</div>

<div class="card-dashboard bg-white p-4">

<table class="table table-hover">

<thead>

<tr>

<th>Cliente</th>

<th>Veículo</th>

<th>Placa</th>

<th>Ano</th>

<th></th>

</tr>

</thead>

<tbody>

<?php foreach($veiculos as $v): ?>

<tr>

<td>

<?= $v['nome']; ?>

</td>

<td>

<?= $v['marca']; ?>

<?= $v['modelo']; ?>

</td>

<td>

<?= $v['placa']; ?>

</td>

<td>

<?= $v['ano']; ?>

</td>

<td>

<button class="btn btn-primary btn-sm">

Editar

</button>

<button class="btn btn-danger btn-sm">

Excluir

</button>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<div
class="modal fade"
id="novoVeiculo">

<div class="modal-dialog">

<div class="modal-content">

<form
action="actions/salvar_veiculo.php"
method="POST">

<div class="modal-header">

<h5>

Cadastrar Veículo

</h5>

</div>

<div class="modal-body">

<label>

Cliente

</label>

<select
name="id_cliente"
class="form-select">

<?php

$clientes=$conn->query("SELECT * FROM clientes");

foreach($clientes as $c){

?>

<option
value="<?=$c['id_cliente']?>">

<?=$c['nome']?>

</option>

<?php } ?>

</select>

<label class="mt-3">

Marca

</label>

<input
type="text"
name="marca"
class="form-control">

<label class="mt-3">

Modelo

</label>

<input
type="text"
name="modelo"
class="form-control">

<label class="mt-3">

Ano

</label>

<input
type="number"
name="ano"
class="form-control">

<label class="mt-3">

Placa

</label>

<input
type="text"
name="placa"
class="form-control">

<label class="mt-3">

Quilometragem

</label>

<input
type="number"
name="km"
class="form-control">

<label class="mt-3">

Cor

</label>

<input
type="text"
name="cor"
class="form-control">

</div>

<div class="modal-footer">

<button
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancelar

</button>

<button
class="btn btn-warning">

Salvar

</button>

</div>

</form>

</div>

</div>

</div>