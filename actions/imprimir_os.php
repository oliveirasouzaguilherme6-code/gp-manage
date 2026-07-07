<?php

require_once "../vendor/autoload.php";
require_once "../config/database.php";

use Dompdf\Dompdf;

$db = new Database();
$conn = $db->connect();

$id = $_GET['id'];

$sql = $conn->prepare("

SELECT

os.*,

c.nome,
c.telefone,
c.email,
c.cidade,

v.modelo,
v.placa,
v.marca,
v.ano

FROM ordens_servico os

INNER JOIN clientes c
ON c.id_cliente=os.id_cliente

INNER JOIN veiculos v
ON v.id_veiculo=os.id_veiculo

WHERE os.id_os=?

");

$sql->execute([$id]);

$os = $sql->fetch(PDO::FETCH_ASSOC);

$pecas = $conn->prepare("

SELECT

op.*,

p.peca

FROM os_pecas op

INNER JOIN pecas p

ON p.id_peca=op.id_peca

WHERE op.id_os=?

");

$pecas->execute([$id]);

$listaPecas = $pecas->fetchAll(PDO::FETCH_ASSOC);

$html='

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<style>

body{

font-family:Arial,Helvetica,sans-serif;

font-size:13px;

color:#333;

}

h1{

text-align:center;

margin-bottom:5px;

}

h3{

margin-top:25px;

margin-bottom:8px;

}

table{

width:100%;

border-collapse:collapse;

margin-top:10px;

}

table th{

background:#f3f3f3;

padding:8px;

border:1px solid #ccc;

}

table td{

padding:8px;

border:1px solid #ccc;

}

.info{

margin-top:20px;

}

.total{

margin-top:20px;

text-align:right;

font-size:18px;

font-weight:bold;

}

</style>

</head>

<body>

<h1>

ORDEM DE SERVIÇO

</h1>

<hr>

<div class="info">

<strong>Número:</strong>

'.$os["numero_os"].'

<br>

<strong>Data:</strong>

'.$os["data_entrada"].'

<br>

<strong>Status:</strong>

'.$os["status"].'

</div>

<h3>

Cliente

</h3>

<strong>'.$os["nome"].'</strong>

<br>

Telefone:

'.$os["telefone"].'

<br>

Email:

'.$os["email"].'

<br>

Cidade:

'.$os["cidade"].'

<h3>

Veículo

</h3>

Marca:

'.$os["marca"].'

<br>

Modelo:

'.$os["modelo"].'

<br>

Placa:

'.$os["placa"].'

<br>

Ano:

'.$os["ano"].'

<h3>

Peças Utilizadas

</h3>

<table>

<tr>

<th>Peça</th>

<th>Qtd</th>

<th>Valor Unitário</th>

<th>Total</th>

</tr>

';

foreach($listaPecas as $item){

$html .= '

<tr>

<td>'.$item["peca"].'</td>

<td align="center">'.$item["quantidade"].'</td>

<td align="right">

R$ '.number_format($item["valor_unitario"],2,",",".").'

</td>

<td align="right">

R$ '.number_format($item["valor_total"],2,",",".").'

</td>

</tr>

';

}

$html .= '

</table>

<div class="total">

Valor das Peças:

R$ '.number_format($os["valor_pecas"],2,",",".").'

<br><br>

Valor dos Serviços:

R$ '.number_format($os["valor_servicos"],2,",",".").'

<br><br>

TOTAL DA O.S.

R$ '.number_format($os["valor_total"],2,",",".").'

</div>

<br><br>

<hr>

<div style="text-align:center;font-size:12px;color:#666;">

Documento gerado automaticamente pelo GP Manager.

</div>

</body>

</html>

';

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper("A4","portrait");

$dompdf->render();

$dompdf->stream(

"OS-".$os["numero_os"].".pdf",

["Attachment"=>false]

);

exit;