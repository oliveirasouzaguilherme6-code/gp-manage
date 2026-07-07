<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("
SELECT
id_veiculo,
marca,
modelo,
placa
FROM veiculos
WHERE id_cliente=?
ORDER BY marca,modelo
");

$sql->execute([$_GET['id_cliente']]);

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));