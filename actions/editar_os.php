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


if($_POST['status']=="Entregue"){

$sql=$conn->prepare("

SELECT

numero_os,

valor_mao_obra,

desconto

FROM ordens_servico

WHERE id_os=?

");

$sql->execute([$id]);

$os=$sql->fetch(PDO::FETCH_ASSOC);

$sql=$conn->prepare("

SELECT

SUM(subtotal)

FROM os_pecas

WHERE id_os=?

");

$sql->execute([$id]);

$totalPecas=$sql->fetchColumn();

if(!$totalPecas){

$totalPecas=0;

}

$total=$os['valor_mao_obra']+$totalPecas-$os['desconto'];

$sql=$conn->prepare("

SELECT COUNT(*)

FROM financeiro

WHERE origem='OS'

AND id_origem=?

");

$sql->execute([$id]);

if($sql->fetchColumn()==0){

// INSERT aqui

}

$conn->prepare("

INSERT INTO financeiro

(

tipo,

descricao,

valor,

vencimento,

forma_pagamento,

origem,

id_origem,

status

)

VALUES

(

'Receber',

?,

?,

CURDATE(),

'A Definir',

'OS',

?,

'Pendente'

)

")->execute([

"Ordem de Serviço ".$os['numero_os'],

$total,

$id

]);

}


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