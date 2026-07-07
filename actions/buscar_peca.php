<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("
SELECT *
FROM pecas
WHERE id_peca=?
");

$sql->execute([$_GET['id']]);

echo json_encode($sql->fetch(PDO::FETCH_ASSOC));