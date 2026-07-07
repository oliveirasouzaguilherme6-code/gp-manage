<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("
SELECT *
FROM ordens_servico
WHERE id_os=?
");

$sql->execute([$_GET['id']]);

echo json_encode($sql->fetch(PDO::FETCH_ASSOC));