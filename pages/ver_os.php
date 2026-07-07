<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$id = $_GET['id'];

$sql = $conn->prepare("
SELECT
os.*,
c.nome,
c.telefone,
v.modelo,
v.placa
FROM ordens_servico os
INNER JOIN clientes c
ON c.id_cliente=os.id_cliente
INNER JOIN veiculos v
ON v.id_veiculo=os.id_veiculo
WHERE os.id_os=?
");

$sql->execute([$id]);

$os = $sql->fetch(PDO::FETCH_ASSOC);

$pecas = $conn->prepare("
SELECT
op.*,
p.peca
FROM os_pecas op
INNER JOIN pecas p
ON p.id_peca=op.id_peca
WHERE op.id_os=?
");

$pecas->execute([$id]);

$lista = $pecas->fetchAll(PDO::FETCH_ASSOC);

$servicos = $conn->prepare("

SELECT *

FROM os_servicos

WHERE id_os=?

");

$servicos->execute([$id]);

$listaServicos = $servicos->fetchAll(PDO::FETCH_ASSOC);

?>

<h2 class="fw-bold mb-4">

Ordem de Serviço

<?= $os['numero_os']; ?>

</h2>

<div class="card-dashboard bg-white p-4">

<h5>

Cliente

</h5>

<p>

<strong>

<?= $os['nome']; ?>

</strong>

</p>

<p>

<?= $os['telefone']; ?>

</p>

<hr>

<h5>

Veículo

</h5>

<p>

<?= $os['modelo']; ?>

-

<?= $os['placa']; ?>

</p>

<hr>

<h5>

Peças Utilizadas

</h5>

<table class="table">

<thead>

<tr>

<th>Peça</th>

<th>Qtd</th>

<th>Valor</th>

<th>Total</th>

</tr>

</thead>

<tbody>

<?php foreach($lista as $item): ?>

<tr>

<td><?= $item['peca']; ?></td>

<td><?= $item['quantidade']; ?></td>

<td>

R$

<?= number_format($item['valor_unitario'],2,",","."); ?>

</td>

<td>

R$

<?= number_format($item['valor_total'],2,",","."); ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<h4 class="mt-4">

Serviços Executados

</h4>

<table class="table table-bordered">

<thead>

<tr>

<th>Serviço</th>

<th>Qtd</th>

<th>Valor</th>

<th>Total</th>

</tr>

</thead>

<tbody>

<?php foreach($listaServicos as $servico): ?>

<tr>

<td>

<?= $servico['servico']; ?>

</td>

<td>

<?= $servico['quantidade']; ?>

</td>

<td>

R$

<?= number_format($servico['valor_unitario'],2,",","."); ?>

</td>

<td>

R$

<?= number_format($servico['valor_total'],2,",","."); ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<div class="text-end mt-4">

<h5>

Valor das Peças:

R$

<?= number_format($os['valor_pecas'],2,",","."); ?>

</h5>

<h5>

Valor dos Serviços:

R$

<?= number_format($os['valor_servicos'],2,",","."); ?>

</h5>

<h3 class="text-success">

TOTAL GERAL

R$

<?= number_format($os['valor_total'],2,",","."); ?>

</h3>

</div>


<hr class="mt-4">

<h4>

Fotos da Ordem de Serviço

</h4>

<form
action="actions/upload_foto_os.php"
method="POST"
enctype="multipart/form-data">

<input
type="hidden"
name="id_os"
value="<?= $os['id_os']; ?>">

<div class="row">

<div class="col-md-5">

<input
type="file"
name="foto"
class="form-control"
required>

</div>

<div class="col-md-5">

<input
type="text"
name="descricao"
class="form-control"
placeholder="Ex.: Antes da pintura">

</div>

<div class="col-md-2">

<button
class="btn btn-success w-100">

Enviar

</button>

</div>

</div>

</form>

<?php

$fotos = $conn->prepare("

SELECT *

FROM os_fotos

WHERE id_os=?

ORDER BY id_foto DESC

");

$fotos->execute([$id]);

?>

<div class="row mt-4">

<?php foreach($fotos as $foto): ?>

<div class="col-md-3 mb-3">

<div class="card">

<img

src="uploads/os/<?= $foto['foto']; ?>"

class="card-img-top"

style="height:220px;object-fit:cover;">

<div class="card-body">

<small>

<?= $foto['descricao']; ?>

</small>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>