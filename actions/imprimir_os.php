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
v.marca,
v.modelo,
v.placa
FROM ordens_servico os
INNER JOIN clientes c ON c.id_cliente=os.id_cliente
INNER JOIN veiculos v ON v.id_veiculo=os.id_veiculo
WHERE id_os=?
");

$sql->execute([$id]);

$os = $sql->fetch(PDO::FETCH_ASSOC);

$dompdf = new Dompdf();

$html = "

<h1>GENILSON PINTURAS</h1>

<hr>

<h2>ORDEM DE SERVIÇO</h2>

<b>Nº:</b> {$os['numero_os']}<br>

<b>Cliente:</b> {$os['nome']}<br>

<b>Telefone:</b> {$os['telefone']}<br>

<b>Veículo:</b> {$os['marca']} {$os['modelo']}<br>

<b>Placa:</b> {$os['placa']}<br>

<b>Status:</b> {$os['status']}<br>

<b>Prioridade:</b> {$os['prioridade']}<br>

";

$dompdf->loadHtml($html);

$dompdf->setPaper("A4");

$dompdf->render();

$dompdf->stream("OS.pdf",["Attachment"=>false]);

<a

href="actions/imprimir_os.php?id=<?=$id?>"

target="_blank"

class="btn btn-danger">

<i class="bi bi-file-earmark-pdf"></i>

Gerar PDF

</a>