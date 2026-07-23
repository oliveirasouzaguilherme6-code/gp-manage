<?php




require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../index.php?page=agenda");
    exit;
}

$sql = $conn->prepare("

UPDATE agenda
SET

id_cliente = ?,
id_veiculo = ?,
titulo = ?,
descricao = ?,
data = ?,
hora = ?,
status = ?

WHERE id_agenda = ?

");

$sql->execute([

$_POST['id_cliente'],
$_POST['id_veiculo'],
$_POST['titulo'],
$_POST['descricao'],
$_POST['data'],
$_POST['hora'],
$_POST['status'],
$_POST['id_agenda']

]);

header("Location: ../index.php?page=agenda");
exit;