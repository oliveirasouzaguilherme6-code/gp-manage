<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$conn->beginTransaction();

$sql = $conn->prepare("
SELECT preco_venda,estoque
FROM pecas
WHERE id_peca=?
");
$sql->execute([$_POST['id_peca']]);
$conn->prepare("

UPDATE ordens_servico

SET valor_pecas=(

SELECT IFNULL(SUM(valor_total),0)

FROM os_pecas

WHERE id_os=?

)

WHERE id_os=?

")->execute([

$_POST["id_os"],
$_POST["id_os"]

]);

$conn->prepare("

UPDATE ordens_servico

SET valor_total=

valor_pecas+valor_servicos

WHERE id_os=?

")->execute([

$_POST["id_os"]

]);

$peca = $sql->fetch(PDO::FETCH_ASSOC);

if($peca['estoque'] < $_POST['quantidade']){

    die("Estoque insuficiente.");

}

$valor = $peca['preco_venda'];

$qtd = $_POST['quantidade'];

$subtotal = $valor * $qtd;

$conn->prepare("
INSERT INTO os_pecas
(id_os,id_peca,quantidade,valor,subtotal)
VALUES (?,?,?,?,?)
")->execute([

$_POST['id_os'],
$_POST['id_peca'],
$qtd,
$valor,
$subtotal

]);

$conn->prepare("
UPDATE pecas
SET estoque=estoque-?
WHERE id_peca=?
")->execute([

$qtd,
$_POST['id_peca']

]);

$conn->commit();

header("Location: ../index.php?page=os_detalhes&id=".$_POST['id_os']);

<?php

$pecas = $conn->query("
SELECT *
FROM pecas
ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

?>

<form action="actions/adicionar_peca_os.php" method="POST">

<input
type="hidden"
name="id_os"
value="<?=$id?>">

<div class="row">

<div class="col-md-6">

<select
name="id_peca"
class="form-select">

<?php foreach($pecas as $p): ?>

<option value="<?=$p['id_peca']?>">

<?=$p['nome']?> | Estoque: <?=$p['estoque']?>

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

<div class="col-md-3">

<button class="btn btn-warning w-100">

Adicionar

</button>

</div>

</div>

</form>

<?php

$sql = $conn->prepare("
SELECT
o.*,
p.nome
FROM os_pecas o
INNER JOIN pecas p
ON p.id_peca=o.id_peca
WHERE id_os=?
");

$sql->execute([$id]);

?>

<table class="table mt-4">

<thead>

<tr>

<th>Peça</th>

<th>Qtd</th>

<th>Valor</th>

<th>Total</th>

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

Total das Peças

</th>

<th>

R$ <?=number_format($total,2,",",".")?>

</th>

</tr>

</tfoot>

</table>