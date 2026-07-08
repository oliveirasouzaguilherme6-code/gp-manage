<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("
INSERT INTO financeiro
(
tipo,
categoria,
descricao,
valor,
forma_pagamento,
status,
data_vencimento,
observacoes
)
VALUES
(
?,?,?,?,?,?,?,?
)
");

$sql->execute([

$_POST['tipo'],
"Manual",
$_POST['descricao'],
$_POST['valor'],
$_POST['forma_pagamento'],
"Pendente",
$_POST['data_vencimento'],
$_POST['observacoes']

]);

header("Location: ../index.php?page=financeiro");
exit;