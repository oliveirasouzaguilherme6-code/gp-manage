<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$id = $_GET['id'];

$sql = $conn->prepare("
SELECT
os.*,
c.nome,
v.marca,
v.modelo,
v.placa

FROM ordens_servico os

INNER JOIN clientes c
ON c.id_cliente=os.id_cliente

INNER JOIN veiculos v
ON v.id_veiculo=os.id_veiculo

WHERE os.id_os=?
");

$sql->execute([$id]);

$os=$sql->fetch(PDO::FETCH_ASSOC);

?>

<h2 class="fw-bold mb-4">

Ordem de Serviço

<?=$os['numero_os']?>

</h2>

<div class="row">

<div class="col-lg-4">

<div class="card-dashboard bg-white p-4">

<h5>Cliente</h5>

<hr>

<b><?=$os['nome']?></b>

<br>

<?=$os['marca']?> <?=$os['modelo']?>

<br>

<?=$os['placa']?>

<br><br>

Status

<span class="badge bg-success">

<?=$os['status']?>

</span>

<br>

Prioridade

<span class="badge bg-danger">

<?=$os['prioridade']?>

</span>

</div>

</div>

<div class="col-lg-8">

<div class="card-dashboard bg-white p-4">

<ul class="nav nav-tabs">

<li class="nav-item">

<button

class="nav-link active"

data-bs-toggle="tab"

data-bs-target="#servicos">

Serviços

</button>

</li>

<li class="nav-item">

<button

class="nav-link"

data-bs-toggle="tab"

data-bs-target="#pecas">

Peças

</button>

</li>

<li class="nav-item">

<button

class="nav-link"

data-bs-toggle="tab"

data-bs-target="#fotos">

Fotos

</button>

</li>

<li class="nav-item">

<button

class="nav-link"

data-bs-toggle="tab"

data-bs-target="#checklist">

Checklist

</button>

</li>

<li class="nav-item">

<button

class="nav-link"

data-bs-toggle="tab"

data-bs-target="#historico">

Histórico

</button>

</li>

</ul>

<div class="tab-content mt-4">

<div

class="tab-pane fade show active"

id="servicos">

<h5>

Serviços

</h5>

<button class="btn btn-warning">

Adicionar Serviço

</button>

</div>

<div

class="tab-pane fade"

id="pecas">

<h5>

Peças

</h5>

<button class="btn btn-warning">

Adicionar Peça

</button>

</div>

<div

class="tab-pane fade"

id="fotos">

<h5>

Galeria

</h5>

<input

type="file"

class="form-control"

multiple>

</div>

<div

class="tab-pane fade"

id="checklist">

<h5>

Checklist

</h5>

<div class="form-check">

<input

class="form-check-input"

type="checkbox">

<label>

Documento

</label>

</div>

<div class="form-check">

<input

class="form-check-input"

type="checkbox">

<label>

Chave Reserva

</label>

</div>

<div class="form-check">

<input

class="form-check-input"

type="checkbox">

<label>

Estepe

</label>

</div>

</div>

<div

class="tab-pane fade"

id="historico">

<?php

$hist=$conn->prepare("

SELECT *

FROM historico_os

WHERE id_os=?

ORDER BY data_hora DESC

");

$hist->execute([$id]);

foreach($hist as $h){

?>

<div class="border rounded p-3 mb-3">

<b>

<?=$h['descricao']?>

</b>

<br>

<small>

<?=$h['usuario']?>

-

<?=$h['data_hora']?>

</small>

</div>

<?php } ?>

</div>

</div>

</div>

</div>

</div>