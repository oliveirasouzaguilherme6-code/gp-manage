<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$veiculos = $conn->query("
SELECT
v.*,
c.nome
FROM veiculos v
LEFT JOIN clientes c
ON c.id_cliente=v.id_cliente
ORDER BY id_veiculo DESC
")->fetchAll(PDO::FETCH_ASSOC);

$clientes = $conn->query("
SELECT id_cliente,nome
FROM clientes
ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="fw-bold">

Veículos

</h2>

<small class="text-muted">

Cadastro de veículos

</small>

</div>

<button

class="btn btn-warning"

onclick="novoVeiculo()">

<i class="bi bi-plus-circle"></i>

Novo Veículo

</button>

</div>

<div class="card-dashboard bg-white p-4">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Foto</th>

<th>Cliente</th>

<th>Marca</th>

<th>Modelo</th>

<th>Placa</th>

<th>Ano</th>

<th width="150">

Ações

</th>

</tr>

</thead>

<tbody>

<?php foreach($veiculos as $v): ?>

<tr>

<td>

<?php if($v['foto']!=""){ ?>

<img

src="uploads/veiculos/<?=$v['foto']?>"

style="width:60px;height:60px;border-radius:12px;object-fit:cover;">

<?php }else{ ?>

<img

src="https://placehold.co/60x60"

style="border-radius:12px;">

<?php } ?>

</td>

<td>

<?=htmlspecialchars($v['nome'])?>

</td>

<td>

<?=$v['marca']?>

</td>

<td>

<?=$v['modelo']?>

</td>

<td>

<?=$v['placa']?>

</td>

<td>

<?=$v['ano']?>

</td>

<td>

<button

class="btn btn-primary btn-sm"

onclick="editarVeiculo(<?=$v['id_veiculo']?>)">

<i class="bi bi-pencil"></i>

</button>

<a

href="actions/excluir_veiculo.php?id=<?=$v['id_veiculo']?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Excluir veículo?')">

<i class="bi bi-trash"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<!-- Modal -->

<div class="modal fade" id="modalVeiculo">

<div class="modal-dialog modal-xl">

<div class="modal-content">

<form

id="formVeiculo"

action="actions/salvar_veiculo.php"

method="POST"

enctype="multipart/form-data">

<input

type="hidden"

name="id_veiculo"

id="id_veiculo">

<div class="modal-header">

<h5 id="tituloVeiculo">

Novo Veículo

</h5>

<button

class="btn-close"

data-bs-dismiss="modal">

</button>

</div>

<div class="modal-body">

<div class="row">

<div class="col-md-6">

<label>Cliente</label>

<select

name="id_cliente"

id="id_cliente"

class="form-select"

required>

<option value="">Selecione</option>

<?php foreach($clientes as $c): ?>

<option value="<?=$c['id_cliente']?>">

<?=$c['nome']?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-3">

<label>Marca</label>

<input

type="text"

name="marca"

id="marca"

class="form-control"

required>

</div>

<div class="col-md-3">

<label>Modelo</label>

<input

type="text"

name="modelo"

id="modelo"

class="form-control"

required>

</div>

<div class="col-md-3 mt-3">

<label>Ano</label>

<input

type="number"

name="ano"

id="ano"

class="form-control">

</div>

<div class="col-md-3 mt-3">

<label>Placa</label>

<input

type="text"

name="placa"

id="placa"

class="form-control">

</div>

<div class="col-md-3 mt-3">

<label>Cor</label>

<input

type="text"

name="cor"

id="cor"

class="form-control">

</div>

<div class="col-md-3 mt-3">

<label>KM</label>

<input

type="number"

name="km"

id="km"

class="form-control">

</div>

<div class="col-md-4 mt-3">

<label>Chassi</label>

<input

type="text"

name="chassi"

id="chassi"

class="form-control">

</div>

<div class="col-md-4 mt-3">

<label>Renavam</label>

<input

type="text"

name="renavam"

id="renavam"

class="form-control">

</div>

<div class="col-md-4 mt-3">

<label>Combustível</label>

<select

name="combustivel"

id="combustivel"

class="form-select">

<option>Gasolina</option>

<option>Etanol</option>

<option>Flex</option>

<option>Diesel</option>

<option>Elétrico</option>

</select>

</div>

<div class="col-md-12 mt-3">

<label>Foto</label>

<input

type="file"

name="foto"

class="form-control">

</div>

<div class="col-md-12 mt-3">

<label>Observações</label>

<textarea

name="observacoes"

id="observacoes"

rows="4"

class="form-control"></textarea>

</div>

</div>

</div>

<div class="modal-footer">

<button

class="btn btn-secondary"

data-bs-dismiss="modal">

Cancelar

</button>

<button

class="btn btn-warning">

Salvar Veículo

</button>

</div>

</form>

</div>

</div>

</div>

<script>

function novoVeiculo(){

    document.getElementById("tituloVeiculo").innerHTML="Novo Veículo";

    document.getElementById("formVeiculo").reset();

    document.getElementById("id_veiculo").value="";

    document.getElementById("formVeiculo").action="actions/salvar_veiculo.php";

    new bootstrap.Modal(document.getElementById("modalVeiculo")).show();

}

function editarVeiculo(id){

    fetch("actions/buscar_veiculo.php?id="+id)

    .then(r=>r.json())

    .then(v=>{

        document.getElementById("tituloVeiculo").innerHTML="Editar Veículo";

        document.getElementById("id_veiculo").value=v.id_veiculo;

        document.getElementById("id_cliente").value=v.id_cliente;

        document.getElementById("marca").value=v.marca;

        document.getElementById("modelo").value=v.modelo;

        document.getElementById("ano").value=v.ano;

        document.getElementById("placa").value=v.placa;

        document.getElementById("cor").value=v.cor;

        document.getElementById("km").value=v.km;

        document.getElementById("chassi").value=v.chassi;

        document.getElementById("renavam").value=v.renavam;

        document.getElementById("combustivel").value=v.combustivel;

        document.getElementById("observacoes").value=v.observacoes;

        document.getElementById("formVeiculo").action="actions/editar_veiculo.php";

        new bootstrap.Modal(document.getElementById("modalVeiculo")).show();

    });

}

</script>