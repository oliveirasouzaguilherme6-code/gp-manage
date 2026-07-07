<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$ultimo = $conn->query("
SELECT id_os
FROM ordens_servico
ORDER BY id_os DESC
LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$numero = 1;

if($ultimo){

    $numero = $ultimo['id_os'] + 1;

}

$numeroOS = "OS".str_pad($numero,6,"0",STR_PAD_LEFT);

$sql = $conn->prepare("

INSERT INTO ordens_servico(

numero_os,
id_cliente,
id_veiculo,
data_entrada,
previsao_entrega,
status,
observacoes

)

VALUES(

?,?,?,?,?,?,?

)

");

$sql->execute([

$numeroOS,
$_POST['id_cliente'],
$_POST['id_veiculo'],
$_POST['data_entrada'],
$_POST['previsao_entrega'],
$_POST['status'],
$_POST['observacoes']

]);

header("Location: ../index.php?page=ordens_servico");

exit;