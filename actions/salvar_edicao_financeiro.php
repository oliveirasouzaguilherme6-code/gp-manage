<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql=$conn->prepare("
UPDATE financeiro
SET

tipo=?,
descricao=?,
valor=?,
forma_pagamento=?,
status=?,
data_vencimento=?

WHERE id_financeiro=?

");

$sql->execute([

$_POST['tipo'],
$_POST['descricao'],
$_POST['valor'],
$_POST['forma_pagamento'],
$_POST['status'],
$_POST['data_vencimento'],
$_POST['id_financeiro']

]);

header("Location: ../index.php?page=financeiro");
exit;