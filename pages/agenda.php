<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

/* Lista Agendamentos */

$sql = $conn->query("
SELECT

agenda.*,

clientes.nome,

veiculos.modelo,

veiculos.placa

FROM agenda

INNER JOIN clientes
ON clientes.id_cliente = agenda.id_cliente

INNER JOIN veiculos
ON veiculos.id_veiculo = agenda.id_veiculo

ORDER BY data,hora

");

$agenda = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<h2 class="fw-bold mb-4">

    Agenda

</h2>

<div class="card-dashboard bg-white p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h5 class="mb-0">

            Agenda de Serviços

        </h5>

        <button
            class="btn btn-warning"
            data-bs-toggle="modal"
            data-bs-target="#novaAgenda">

            <i class="bi bi-plus-circle"></i>

            Novo Agendamento

        </button>

    </div>

    <table class="table table-hover align-middle">

        <thead>

            <tr>

                <th>Data</th>

                <th>Hora</th>

                <th>Cliente</th>

                <th>Veículo</th>

                <th>Título</th>

                <th>Status</th>

                <th width="120">

                    Ações

                </th>

            </tr>

        </thead>

        <tbody>

        <?php foreach($agenda as $a): ?>

            <tr>

                <td>

                    <?= date('d/m/Y',strtotime($a['data'])) ?>

                </td>

                <td>

                    <?= substr($a['hora'],0,5) ?>

                </td>

                <td>

                    <?= htmlspecialchars($a['nome']) ?>

                </td>

                <td>

                    <?= htmlspecialchars($a['modelo']) ?>

                    -

                    <?= htmlspecialchars($a['placa']) ?>

                </td>

                <td>

                    <?= htmlspecialchars($a['titulo']) ?>

                </td>

                <td>

<?php

$cor = "secondary";

switch($a['status']){

    case "Agendado":
        $cor = "primary";
    break;

    case "Confirmado":
        $cor = "info";
    break;

    case "Em Atendimento":
        $cor = "warning";
    break;

    case "Concluído":
        $cor = "success";
    break;

    case "Cancelado":
        $cor = "danger";
    break;

}

?>

<span class="badge bg-<?= $cor ?>">

<?= htmlspecialchars($a['status']) ?>

</span>

                </td>

                <td>

                    <a
                    href="index.php?page=editar_agendamento&id=<?= $a['id_agenda']; ?>"
                    class="btn btn-warning btn-sm">

                        <i class="bi bi-pencil"></i>

                    </a>

                    <a
                    href="actions/excluir_agendamento.php?id=<?= $a['id_agenda']; ?>"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Deseja realmente excluir este agendamento?')">

                        <i class="bi bi-trash"></i>

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

?>

<div class="modal fade" id="novaAgenda" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form
                action="actions/salvar_agendamento.php"
                method="POST">

                <div class="modal-header">

                    <h5 class="modal-title">

                        Novo Agendamento

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6">

                            <label class="form-label">

                                Cliente

                            </label>

                            <select
                                name="id_cliente"
                                class="form-select"
                                required>

                                <option value="">

                                    Selecione...

                                </option>

                                <?php foreach($clientes as $cliente): ?>

                                    <option value="<?= $cliente['id_cliente']; ?>">

                                        <?= htmlspecialchars($cliente['nome']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">

                                Veículo

                            </label>

                            <select
                                name="id_veiculo"
                                class="form-select"
                                required>

                                <option value="">

                                    Selecione...

                                </option>

                                <?php foreach($veiculos as $veiculo): ?>

                                    <option value="<?= $veiculo['id_veiculo']; ?>">

                                        <?= htmlspecialchars($veiculo['modelo']); ?>

                                        -

                                        <?= htmlspecialchars($veiculo['placa']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-8 mt-3">

                            <label class="form-label">

                                Título

                            </label>

                            <input
                                type="text"
                                name="titulo"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-4 mt-3">

                            <label class="form-label">

                                Status

                            </label>

                            <select
                                name="status"
                                class="form-select">

                                <option value="Agendado">

                                    Agendado

                                </option>

                                <option value="Confirmado">

                                    Confirmado

                                </option>

                                <option value="Em Atendimento">

                                    Em Atendimento

                                </option>

                                <option value="Concluído">

                                    Concluído

                                </option>

                                <option value="Cancelado">

                                    Cancelado

                                </option>

                            </select>

                        </div>

                        <div class="col-md-6 mt-3">

                            <label class="form-label">

                                Data

                            </label>

                            <input
                                type="date"
                                name="data"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-md-6 mt-3">

                            <label class="form-label">

                                Hora

                            </label>

                            <input
                                type="time"
                                name="hora"
                                class="form-control"
                                required>

                        </div>

                        <div class="col-12 mt-3">

                            <label class="form-label">

                                Descrição

                            </label>

                            <textarea
                                name="descricao"
                                class="form-control"
                                rows="4"></textarea>

                        </div>

                    </div>


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

                        <i class="bi bi-check-circle"></i>

                        Salvar Agendamento

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>