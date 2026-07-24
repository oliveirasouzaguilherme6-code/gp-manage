<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| VALIDAR ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php?page=financeiro&erro=1");
    exit;
}

$id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| BUSCAR LANÇAMENTO
|--------------------------------------------------------------------------
*/

$sql = $conn->prepare("
    SELECT *
    FROM financeiro
    WHERE id_financeiro = ?
    LIMIT 1
");

$sql->execute([$id]);

$f = $sql->fetch(PDO::FETCH_ASSOC);

if (!$f) {
    header("Location: ../index.php?page=financeiro&erro=1");
    exit;
}

function e($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$formas = [
    "Pix",
    "Dinheiro",
    "Cartão",
    "Boleto",
    "Transferência"
];

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Editar Lançamento | GP Manager</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #f4f6f9;
        }

        .edit-container {
            max-width: 900px;
            margin: 50px auto;
        }

        .card {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header {
            background: #ffffff;
            padding: 22px 25px;
            border-bottom: 1px solid #e9ecef;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .card-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            min-height: 45px;
        }

        textarea.form-control {
            min-height: 100px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="edit-container">

        <div class="card shadow-sm">

            <div class="card-header">

                <h4>
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar Lançamento
                </h4>

            </div>

            <div class="card-body">

                <form
                    action="salvar_edicao_financeiro.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="id_financeiro"
                        value="<?= (int) $f['id_financeiro'] ?>"
                    >

                    <!-- TIPO -->

                    <div class="mb-3">

                        <label class="form-label">
                            Tipo
                        </label>

                        <select
                            name="tipo"
                            class="form-select"
                            required
                        >

                            <option
                                value="Receita"
                                <?= $f['tipo'] === "Receita" ? "selected" : "" ?>
                            >
                                Receita / Receber
                            </option>

                            <option
                                value="Despesa"
                                <?= $f['tipo'] === "Despesa" ? "selected" : "" ?>
                            >
                                Despesa / Pagar
                            </option>

                        </select>

                    </div>

                    <!-- CATEGORIA -->

                    <div class="mb-3">

                        <label class="form-label">
                            Categoria
                        </label>

                        <input
                            type="text"
                            name="categoria"
                            class="form-control"
                            maxlength="100"
                            value="<?= e($f['categoria']) ?>"
                            required
                        >

                    </div>

                    <!-- DESCRIÇÃO -->

                    <div class="mb-3">

                        <label class="form-label">
                            Descrição
                        </label>

                        <input
                            type="text"
                            name="descricao"
                            class="form-control"
                            maxlength="255"
                            value="<?= e($f['descricao']) ?>"
                            required
                        >

                    </div>

                    <div class="row">

                        <!-- VALOR -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Valor
                            </label>

                            <input
                                type="number"
                                name="valor"
                                class="form-control"
                                step="0.01"
                                min="0.01"
                                value="<?= e($f['valor']) ?>"
                                required
                            >

                        </div>

                        <!-- VENCIMENTO -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Data de Vencimento
                            </label>

                            <input
                                type="date"
                                name="data_vencimento"
                                class="form-control"
                                value="<?= e($f['data_vencimento']) ?>"
                            >

                        </div>

                    </div>

                    <div class="row">

                        <!-- PAGAMENTO -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Forma de Pagamento
                            </label>

                            <select
                                name="forma_pagamento"
                                class="form-select"
                            >

                                <option value="">
                                    Selecione
                                </option>

                                <?php foreach ($formas as $forma): ?>

                                    <option
                                        value="<?= e($forma) ?>"
                                        <?= $f['forma_pagamento'] === $forma ? "selected" : "" ?>
                                    >
                                        <?= e($forma) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- STATUS -->

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-select"
                                required
                            >

                                <option
                                    value="Pendente"
                                    <?= $f['status'] === "Pendente" ? "selected" : "" ?>
                                >
                                    Pendente
                                </option>

                                <option
                                    value="Pago"
                                    <?= $f['status'] === "Pago" ? "selected" : "" ?>
                                >
                                    Pago
                                </option>

                                <option
                                    value="Cancelado"
                                    <?= $f['status'] === "Cancelado" ? "selected" : "" ?>
                                >
                                    Cancelado
                                </option>

                            </select>

                        </div>

                    </div>

                    <!-- OBSERVAÇÕES -->

                    <div class="mb-4">

                        <label class="form-label">
                            Observações
                        </label>

                        <textarea
                            name="observacoes"
                            class="form-control"
                            rows="4"
                        ><?= e($f['observacoes']) ?></textarea>

                    </div>

                    <!-- BOTÕES -->

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            <i class="bi bi-check-circle me-1"></i>
                            Salvar Alterações
                        </button>

                        <a
                            href="../index.php?page=financeiro"
                            class="btn btn-secondary"
                        >
                            Cancelar
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>