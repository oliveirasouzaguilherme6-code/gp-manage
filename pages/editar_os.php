<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

if(!isset($_GET['id'])){
    header("Location: index.php?page=os");
    exit;
}

$id = intval($_GET['id']);

$sql = $conn->prepare("
SELECT *
FROM ordens_servico
WHERE id_os=?
");

$sql->execute([$id]);

$os = $sql->fetch(PDO::FETCH_ASSOC);

if(!$os){
    die("Ordem de Serviço não encontrada.");
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

Editar Ordem de Serviço

</h2>

<form
action="actions/atualizar_os.php"
method="POST">

<input
type="hidden"
name="id_os"
value="<?= $os['id_os']; ?>">

<div class="row">

<div class="col-md-6 mb-3">

<label>Cliente</label>

<select
name="id_cliente"
class="form-select"
required>

<?php foreach($clientes as $cliente): ?>

<option
value="<?= $cliente['id_cliente']; ?>"
<?= $cliente['id_cliente']==$os['id_cliente'] ? 'selected' : ''; ?>>

<?= htmlspecialchars($cliente['nome']); ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Veículo</label>

<select
name="id_veiculo"
class="form-select"
required>

<?php foreach($veiculos as $veiculo): ?>

<option
value="<?= $veiculo['id_veiculo']; ?>"
<?= $veiculo['id_veiculo']==$os['id_veiculo'] ? 'selected' : ''; ?>>

<?= htmlspecialchars($veiculo['modelo']); ?>

-

<?= htmlspecialchars($veiculo['placa']); ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-4 mb-3">

<label>Status</label>

<select
name="status"
class="form-select">

<option <?= $os['status']=="Recebido" ? "selected":"" ?>>Recebido</option>

<option <?= $os['status']=="Aguardando Aprovação" ? "selected":"" ?>>Aguardando Aprovação</option>

<option <?= $os['status']=="Aguardando Peças" ? "selected":"" ?>>Aguardando Peças</option>

<option <?= $os['status']=="Em Serviço" ? "selected":"" ?>>Em Serviço</option>

<option <?= $os['status']=="Finalizado" ? "selected":"" ?>>Finalizado</option>

<option <?= $os['status']=="Entregue" ? "selected":"" ?>>Entregue</option>

<option <?= $os['status']=="Cancelado" ? "selected":"" ?>>Cancelado</option>

</select>

</div>

<div class="col-md-4 mb-3">

<label>Previsão</label>

<input
type="date"
name="previsao"
class="form-control"
value="<?= $os['previsao']; ?>">

</div>

<div class="col-md-4 mb-3">

<label>Mão de Obra</label>

<input
type="number"
step="0.01"
name="valor_mao_obra"
class="form-control"
value="<?= $os['valor_mao_obra']; ?>">

</div>

<div class="col-12 mb-3">

<label>Observações</label>

<textarea
name="observacoes"
rows="6"
class="form-control"><?= htmlspecialchars($os['observacoes']); ?></textarea>

</div>

<div class="col-12">

<button
class="btn btn-warning">

<i class="bi bi-check-circle"></i>

Salvar Alterações

</button>

<a
href="index.php?page=ver_os&id=<?= $os['id_os']; ?>"
class="btn btn-secondary">

Cancelar

</a>

</div>

</div>

</form>

</div>