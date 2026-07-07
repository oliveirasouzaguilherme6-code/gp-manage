<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("SELECT * FROM clientes WHERE id_cliente=?");

$sql->execute([$_GET['id']]);

echo json_encode($sql->fetch(PDO::FETCH_ASSOC));