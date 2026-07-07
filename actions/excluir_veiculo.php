<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("DELETE FROM veiculos WHERE id_veiculo=?");
$sql->execute([$_GET['id']]);

header("Location: ../index.php?page=veiculos");
exit;