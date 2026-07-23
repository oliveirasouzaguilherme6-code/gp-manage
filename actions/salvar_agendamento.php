<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("

INSERT INTO agenda
(

id_cliente,
id_veiculo,
titulo,
descricao,
data,
hora,
status

)

VALUES
(

?,?,?,?,?,?,?

)

");

$sql->execute([

$_POST['id_cliente'],

$_POST['id_veiculo'],

$_POST['titulo'],

$_POST['descricao'],

$_POST['data'],

$_POST['hora'],

$_POST['status']

]);

header("Location: ../index.php?page=agenda");

exit;