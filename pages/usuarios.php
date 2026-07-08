<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$usuarios = $conn->query("
SELECT *
FROM usuarios
ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>Usuários</h2>

<button
class="btn btn-warning"
data-bs-toggle="modal"
data-bs-target="#modalUsuario">

Novo Usuário

</button>

</div>

<div class="card-dashboard bg-white p-4">

<table class="table table-hover">

<thead>

<tr>

<th>Nome</th>

<th>Email</th>

<th>Nível</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($usuarios as $u): ?>

<tr>

<td><?=htmlspecialchars($u['nome'])?></td>

<td><?=htmlspecialchars($u['email'])?></td>

<td><?=$u['nivel']?></td>

<td>

<?php if($u['ativo']){ ?>

<span class="badge bg-success">

Ativo

</span>

<?php }else{ ?>

<span class="badge bg-danger">

Inativo

</span>

<?php } ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<!-- Modal -->

<div class="modal fade" id="modalUsuario">

<div class="modal-dialog">

<div class="modal-content">

<form action="actions/salvar_usuario.php" method="POST">

<div class="modal-header">

<h5>Novo Usuário</h5>

<button class="btn-close" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">

<label>Nome</label>

<input
type="text"
name="nome"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Senha</label>

<input
type="password"
name="senha"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Nível</label>

<select
name="nivel"
class="form-select">

<option>Administrador</option>

<option>Recepção</option>

<option>Funilaria</option>

<option>Pintura</option>

<option>Financeiro</option>

</select>

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

Salvar

</button>

</div>

</form>

</div>

</div>

</div>