<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$financeiro = $conn->query("
SELECT *
FROM financeiro
ORDER BY data_movimento DESC
");

?>

<div class="d-flex justify-content-between align-items-center mb-4">

<h2 class="fw-bold">

Financeiro

</h2>

<button
class="btn btn-success"
data-bs-toggle="modal"
data-bs-target="#novoLancamento">

<i class="bi bi-plus-circle"></i>

Novo Lançamento

</button>

</div>

<div class="card-dashboard bg-white p-4">

<table class="table table-hover">

<thead>

<tr>

<th>Data</th>

<th>Tipo</th>

<th>Descrição</th>

<th>Categoria</th>

<th>Valor</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($financeiro as $item): ?>

<tr>

<td>

<?= date("d/m/Y",strtotime($item["data_movimento"])); ?>

</td>

<td>

<?= $item["tipo"]; ?>

</td>

<td>

<?= $item["descricao"]; ?>

</td>

<td>

<?= $item["categoria"]; ?>

</td>

<td>

R$

<?= number_format($item["valor"],2,",","."); ?>

</td>

<td>

<?= $item["status"]; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>