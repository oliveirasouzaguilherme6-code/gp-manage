<?php

require_once "../config/database.php";

$db=new Database();

$conn=$db->connect();

$sql=$conn->prepare("

INSERT INTO veiculos

(id_cliente,marca,modelo,ano,placa,cor,km)

VALUES

(?,?,?,?,?,?,?)

");

$sql->execute([

$_POST['id_cliente'],

$_POST['marca'],

$_POST['modelo'],

$_POST['ano'],

$_POST['placa'],

$_POST['cor'],

$_POST['km']

]);

header("Location: ../index.php?page=veiculos");
exit;