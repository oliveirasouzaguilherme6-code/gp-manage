<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

if(!isset($_GET['id'])){
    die("ID inválido.");
}

$id = intval($_GET['id']);

$sql = $conn->prepare("
UPDATE ordens_servico
SET
status='Entregue',
saida=CURDATE(),
etapa='Entregue'
WHERE id_os=?
");

$sql->execute([$id]);


$sqlHistorico = $conn->prepare("
INSERT INTO historico_os
(
id_os,
acao,
descricao,
usuario
)
VALUES
(
?,?,?,?
)
");

$sqlHistorico->execute([

$id,

'Veículo Entregue',

'Veículo entregue ao cliente.',

'Administrador'

]);


header("Location: ../index.php?page=ver_os&id=".$id);

$sqlHistorico = $conn->prepare("
INSERT INTO historico_os
(
    id_os,
    acao,
    descricao,
    usuario
)
VALUES
(
    ?,?,?,?
)
");

$sqlHistorico->execute([

    $_GET['id'],

    'Veículo Entregue',

    'O veículo foi entregue ao cliente.',

    'Administrador'

]);



exit;