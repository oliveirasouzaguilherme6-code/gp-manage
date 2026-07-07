<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$conn->prepare("
UPDATE ordens_servico
SET

id_cliente=?,
id_veiculo=?,
status=?,
prioridade=?,
previsao_entrega=?,
valor_mao_obra=?,
desconto=?,
observacoes=?

WHERE id_os=?

")->execute([

$_POST['id_cliente'],
$_POST['id_veiculo'],
$_POST['status'],
$_POST['prioridade'],
$_POST['previsao_entrega'],
$_POST['valor_mao_obra'],
$_POST['desconto'],
$_POST['observacoes'],
$_POST['id_os']

]);

$conn->prepare("
INSERT INTO historico_os
(id_os,descricao,usuario)
VALUES (?,?,?)
")->execute([

$_POST['id_os'],

"Ordem de Serviço editada.",

"Administrador"

]);

header("Location: ../index.php?page=os");
exit;