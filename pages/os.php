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
INNER JOIN clientes c ON c.id_cliente = os.id_cliente
INNER JOIN veiculos v ON v.id_veiculo = os.id_veiculo
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

<th>Mão de Obra</th>

<th>Total</th>

<th width="130">

Ações

</th>

</tr>

</thead>

<tbody>

<?php foreach($ordens as $os): ?>

<?php

$cor="secondary";

switch($os['status']){

case "Recebido":
$cor="primary";
break;

case "Aguardando Aprovação":
$cor="warning";
break;

case "Aguardando Peças":
$cor="danger";
break;

case "Em Serviço":
$cor="info";
break;

case "Finalizado":
$cor="success";
break;

case "Entregue":
$cor="dark";
break;

case "Cancelado":
$cor="secondary";
break;

}

$total =
($os['valor_pecas'] ?? 0)
+
($os['valor_mao_obra'] ?? 0);

?>

<tr>

<td>

<?= $os['numero_os']; ?>

</td>

<td>

<?= htmlspecialchars($os['nome']); ?>

</td>

<td>

<strong>

<?= htmlspecialchars($os['modelo']); ?>

</strong>

<br>

<small class="text-muted">

<?= htmlspecialchars($os['placa']); ?>

</small>

</td>

<td>

<span class="badge bg-<?= $cor ?>">

<?= htmlspecialchars($os['status']); ?>

</span>

</td>

<td>

R$

<?= number_format($os['valor_pecas'] ?? 0,2,",","."); ?>

</td>

<td>

R$

<?= number_format($os['valor_mao_obra'] ?? 0,2,",","."); ?>

</td>

<td>

<strong>

R$

<?= number_format($total,2,",","."); ?>

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

<h5>Nova Ordem de Serviço</h5>

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

<option value="">Selecione...</option>

<?php foreach($clientes as $cliente): ?>

<option value="<?= $cliente['id_cliente']; ?>">

<?= htmlspecialchars($cliente['nome']); ?>

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

<option value="">Selecione...</option>

<?php foreach($veiculos as $veiculo): ?>

<option value="<?= $veiculo['id_veiculo']; ?>">

<?= htmlspecialchars($veiculo['modelo']); ?>

-

<?= htmlspecialchars($veiculo['placa']); ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-3 mt-3">

<label>Entrada</label>

<input
type="date"
name="entrada"
class="form-control"
value="<?= date('Y-m-d'); ?>"
required>

</div>

<div class="col-md-3 mt-3">

<label>Previsão</label>

<input
type="date"
name="previsao"
class="form-control">

</div>

<div class="col-md-3 mt-3">

<label>Status</label>

<select
name="status"
class="form-select">

<option value="Recebido">Recebido</option>

<option value="Aguardando Aprovação">Aguardando Aprovação</option>

<option value="Aguardando Peças">Aguardando Peças</option>

<option value="Em Serviço">Em Serviço</option>

<option value="Finalizado">Finalizado</option>

<option value="Entregue">Entregue</option>

<option value="Cancelado">Cancelado</option>

</select>

</div>

<div class="col-md-3 mt-3">

<label>Mão de Obra</label>

<input
type="number"
step="0.01"
min="0"
name="valor_mao_obra"
id="valor_mao_obra"
class="form-control"
value="0.00">

</div>

<div class="col-12 mt-3">

<label>Observações</label>

<textarea
name="observacoes"
rows="3"
class="form-control"></textarea>

</div>

<hr class="mt-4">

<h5>Peças da Ordem de Serviço</h5>
<table
class="table table-bordered align-middle"
id="tabelaPecas">

<thead class="table-light">

<tr>

<th>Peça</th>

<th width="100">Qtd</th>

<th width="140">Valor Unit.</th>

<th width="150">Subtotal</th>

</tr>

</thead>

<tbody>

<tr>

<td>

<select
name="peca[]"
class="form-select peca">

<option value="">Selecione...</option>

<?php foreach($pecas as $peca): ?>

<option
value="<?= $peca['id_peca']; ?>"
data-valor="<?= $peca['venda']; ?>">

<?= htmlspecialchars($peca['peca']); ?>

(Estoque: <?= $peca['estoque']; ?>)

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

<div class="row mt-4">

<hr class="mt-5">

<h5>

Serviços Executados

</h5>

<table
class="table table-bordered align-middle"
id="tabelaServicos">

<thead class="table-light">

<tr>

<th>Descrição</th>

<th>Funcionário</th>

<th width="90">Horas</th>

<th width="130">Valor</th>

<th width="170">Status</th>

</tr>

</thead>

<tbody>

<tr>

<td>

<input
type="text"
name="descricao_servico[]"
class="form-control">

</td>

<td>

<input
type="text"
name="funcionario[]"
class="form-control">

</td>

<td>

<input
type="number"
step="0.5"
min="0"
name="horas[]"
class="form-control">

</td>

<td>

<input
type="number"
step="0.01"
min="0"
name="valor_servico[]"
class="form-control">

</td>

<td>

<select
name="status_servico[]"
class="form-select">

<option>Pendente</option>

<option>Executando</option>

<option>Concluído</option>

</select>

</td>

</tr>

</tbody>

</table>

<button
type="button"
class="btn btn-primary btn-sm"
id="adicionarServico">

<i class="bi bi-plus-circle"></i>

Adicionar Serviço

</button>

<div class="col-md-4 ms-auto">

<table class="table">

<tr>

<th>Total Peças</th>

<td class="text-end">

<span id="textoTotalPecas">

R$ 0,00

</span>

</td>

</tr>

<tr>

<th>Mão de Obra</th>

<td class="text-end">

<span id="textoMaoObra">

R$ 0,00

</span>

</td>

</tr>

<tr class="table-warning">

<th>Total O.S.</th>

<td class="text-end">

<strong id="valorTotalOS">

R$ 0,00

</strong>

</td>

</tr>

</table>

</div>

</div>

<input
type="hidden"
name="valor_pecas"
id="valor_pecas">

<input
type="hidden"
name="valor_total"
id="valor_total">

</div>

<input
type="hidden"
name="valor_mao_obra_hidden"
id="valor_mao_obra_hidden">

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

<i class="bi bi-check-circle"></i>

Salvar Ordem de Serviço

</button>

</div>


<input
type="hidden"
name="valor_pecas"
id="valor_pecas">

<input
type="hidden"
name="valor_total"
id="valor_total">

</form>

</div>

</div>

</div>

<script>

function ativarEventos(){

    document.querySelectorAll(".peca").forEach(function(select){

        select.onchange=function(){

            atualizarLinha(this.closest("tr"));

        };

    });

    document.querySelectorAll(".quantidade").forEach(function(input){

        input.onkeyup=function(){

            atualizarLinha(this.closest("tr"));

        };

        input.onchange=input.onkeyup;

    });

}

function atualizarLinha(linha){

    let select=linha.querySelector(".peca");

    let valor=parseFloat(
        select.options[select.selectedIndex]?.dataset.valor || 0
    );

    let qtd=parseFloat(
        linha.querySelector(".quantidade").value || 0
    );

    linha.querySelector(".valor").value=
        valor.toFixed(2);

    linha.querySelector(".total").value=
        (valor*qtd).toFixed(2);

    calcularTotal();

}

function calcularTotal(){

    let totalPecas=0;

    document.querySelectorAll(".total").forEach(function(item){

        totalPecas+=parseFloat(item.value || 0);

    });

    let maoObra=parseFloat(
        document.getElementById("valor_mao_obra").value || 0
    );

    let total=totalPecas+maoObra;

    document.getElementById("textoTotalPecas").innerHTML=
        "R$ "+totalPecas.toLocaleString("pt-BR",{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });

    document.getElementById("textoMaoObra").innerHTML=
        "R$ "+maoObra.toLocaleString("pt-BR",{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });

    document.getElementById("valorTotalOS").innerHTML=
        "R$ "+total.toLocaleString("pt-BR",{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });

    document.getElementById("valor_pecas").value=totalPecas;

    document.getElementById("valor_total").value=total;

}

document.getElementById("valor_mao_obra").addEventListener(
"keyup",
calcularTotal
);

document.getElementById("valor_mao_obra").addEventListener(
"change",
calcularTotal
);

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


document.getElementById("adicionarServico").addEventListener("click",function(){

    let tbody=document.querySelector("#tabelaServicos tbody");

    let linha=tbody.rows[0].cloneNode(true);

    linha.querySelectorAll("input").forEach(function(input){

        input.value="";

    });

    linha.querySelector("select").selectedIndex=0;

    tbody.appendChild(linha);

});

ativarEventos();

calcularTotal();

</script>