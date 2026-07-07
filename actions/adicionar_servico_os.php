<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sqlServico = $conn->prepare("
SELECT valor
FROM servicos
WHERE id_servico=?
");

$sqlServico->execute([$_POST['id_servico']]);

$servico = $sqlServico->fetch(PDO::FETCH_ASSOC);

$valor = $servico['valor'];
$qtd = $_POST['quantidade'];
$subtotal = $valor * $qtd;

$sql = $conn->prepare("
INSERT INTO os_servicos
(
id_os,
id_servico,
descricao,
quantidade,
valor,
subtotal
)
VALUES
(?,?,?,?,?,?)
");

$sql->execute([

$_POST['id_os'],
$_POST['id_servico'],
$_POST['descricao'],
$qtd,
$valor,
$subtotal

]);

header("Location: ../index.php?page=os_detalhes&id=".$_POST['id_os']);
exit;

<?php

$servicos = $conn->query("
SELECT *
FROM servicos
ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

?>

<form action="actions/adicionar_servico_os.php" method="POST">

<input
type="hidden"
name="id_os"
value="<?=$id?>">

<div class="row">

<div class="col-md-5">

<select
name="id_servico"
class="form-select">

<?php foreach($servicos as $s): ?>

<option value="<?=$s['id_servico']?>">

<?=$s['nome']?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-3">

<input
type="number"
name="quantidade"
value="1"
class="form-control">

</div>

<div class="col-md-4">

<button class="btn btn-warning w-100">

Adicionar Serviço

</button>

</div>

</div>

<input
type="text"
name="descricao"
class="form-control mt-3"
placeholder="Descrição">

</form>

<?php

$sql = $conn->prepare("
SELECT
o.*,
s.nome
FROM os_servicos o
INNER JOIN servicos s
ON s.id_servico=o.id_servico
WHERE id_os=?
");

$sql->execute([$id]);

?>

<table class="table mt-4">

<thead>

<tr>

<th>Serviço</th>

<th>Qtd</th>

<th>Valor</th>

<th>Subtotal</th>

</tr>

</thead>

<tbody>

<?php

$total = 0;

foreach($sql as $item){

$total += $item['subtotal'];

?>

<tr>

<td><?=$item['nome']?></td>

<td><?=$item['quantidade']?></td>

<td>R$ <?=number_format($item['valor'],2,",",".")?></td>

<td>R$ <?=number_format($item['subtotal'],2,",",".")?></td>

</tr>

<?php } ?>

</tbody>

<tfoot>

<tr>

<th colspan="3">

Total

</th>

<th>

R$ <?=number_format($total,2,",",".")?>

</th>

</tr>

</tfoot>

</table>