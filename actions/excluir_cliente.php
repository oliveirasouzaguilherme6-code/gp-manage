<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("DELETE FROM clientes WHERE id_cliente=?");

$sql->execute([$_GET['id']]);

header("Location: ../index.php?page=clientes");
exit;