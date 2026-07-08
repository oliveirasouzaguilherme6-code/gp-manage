<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$id = intval($_GET['id']);

$sql = $conn->prepare("
SELECT *
FROM financeiro
WHERE id_financeiro=?
");

$sql->execute([$id]);

$f = $sql->fetch(PDO::FETCH_ASSOC);

if(!$f){
    die("Lançamento não encontrado.");
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Editar Financeiro</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header">

<h4>Editar Lançamento</h4>

</div>

<div class="card-body">

<form action="salvar_edicao_financeiro.php" method="POST">

<input
type="hidden"
name="id_financeiro"
value="<?=$f['id_financeiro']?>">

<label>Tipo</label>

<select
name="tipo"
class="form-select">

<option value="Receita" <?=$f['tipo']=="Receita"?"selected":""?>>

Receber

</option>

<option value="Despesa" <?=$f['tipo']=="Despesa"?"selected":""?>>

Pagar

</option>

</select>

<label class="mt-3">

Descrição

</label>

<input
type="text"
name="descricao"
class="form-control"
value="<?=$f['descricao']?>">

<label class="mt-3">

Valor

</label>

<input
type="number"
step="0.01"
name="valor"
class="form-control"
value="<?=$f['valor']?>">

<label class="mt-3">

Data Vencimento

</label>

<input
type="date"
name="data_vencimento"
class="form-control"
value="<?=$f['data_vencimento']?>">

<label class="mt-3">

Forma Pagamento

</label>

<select
name="forma_pagamento"
class="form-select">

<?php

$formas=[
"Pix",
"Dinheiro",
"Cartão",
"Boleto",
"Transferência"
];

foreach($formas as $forma){

$selected=$forma==$f['forma_pagamento']?"selected":"";

echo "<option $selected>$forma</option>";

}

?>

</select>

<label class="mt-3">

Status

</label>

<select
name="status"
class="form-select">

<option <?=$f['status']=="Pendente"?"selected":""?>>

Pendente

</option>

<option <?=$f['status']=="Pago"?"selected":""?>>

Pago

</option>

<option <?=$f['status']=="Cancelado"?"selected":""?>>

Cancelado

</option>

</select>

<br>

<button class="btn btn-success">

Salvar Alterações

</button>

<a
href="../index.php?page=financeiro"
class="btn btn-secondary">

Cancelar

</a>

</form>

</div>

</div>

</div>

</body>

</html>