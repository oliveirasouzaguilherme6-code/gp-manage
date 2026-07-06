<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("

INSERT INTO clientes

(nome,telefone,email,cpf,cidade,observacoes)

VALUES

(?,?,?,?,?,?)

");

$sql->execute([

$_POST['nome'],
$_POST['telefone'],
$_POST['email'],
$_POST['cpf'],
$_POST['cidade'],
$_POST['observacoes']

]);

header("Location: ../index.php?page=clientes");
exit;