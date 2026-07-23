<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

if(!isset($_GET['id'])){
    header("Location: index.php?page=agenda");
    exit;
}

$id = intval($_GET['id']);

$sql = $conn->prepare("
SELECT *
FROM agenda
WHERE id_agenda = ?
");

$sql->execute([$id]);

$agendamento = $sql->fetch(PDO::FETCH_ASSOC);

if(!$agendamento){
    header("Location: index.php?page=agenda");
    exit;
}

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

?>

<div class="card-dashboard bg-white p-4">

<h2 class="fw-bold mb-4">

Editar Agendamento

</h2>

<form action="actions/atualizar_agendamento.php" method="POST">

<input
type="hidden"
name="id_agenda"
value="<?= $agendamento['id_agenda']; ?>">

<div class="row">

<div class="col-md-6">

<label>Cliente</label>

<select
name="id_cliente"
class="form-select"
required>

<?php foreach($clientes as $cliente): ?>

<option
value="<?= $cliente['id_cliente']; ?>"
<?= $cliente['id_cliente']==$agendamento['id_cliente'] ? "selected":"" ?>>

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

<?php foreach($veiculos as $veiculo): ?>

<option
value="<?= $veiculo['id_veiculo']; ?>"
<?= $veiculo['id_veiculo']==$agendamento['id_veiculo'] ? "selected":"" ?>>

<?= htmlspecialchars($veiculo['modelo']); ?>

- <?= htmlspecialchars($veiculo['placa']); ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-8 mt-3">

<label>Título</label>

<input
type="text"
name="titulo"
class="form-control"
value="<?= htmlspecialchars($agendamento['titulo']); ?>"
required>

</div>

<div class="col-md-4 mt-3">

<label>Status</label>

<select
name="status"
class="form-select">

<?php

$status = [

"Agendado",
"Em andamento",
"Concluído",
"Cancelado"

];

foreach($status as $s){

?>

<option
value="<?= $s ?>"
<?= $agendamento['status']==$s ? "selected":"" ?>>

<?= $s ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6 mt-3">

<label>Data</label>

<input
type="date"
name="data"
class="form-control"
value="<?= $agendamento['data']; ?>"
required>

</div>

<div class="col-md-6 mt-3">

<label>Hora</label>

<input
type="time"
name="hora"
class="form-control"
value="<?= substr($agendamento['hora'],0,5); ?>"
required>

</div>

<div class="col-12 mt-3">

<label>Descrição</label>

<textarea
name="descricao"
rows="4"
class="form-control"><?= htmlspecialchars($agendamento['descricao']); ?></textarea>

</div>

<div class="col-12 mt-4">

<button
class="btn btn-warning">

Salvar Alterações

</button>

<a
href="index.php?page=agenda"
class="btn btn-secondary">

Cancelar

</a>

</div>

</div>

</form>

</div>