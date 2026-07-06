<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$pesquisa = $_GET['q'] ?? '';

$sql = $conn->prepare("
SELECT
c.nome,
v.modelo,
v.placa
FROM clientes c
LEFT JOIN veiculos v
ON c.id_cliente=v.id_cliente
WHERE

c.nome LIKE ?
OR

c.telefone LIKE ?
OR

v.placa LIKE ?
OR

v.modelo LIKE ?

LIMIT 10
");

$like="%".$pesquisa."%";

$sql->execute([
$like,
$like,
$like,
$like
]);

while($r=$sql->fetch(PDO::FETCH_ASSOC)){

echo"

<div class='resultado-item p-3 border-bottom'>

<strong>{$r['nome']}</strong><br>

{$r['modelo']} -
{$r['placa']}

</div>

";

}