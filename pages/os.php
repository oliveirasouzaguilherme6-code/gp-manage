<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->query("
SELECT
os.*,
c.nome,
v.marca,
v.modelo,
v.placa
FROM ordens_servico os
INNER JOIN clientes c
ON os.id_cliente=c.id_cliente
INNER JOIN veiculos v
ON os.id_veiculo=v.id_veiculo
ORDER BY os.id_os DESC
");

$ordens=$sql->fetchAll(PDO::FETCH_ASSOC);

?>

<h2 class="fw-bold mb-4">

Ordens de Serviço

</h2>

<div class="card-dashboard bg-white p-4">

<div class="d-flex justify-content-between mb-4">

<h5>

Lista de O.S.

</h5>

<button
class="btn btn-warning"
data-bs-toggle="modal"
data-bs-target="#novaOS">

Nova O.S.

</button>

</div>

<table class="table table-hover">

<thead>

<tr>

<th>OS</th>

<th>Cliente</th>

<th>Veículo</th>

<th>Status</th>

<th>Total</th>

<th></th>

</tr>

</thead>

<tbody>

<?php foreach($ordens as $os): ?>

<tr>

<td>

<?= $os['numero_os']; ?>

</td>

<td>

<?= $os['nome']; ?>

</td>

<td>

<?= $os['marca']; ?>

<?= $os['modelo']; ?>

</td>

<td>

<span class="badge bg-primary">

<?= $os['status']; ?>

</span>

</td>

<td>

R$ <?= number_format($os['valor_total'],2,",","."); ?>

</td>

<td>

<button class="btn btn-primary btn-sm">

Abrir

</button>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<div
class="modal fade"
id="novaOS">

<div class="modal-dialog modal-xl">

<div class="modal-content">

<form
action="actions/salvar_os.php"
method="POST">

<div class="modal-header">

<h5>

Nova Ordem de Serviço

</h5>

</div>

<div class="modal-body">

<div class="row">

<div class="col-md-3">

<label>

Número O.S.

</label>

<input

type="text"

name="numero_os"

class="form-control"

value="<?= date('YmdHis')?>"

readonly>

</div>

<div class="col-md-5">

<label>

Cliente

</label>

<select
name="id_cliente"
class="form-select">

<?php

$clientes=$conn->query("SELECT * FROM clientes");

foreach($clientes as $c){

?>

<option value="<?=$c['id_cliente']?>">

<?=$c['nome']?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-4">

<label>

Veículo

</label>

<select
name="id_veiculo"
class="form-select">

<?php

$veiculos=$conn->query("SELECT * FROM veiculos");

foreach($veiculos as $v){

?>

<option value="<?=$v['id_veiculo']?>">

<?=$v['marca']?> <?=$v['modelo']?> -
<?=$v['placa']?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-3 mt-3">

<label>

Prioridade

</label>

<select
name="prioridade"
class="form-select">

<option>Baixa</option>

<option>Média</option>

<option>Alta</option>

<option>Urgente</option>

</select>

</div>

<div class="col-md-3 mt-3">

<label>

Tipo

</label>

<select
name="tipo"
class="form-select">

<option>Seguro</option>

<option>Particular</option>

</select>

</div>

<div class="col-md-3 mt-3">

<label>

Entrada

</label>

<input
type="date"
name="entrada"
class="form-control">

</div>

<div class="col-md-3 mt-3">

<label>

Entrega Prevista

</label>

<input
type="date"
name="previsao"

class="form-control">

</div>

<div class="col-12 mt-3">

<label>

Observações

</label>

<textarea
name="observacoes"
rows="4"
class="form-control">

</textarea>

</div>

<script>

document.getElementById("id_cliente").addEventListener("change",function(){

let id=this.value;

fetch("actions/buscar_veiculos_cliente.php?id_cliente="+id)

.then(r=>r.json())

.then(lista=>{

let select=document.getElementById("id_veiculo");

select.innerHTML="<option value=''>Selecione</option>";

lista.forEach(v=>{

select.innerHTML+=`

<option value="${v.id_veiculo}">

${v.marca} ${v.modelo} - ${v.placa}

</option>

`;

});

});

});

</script>

<select
id="id_cliente"
name="id_cliente"
class="form-select">
</select>

<select
id="id_veiculo"
name="id_veiculo"
class="form-select">
<option>Selecione um cliente</option>
</select>

<select
name="status"
class="form-select">

<option>Novo</option>
<option>Aguardando Aprovação</option>
<option>Aguardando Peças</option>
<option>Funilaria</option>
<option>Preparação</option>
<option>Pintura</option>
<option>Montagem</option>
<option>Polimento</option>
<option>Lavagem</option>
<option>Pronto</option>
<option>Entregue</option>

</select>

<select
name="prioridade"
class="form-select">

<option>Baixa</option>
<option selected>Média</option>
<option>Alta</option>
<option>Urgente</option>

</select>

<input
type="date"
name="previsao_entrega"
class="form-control">

<input
type="number"
step="0.01"
name="valor_mao_obra"
class="form-control">

<input
type="number"
step="0.01"
name="desconto"
class="form-control">

<textarea
name="observacoes"
class="form-control"
rows="4"></textarea>

<button

class="btn btn-primary btn-sm"

onclick="editarOS(<?=$os['id_os']?>)">

<i class="bi bi-pencil"></i>

</button>

<a

href="actions/excluir_os.php?id=<?=$os['id_os']?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Excluir Ordem de Serviço?')">

<i class="bi bi-trash"></i>

</a>

<a

href="index.php?page=os_detalhes&id=<?=$os['id_os']?>"

class="btn btn-success btn-sm">

<i class="bi bi-eye"></i>

</a>