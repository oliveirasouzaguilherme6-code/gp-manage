<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$financeiro = $conn->query("
SELECT *
FROM financeiro
ORDER BY data_vencimento DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>Financeiro</h2>

    <button
        class="btn btn-warning"
        data-bs-toggle="modal"
        data-bs-target="#modalFinanceiro">

        <i class="bi bi-plus-circle"></i>
        Novo Lançamento

    </button>

</div>

<div class="card-dashboard bg-white p-4">

    <table class="table table-hover align-middle">

        <thead>

            <tr>

                <th>Tipo</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Status</th>
                <th width="150">Ações</th>

            </tr>

        </thead>

        <tbody>

        <?php if(count($financeiro)>0): ?>

            <?php foreach($financeiro as $f): ?>

            <tr>

                <td>

                    <?php

                    if($f['tipo']=="Receita"){
                        echo "<span class='badge bg-success'>Receber</span>";
                    }else{
                        echo "<span class='badge bg-danger'>Pagar</span>";
                    }

                    ?>

                </td>

                <td><?= htmlspecialchars($f['descricao']) ?></td>

                <td>

                    <strong>

                    R$ <?= number_format($f['valor'],2,",",".") ?>

                    </strong>

                </td>

                <td>

                    <?= date("d/m/Y", strtotime($f['data_vencimento'])) ?>

                </td>

                <td>

                    <?php

                    switch($f['status']){

                        case "Pago":
                            echo "<span class='badge bg-success'>Pago</span>";
                            break;

                        case "Cancelado":
                            echo "<span class='badge bg-danger'>Cancelado</span>";
                            break;

                        default:
                            echo "<span class='badge bg-warning text-dark'>Pendente</span>";
                            break;

                    }

                    ?>

                </td>

                <td>

                    <a
                        href="actions/editar_financeiro.php?id=<?=$f['id_financeiro']?>"
                        class="btn btn-primary btn-sm">

                        <i class="bi bi-pencil"></i>

                    </a>

                    <a
                        href="actions/excluir_financeiro.php?id=<?=$f['id_financeiro']?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Deseja realmente excluir este lançamento?')">

                        <i class="bi bi-trash"></i>

                    </a>

                </td>

            </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="6" class="text-center text-muted py-4">

                    Nenhum lançamento encontrado.

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

<div class="modal fade" id="modalFinanceiro">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="actions/salvar_financeiro.php" method="POST">

                <div class="modal-header">

                    <h5>Novo Lançamento</h5>

                    <button
                        class="btn-close"
                        data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <label>Tipo</label>

                    <select
                        name="tipo"
                        class="form-select">

                        <option value="Receita">Receber</option>
                        <option value="Despesa">Pagar</option>

                    </select>

                    <label class="mt-3">Descrição</label>

                    <input
                        type="text"
                        name="descricao"
                        class="form-control"
                        required>

                    <label class="mt-3">Valor</label>

                    <input
                        type="number"
                        step="0.01"
                        name="valor"
                        class="form-control"
                        required>

                    <label class="mt-3">Vencimento</label>

                    <input
                        type="date"
                        name="data_vencimento"
                        class="form-control">

                    <label class="mt-3">Forma de Pagamento</label>

                    <select
                        name="forma_pagamento"
                        class="form-select">

                        <option>Pix</option>
                        <option>Dinheiro</option>
                        <option>Cartão</option>
                        <option>Boleto</option>
                        <option>Transferência</option>

                    </select>

                    <label class="mt-3">Observações</label>

                    <textarea
                        name="observacoes"
                        class="form-control"
                        rows="3"></textarea>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
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