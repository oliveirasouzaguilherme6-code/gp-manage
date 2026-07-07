<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->query("
SELECT
os.*,
c.nome,
v.modelo,
v.placa
FROM ordens_servico os
INNER JOIN clientes c ON c.id_cliente=os.id_cliente
INNER JOIN veiculos v ON v.id_veiculo=os.id_veiculo
ORDER BY os.id_os DESC
");

$ordens = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="fw-bold">

Ordens de Serviço

</h2>

<small class="text-muted">

Controle das O.S.

</small>

</div>

<button
class="btn btn-warning"
data-bs-toggle="modal"
data-bs-target="#novaOS">

<i class="bi bi-plus-circle"></i>

Nova O.S.

</button>

</div>

<div class="card-dashboard bg-white p-4">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>

<th>Nº</th>

<th>Cliente</th>

<th>Veículo</th>

<th>Status</th>

<th>Peças</th>

<th>Serviços</th>

<th>Total</th>

<th width="130">

Ações

</th>

</tr>

</thead>

<tbody>

<?php foreach($ordens as $os): ?>

<tr>

<td>

<?= $os['numero_os']; ?>

</td>

<td>

<?= htmlspecialchars($os['nome']); ?>

</td>

<td>

<?= htmlspecialchars($os['modelo']); ?>

<br>

<small>

<?= htmlspecialchars($os['placa']); ?>

</small>

</td>

<td>

<span class="badge bg-primary">

<?= $os['status']; ?>

</span>

</td>

<td>

R$

<?= number_format($os['valor_pecas'],2,",","."); ?>

</td>

<td>

R$

<?= number_format($os['valor_servicos'],2,",","."); ?>

</td>

<td>

<strong>

R$

<?= number_format($os['valor_total'],2,",","."); ?>

</strong>

</td>

<td>

<a
href="index.php?page=ver_os&id=<?= $os['id_os']; ?>"
class="btn btn-primary btn-sm">

<i class="bi bi-eye"></i>

</a>
<a
href="actions/imprimir_os.php?id=<?= $os['id_os']; ?>"
target="_blank"
class="btn btn-success btn-sm">

<i class="bi bi-printer"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php

$clientes = $conn->query("
SELECT id_cliente,nome
FROM clientes
ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

$veiculos = $conn->query("
SELECT id_veiculo,modelo,placa
FROM veiculos
ORDER BY modelo
")->fetchAll(PDO::FETCH_ASSOC);

$pecas = $conn->query("
SELECT
id_peca,
peca,
venda,
estoque
FROM pecas
ORDER BY peca
")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="modal fade" id="novaOS">

<div class="modal-dialog modal-xl">

<div class="modal-content">

<form
id="formOS"
action="actions/salvar_os.php"
method="POST">

<div class="modal-header">

<h5>

Nova Ordem de Serviço

</h5>

<button
type="button"
class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="row">

<div class="col-md-6">

<label>Cliente</label>

<select
name="id_cliente"
class="form-select"
required>

<option value="">

Selecione...

</option>

<?php foreach($clientes as $cliente): ?>

<option value="<?= $cliente['id_cliente']; ?>">

<?= $cliente['nome']; ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-6">

<label>Veículo</label>

<select
name="id_veiculo"
class="form-select"
required>

<option value="">

Selecione...

</option>

<?php foreach($veiculos as $veiculo): ?>

<option value="<?= $veiculo['id_veiculo']; ?>">

<?= $veiculo['modelo']; ?>

-

<?= $veiculo['placa']; ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-4 mt-3">

<label>Data Entrada</label>

<input
type="datetime-local"
name="data_entrada"
class="form-control"
required>

</div>

<div class="col-md-4 mt-3">

<label>Previsão</label>

<input
type="date"
name="previsao_entrega"
class="form-control">

</div>

<div class="col-md-4 mt-3">

<label>Status</label>

<select
name="status"
class="form-select">

<option>Aberta</option>

<option>Em andamento</option>

<option>Aguardando Peças</option>

<option>Finalizada</option>

<option>Entregue</option>

</select>

</div>

<div class="col-12 mt-3">

<label>Observações</label>

<textarea
name="observacoes"
rows="3"
class="form-control"></textarea>

</div>

<hr class="mt-4 mb-4">

<h5>

Peças da Ordem de Serviço

</h5>

<table
class="table table-bordered"
id="tabelaPecas">

<thead>

<tr>

<th>Peça</th>

<th width="120">

Qtd

</th>

<th width="140">

Valor

</th>

<th width="160">

Total

</th>

</tr>

</thead>

<tbody>

<tr>

<td>

<select
name="peca[]"
class="form-select">

<option value="">

Selecione...

</option>

<?php foreach($pecas as $peca): ?>

<option
value="<?= $peca['id_peca']; ?>"
data-valor="<?= $peca['venda']; ?>">

<?= $peca['peca']; ?>

(Estoque:

<?= $peca['estoque']; ?>)

</option>

<?php endforeach; ?>

</select>

</td>

<td>

<input
type="number"
name="quantidade[]"
class="form-control quantidade"
value="1"
min="1">

</td>

<td>

<input
type="text"
class="form-control valor"
readonly>

</td>

<td>

<input
type="text"
class="form-control total"
readonly>

</td>

</tr>

</tbody>

</table>

<button
type="button"
class="btn btn-success btn-sm"
id="adicionarPeca">

<i class="bi bi-plus-circle"></i>

Adicionar Peça

</button>

<div class="text-end mt-4">

<h3>

Total da O.S.

<span
id="valorTotalOS"
class="text-success">

R$ 0,00

</span>

</h3>

</div>

<input
type="hidden"
name="valor_total"
id="valor_total">

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancelar

</button>

<button
type="submit"
class="btn btn-warning">

Salvar Ordem de Serviço

</button>

</div>

</form>

</div>

</div>

</div>


<script>

function ativarEventos(){

document.querySelectorAll("select[name='peca[]']").forEach(function(select){

select.onchange=function(){

let linha=this.closest("tr");

let valor=parseFloat(this.options[this.selectedIndex].dataset.valor || 0);

linha.querySelector(".valor").value=valor.toFixed(2);

let qtd=parseFloat(linha.querySelector(".quantidade").value);

linha.querySelector(".total").value=(valor*qtd).toFixed(2);

calcularTotal();

};

});

document.querySelectorAll(".quantidade").forEach(function(input){

input.onkeyup=function(){

let linha=this.closest("tr");

let valor=parseFloat(linha.querySelector(".valor").value || 0);

linha.querySelector(".total").value=(valor*this.value).toFixed(2);

calcularTotal();

};

input.onchange=input.onkeyup;

});

}

function calcularTotal(){

let total=0;

document.querySelectorAll(".total").forEach(function(item){

total+=parseFloat(item.value || 0);

});

document.getElementById("valor_total").value=total;

document.getElementById("valorTotalOS").innerHTML=

"R$ "+total.toLocaleString("pt-BR",{

minimumFractionDigits:2,

maximumFractionDigits:2

});

}

document.getElementById("adicionarPeca").addEventListener("click",function(){

let tbody=document.querySelector("#tabelaPecas tbody");

let linha=tbody.rows[0].cloneNode(true);

linha.querySelector("select").selectedIndex=0;

linha.querySelector(".quantidade").value=1;

linha.querySelector(".valor").value="";

linha.querySelector(".total").value="";

tbody.appendChild(linha);

ativarEventos();

});

ativarEventos();

</script>