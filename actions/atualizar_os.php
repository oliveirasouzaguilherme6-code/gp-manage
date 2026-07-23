<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    header("Location: ../index.php?page=os");
    exit;
}

$sql = $conn->prepare("

UPDATE ordens_servico
SET

id_cliente=?,
id_veiculo=?,
status=?,
previsao=?,
valor_mao_obra=?,
observacoes=?

WHERE id_os=?

");

$sql->execute([

$_POST['id_cliente'],
$_POST['id_veiculo'],
$_POST['status'],
$_POST['previsao'],
$_POST['valor_mao_obra'],
$_POST['observacoes'],
$_POST['id_os']

]);

/* Histórico */

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

$_POST['id_os'],

'Ordem Atualizada',

'Os dados da Ordem de Serviço foram alterados.',

'Administrador'

]);

header("Location: ../index.php?page=ver_os&id=".$_POST['id_os']);

exit;