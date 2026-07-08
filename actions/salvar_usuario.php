<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

$sql = $conn->prepare("
INSERT INTO usuarios
(nome,email,senha,nivel)
VALUES(?,?,?,?)
");

$sql->execute([

$_POST['nome'],
$_POST['email'],
$senha,
$_POST['nivel']

]);

header("Location: ../index.php?page=usuarios");
exit;