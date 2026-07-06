<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->query("SELECT * FROM clientes ORDER BY id_cliente DESC");
$clientes = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<h2 class="fw-bold mb-4">
    Clientes
</h2>

<div class="card-dashboard bg-white p-4">

    <div class="d-flex justify-content-between mb-4">

        <h5>Lista de Clientes</h5>

        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#novoCliente">
            <i class="bi bi-plus-circle"></i>
            Novo Cliente
        </button>

    </div>

    <table class="table table-hover">

        <thead>

        <tr>

            <th>ID</th>

            <th>Nome</th>

            <th>Telefone</th>

            <th>Email</th>

            <th></th>

        </tr>

        </thead>

        <tbody>

        <?php foreach($clientes as $cliente): ?>

        <tr>

            <td><?= $cliente['id_cliente']; ?></td>

            <td><?= htmlspecialchars($cliente['nome']); ?></td>

            <td><?= htmlspecialchars($cliente['telefone']); ?></td>

            <td><?= htmlspecialchars($cliente['email']); ?></td>

            <td>

                <button class="btn btn-sm btn-primary">
                    Editar
                </button>

                <button class="btn btn-sm btn-danger">
                    Excluir
                </button>

            </td>

        </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<div class="modal fade" id="novoCliente">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<form action="pages/salvar_cliente.php" method="POST">

<div class="modal-header">

<h5>Novo Cliente</h5>

<button class="btn-close" data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="row">

<div class="col-md-6">

<label>Nome</label>

<input
type="text"
name="nome"
class="form-control"
required>

</div>

<div class="col-md-6">

<label>Telefone</label>

<input
type="text"
name="telefone"
class="form-control">

</div>

<div class="col-md-6 mt-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control">

</div>

<div class="col-md-6 mt-3">

<label>CPF</label>

<input
type="text"
name="cpf"
class="form-control">

</div>

<div class="col-12 mt-3">

<label>Cidade</label>

<input
type="text"
name="cidade"
class="form-control">

</div>

<div class="col-12 mt-3">

<label>Observações</label>

<textarea
name="observacoes"
class="form-control"
rows="3"></textarea>

</div>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-secondary" data-bs-dismiss="modal">

Cancelar

</button>

<button class="btn btn-warning">

Salvar Cliente

</button>

</div>

</form>

</div>

</div>

</div>