<?php

require_once "../vendor/autoload.php";
require_once "../config/database.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================================
   BANCO
========================================= */

$db = new Database();
$conn = $db->connect();

/* =========================================
   VALIDAR ID
========================================= */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Ordem de Serviço inválida.");
}

/* =========================================
   BUSCAR ORDEM
========================================= */

$sql = $conn->prepare("
    SELECT
        os.*,

        c.nome,
        c.telefone,
        c.cpf,
        c.email,
        c.endereco,

        v.modelo,
        v.marca,
        v.placa,
        v.ano,
        v.cor

    FROM ordens_servico os

    INNER JOIN clientes c
        ON c.id_cliente = os.id_cliente

    INNER JOIN veiculos v
        ON v.id_veiculo = os.id_veiculo

    WHERE os.id_os = ?
");

$sql->execute([$id]);

$os = $sql->fetch(PDO::FETCH_ASSOC);

if (!$os) {
    die("Ordem de Serviço não encontrada.");
}

/* =========================================
   PEÇAS
========================================= */

$sqlPecas = $conn->prepare("
    SELECT
        op.*,
        p.peca

    FROM os_pecas op

    INNER JOIN pecas p
        ON p.id_peca = op.id_peca

    WHERE op.id_os = ?
");

$sqlPecas->execute([$id]);

$pecas = $sqlPecas->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   SERVIÇOS
========================================= */

$sqlServicos = $conn->prepare("
    SELECT *
    FROM os_servicos
    WHERE id_os = ?
    ORDER BY id ASC
");

$sqlServicos->execute([$id]);

$servicos = $sqlServicos->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   FUNÇÕES
========================================= */

function e($valor)
{
    return htmlspecialchars(
        (string)($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function dataBR($data)
{
    if (empty($data) || $data === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($data);

    if (!$timestamp) {
        return '-';
    }

    return date('d/m/Y', $timestamp);
}

function moeda($valor)
{
    return 'R$ ' . number_format(
        (float)($valor ?? 0),
        2,
        ',',
        '.'
    );
}

/* =========================================
   HTML DO PDF
========================================= */

$html = '

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<style>

@page {
    margin: 28px 35px;
}

* {
    box-sizing: border-box;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #252525;
    margin: 0;
}

.header {
    width: 100%;
    border-bottom: 3px solid #222;
    padding-bottom: 14px;
    margin-bottom: 20px;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
}

.header-table td {
    border: none;
    padding: 0;
}

.title {
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 1px;
}

.os-number {
    text-align: right;
    font-size: 17px;
    font-weight: bold;
}

.subtitle {
    color: #777;
    font-size: 10px;
    margin-top: 4px;
}

.section {
    margin-top: 18px;
}

.section-title {
    background: #252525;
    color: #ffffff;
    padding: 7px 10px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table td {
    border: 1px solid #d5d5d5;
    padding: 7px;
    vertical-align: top;
}

.label {
    font-size: 9px;
    color: #777;
    text-transform: uppercase;
    margin-bottom: 3px;
}

.value {
    font-size: 11px;
    font-weight: bold;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #eeeeee;
    border: 1px solid #cccccc;
    padding: 7px;
    font-size: 10px;
    text-align: left;
}

.data-table td {
    border: 1px solid #d5d5d5;
    padding: 7px;
    font-size: 10px;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.empty {
    text-align: center;
    color: #888;
    padding: 12px !important;
}

.observacoes {
    border: 1px solid #d5d5d5;
    padding: 10px;
    min-height: 45px;
    line-height: 1.5;
}

.total-box {
    width: 45%;
    margin-left: 55%;
    margin-top: 18px;
}

.total-table {
    width: 100%;
    border-collapse: collapse;
}

.total-table td {
    padding: 7px;
    border-bottom: 1px solid #dddddd;
}

.grand-total td {
    background: #252525;
    color: #ffffff;
    font-size: 14px;
    font-weight: bold;
    border: none;
}

.signatures {
    width: 100%;
    margin-top: 55px;
    border-collapse: collapse;
}

.signatures td {
    width: 50%;
    border: none;
    padding: 0 25px;
    text-align: center;
}

.signature-line {
    border-top: 1px solid #555;
    padding-top: 5px;
    font-size: 9px;
}

.footer {
    margin-top: 30px;
    padding-top: 8px;
    border-top: 1px solid #cccccc;
    text-align: center;
    font-size: 8px;
    color: #777;
}

</style>

</head>

<body>


<div class="header">

<table class="header-table">

<tr>

<td>

<div class="title">
GP MANAGER
</div>

<div class="subtitle">
Ordem de Serviço
</div>

</td>

<td class="os-number">

'.e($os['numero_os']).'

</td>

</tr>

</table>

</div>


<div class="section">

<div class="section-title">
Informações da Ordem
</div>

<table class="info-table">

<tr>

<td width="25%">

<div class="label">
Status
</div>

<div class="value">
'.e($os['status']).'
</div>

</td>

<td width="25%">

<div class="label">
Etapa
</div>

<div class="value">
'.e($os['etapa']).'
</div>

</td>

<td width="25%">

<div class="label">
Entrada
</div>

<div class="value">
'.dataBR($os['entrada']).'
</div>

</td>

<td width="25%">

<div class="label">
Previsão
</div>

<div class="value">
'.dataBR($os['previsao']).'
</div>

</td>

</tr>

</table>

</div>


<div class="section">

<div class="section-title">
Cliente
</div>

<table class="info-table">

<tr>

<td width="50%">

<div class="label">
Nome
</div>

<div class="value">
'.e($os['nome']).'
</div>

</td>

<td width="25%">

<div class="label">
CPF
</div>

<div class="value">
'.e($os['cpf']).'
</div>

</td>

<td width="25%">

<div class="label">
Telefone
</div>

<div class="value">
'.e($os['telefone']).'
</div>

</td>

</tr>

<tr>

<td colspan="2">

<div class="label">
E-mail
</div>

<div class="value">
'.e($os['email']).'
</div>

</td>

<td>

<div class="label">
Endereço
</div>

<div class="value">
'.e($os['endereco']).'
</div>

</td>

</tr>

</table>

</div>


<div class="section">

<div class="section-title">
Veículo
</div>

<table class="info-table">

<tr>

<td width="20%">

<div class="label">
Marca
</div>

<div class="value">
'.e($os['marca']).'
</div>

</td>

<td width="25%">

<div class="label">
Modelo
</div>

<div class="value">
'.e($os['modelo']).'
</div>

</td>

<td width="20%">

<div class="label">
Placa
</div>

<div class="value">
'.e($os['placa']).'
</div>

</td>

<td width="15%">

<div class="label">
Ano
</div>

<div class="value">
'.e($os['ano']).'
</div>

</td>

<td width="20%">

<div class="label">
Cor
</div>

<div class="value">
'.e($os['cor']).'
</div>

</td>

</tr>

</table>

</div>


<div class="section">

<div class="section-title">
Peças Utilizadas
</div>

<table class="data-table">

<thead>

<tr>

<th>
Peça
</th>

<th width="70" class="text-center">
Qtd.
</th>

<th width="110" class="text-right">
Valor Unit.
</th>

<th width="110" class="text-right">
Total
</th>

</tr>

</thead>

<tbody>

';

if (!empty($pecas)) {

    foreach ($pecas as $peca) {

        $html .= '

        <tr>

        <td>
        '.e($peca['peca']).'
        </td>

        <td class="text-center">
        '.e($peca['quantidade']).'
        </td>

        <td class="text-right">
        '.moeda($peca['valor_unitario'] ?? 0).'
        </td>

        <td class="text-right">
        '.moeda($peca['valor_total'] ?? 0).'
        </td>

        </tr>

        ';
    }

} else {

    $html .= '

    <tr>

    <td colspan="4" class="empty">
    Nenhuma peça cadastrada nesta ordem.
    </td>

    </tr>

    ';
}

$html .= '

</tbody>

</table>

</div>


<div class="section">

<div class="section-title">
Serviços Executados
</div>

<table class="data-table">

<thead>

<tr>

<th>
Descrição
</th>

<th width="130">
Funcionário
</th>

<th width="65" class="text-center">
Horas
</th>

<th width="100" class="text-right">
Valor
</th>

<th width="90">
Status
</th>

</tr>

</thead>

<tbody>

';

if (!empty($servicos)) {

    foreach ($servicos as $servico) {

        $html .= '

        <tr>

        <td>
        '.e($servico['descricao']).'
        </td>

        <td>
        '.e($servico['funcionario']).'
        </td>

        <td class="text-center">
        '.e($servico['horas']).'
        </td>

        <td class="text-right">
        '.moeda($servico['valor']).'
        </td>

        <td>
        '.e($servico['status']).'
        </td>

        </tr>

        ';
    }

} else {

    $html .= '

    <tr>

    <td colspan="5" class="empty">
    Nenhum serviço cadastrado nesta ordem.
    </td>

    </tr>

    ';
}

$html .= '

</tbody>

</table>

</div>


<div class="section">

<div class="section-title">
Observações
</div>

<div class="observacoes">
'.(!empty($os['observacoes'])
    ? nl2br(e($os['observacoes']))
    : 'Nenhuma observação cadastrada.'
).'
</div>

</div>


<div class="total-box">

<table class="total-table">

<tr>

<td>
Peças
</td>

<td class="text-right">
'.moeda($os['valor_pecas']).'
</td>

</tr>

<tr>

<td>
Mão de Obra
</td>

<td class="text-right">
'.moeda($os['valor_mao_obra']).'
</td>

</tr>

<tr class="grand-total">

<td>
TOTAL
</td>

<td class="text-right">
'.moeda($os['valor_total']).'
</td>

</tr>

</table>

</div>


<table class="signatures">

<tr>

<td>

<div class="signature-line">
Responsável pela Oficina
</div>

</td>

<td>

<div class="signature-line">
'.e($os['nome']).'
<br>
Cliente
</div>

</td>

</tr>

</table>


<div class="footer">

Documento gerado automaticamente pelo GP Manager
<br>

Ordem de Serviço '.e($os['numero_os']).'

</div>


</body>

</html>

';

/* =========================================
   CONFIGURAR DOMPDF
========================================= */

$options = new Options();

$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

/* =========================================
   GERAR PDF
========================================= */

$dompdf->loadHtml($html, 'UTF-8');

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

/* Limpa qualquer saída anterior */
while (ob_get_level()) {
    ob_end_clean();
}

/* =========================================
   EXIBIR PDF
========================================= */

$dompdf->stream(
    'OS-' . $os['numero_os'] . '.pdf',
    [
        'Attachment' => false
    ]
);

exit;