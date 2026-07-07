<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->query("
SELECT *
FROM clientes
ORDER BY id_cliente DESC
");

$clientes = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Clientes
        </h2>

        <small class="text-muted">
            Gerencie todos os clientes cadastrados.
        </small>

    </div>

    <button
        class="btn btn-warning"
        onclick="novoCliente()">

        <i class="bi bi-plus-circle"></i>

        Novo Cliente

    </button>

</div>

<div class="card card-dashboard">

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>

<th width="80">

Foto

</th>

<th>ID</th>

<th>Nome</th>

<th>Telefone</th>

<th>Email</th>

<th width="150">

Ações

</th>

</tr>

</thead>

<tbody>

<?php foreach($clientes as $cliente): ?>

<tr>

<td>

<?php if(!empty($cliente['foto'])): ?>

<img

src="uploads/clientes/<?= $cliente['foto']; ?>"

style="

width:55px;

height:55px;

object-fit:cover;

border-radius:50%;

border:2px solid #eee;

">

<?php else: ?>

<img

src="https://placehold.co/55x55"

style="border-radius:50%;">

<?php endif; ?>

</td>

<td>

<?= $cliente['id_cliente']; ?>

</td>

<td>

<strong>

<?= htmlspecialchars($cliente['nome']); ?>

</strong>

</td>

<td>

<?= htmlspecialchars($cliente['telefone']); ?>

</td>

<td>

<?= htmlspecialchars($cliente['email']); ?>

</td>

<td>

<button

class="btn btn-primary btn-sm"

onclick="editarCliente(<?= $cliente['id_cliente']; ?>)">

<i class="bi bi-pencil-square"></i>

</button>

<a

href="actions/excluir_cliente.php?id=<?= $cliente['id_cliente']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Deseja realmente excluir este cliente?')">

<i class="bi bi-trash"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- Modal Cliente -->
<div class="modal fade" id="novoCliente" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form
                id="formCliente"
                action="actions/salvar_cliente.php"
                method="POST"
                enctype="multipart/form-data">

                <input
                    type="hidden"
                    name="id_cliente"
                    id="id_cliente">

                <div class="modal-header">

                    <h5 id="tituloModal">

                        Novo Cliente

                    </h5>

                    <button
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6">

                            <label class="form-label">

                                Nome

                            </label>

                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">

                                Telefone

                            </label>

                            <input
                                type="text"
                                name="telefone"
                                id="telefone"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mt-3">

                            <label class="form-label">

                                Email

                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mt-3">

                            <label class="form-label">

                                CPF

                            </label>

                            <input
                                type="text"
                                name="cpf"
                                id="cpf"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mt-3">

                            <label class="form-label">

                                Cidade

                            </label>

                            <input
                                type="text"
                                name="cidade"
                                id="cidade"
                                class="form-control">

                        </div>

                        <div class="col-md-6 mt-3">

                            <label class="form-label">

                                Foto

                            </label>

                            <input
                                type="file"
                                name="foto"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp">

                        </div>

                        <div class="col-12 mt-3">

                            <label class="form-label">

                                Observações

                            </label>

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
                        type="submit"
                        class="btn btn-warning">

                        <i class="bi bi-check-circle"></i>

                        Salvar Cliente

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
<script>

function novoCliente(){

    document.getElementById("tituloModal").innerHTML="Novo Cliente";

    document.getElementById("formCliente").reset();

    document.getElementById("id_cliente").value="";

    document.getElementById("formCliente").action="actions/salvar_cliente.php";

    new bootstrap.Modal(document.getElementById("novoCliente")).show();

}

function editarCliente(id){

    fetch("actions/buscar_cliente.php?id="+id)

    .then(response=>response.json())

    .then(cliente=>{

        document.getElementById("tituloModal").innerHTML="Editar Cliente";

        document.getElementById("id_cliente").value=cliente.id_cliente;

        document.getElementById("nome").value=cliente.nome;

        document.getElementById("telefone").value=cliente.telefone;

        document.getElementById("email").value=cliente.email;

        document.getElementById("cpf").value=cliente.cpf;

        document.getElementById("cidade").value=cliente.cidade;

        document.getElementById("observacoes").value=cliente.observacoes;

        document.getElementById("formCliente").action="actions/editar_cliente.php";

        new bootstrap.Modal(document.getElementById("novoCliente")).show();

    });

}

</script>