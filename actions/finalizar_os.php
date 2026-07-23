<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$id = intval($_GET['id']);

$sql = $conn->prepare("
UPDATE ordens_servico
SET
status='Finalizado'
WHERE id_os=?
");

$sql->execute([$id]);

$sql = $conn->prepare("
INSERT INTO historico_os
(
id_os,
descricao,
usuario
)
VALUES
(
?,?,?
)
");

$sql->execute([

$id,

'Ordem de Serviço finalizada.',

'Administrador'

]);

header("Location: ../index.php?page=ver_os&id=".$id);

exit;