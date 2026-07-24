<?php

require_once __DIR__ . "/../config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| BUSCAR LANÇAMENTOS
|--------------------------------------------------------------------------
*/

$sql = $conn->query("
    SELECT *
    FROM financeiro
    ORDER BY
        CASE
            WHEN status = 'Pendente' THEN 0
            ELSE 1
        END,
        data_vencimento DESC,
        id_financeiro DESC
");

$financeiro = $sql->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| RESUMO FINANCEIRO
|--------------------------------------------------------------------------
*/

$resumo = $conn->query("
    SELECT

        COALESCE(SUM(
            CASE
                WHEN tipo = 'Receita'
                AND status = 'Pago'
                THEN valor
                ELSE 0
            END
        ), 0) AS receitas,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'Despesa'
                AND status = 'Pago'
                THEN valor
                ELSE 0
            END
        ), 0) AS despesas,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'Receita'
                AND status = 'Pendente'
                THEN valor
                ELSE 0
            END
        ), 0) AS receber,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'Despesa'
                AND status = 'Pendente'
                THEN valor
                ELSE 0
            END
        ), 0) AS pagar

    FROM financeiro
")->fetch(PDO::FETCH_ASSOC);

$receitas = (float)($resumo["receitas"] ?? 0);
$despesas = (float)($resumo["despesas"] ?? 0);
$receber  = (float)($resumo["receber"] ?? 0);
$pagar    = (float)($resumo["pagar"] ?? 0);

$saldo = $receitas - $despesas;

/*
|--------------------------------------------------------------------------
| MENSAGENS
|--------------------------------------------------------------------------
*/

$mensagemSucesso = "";
$mensagemErro = "";

$sucesso = $_GET["sucesso"] ?? "";
$erro = $_GET["erro"] ?? "";

switch ($sucesso) {

    case "cadastrado":
        $mensagemSucesso = "Lançamento cadastrado com sucesso.";
        break;

    case "editado":
        $mensagemSucesso = "Lançamento atualizado com sucesso.";
        break;

    case "excluido":
        $mensagemSucesso = "Lançamento excluído com sucesso.";
        break;

    case "pago":
        $mensagemSucesso = "Pagamento confirmado com sucesso.";
        break;
}

switch ($erro) {

    case "campos":
        $mensagemErro = "Preencha corretamente os campos obrigatórios.";
        break;

    case "tipo":
        $mensagemErro = "O tipo do lançamento é inválido.";
        break;

    case "valor":
        $mensagemErro = "Informe um valor válido.";
        break;

    case "data":
        $mensagemErro = "A data informada é inválida.";
        break;

    case "pagamento":
        $mensagemErro = "A forma de pagamento informada é inválida.";
        break;

    case "status":
        $mensagemErro = "O status informado é inválido.";
        break;

    case "lancamento":
        $mensagemErro = "Lançamento financeiro não encontrado.";
        break;

    case "acesso":
    case "permissao":
        $mensagemErro = "Você não possui permissão para realizar esta operação.";
        break;

    case "banco":
        $mensagemErro = "Não foi possível concluir a operação no banco de dados.";
        break;

    default:

        if ($erro !== "") {
            $mensagemErro = "Não foi possível realizar a operação.";
        }

        break;
}

/*
|--------------------------------------------------------------------------
| FUNÇÃO DE ESCAPE
|--------------------------------------------------------------------------
*/

function financeiroEscape($valor): string
{
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!--
|--------------------------------------------------------------------------
| CABEÇALHO
|--------------------------------------------------------------------------
-->

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

    <div>

        <h2 class="mb-1">
            Financeiro
        </h2>

        <div class="text-muted">
            Controle de receitas, despesas e pagamentos
        </div>

    </div>

    <button
        type="button"
        class="btn btn-warning"
        data-bs-toggle="modal"
        data-bs-target="#modalFinanceiro">

        <i class="bi bi-plus-circle me-1"></i>

        Novo Lançamento

    </button>

</div>


<!--
|--------------------------------------------------------------------------
| MENSAGEM DE SUCESSO
|--------------------------------------------------------------------------
-->

<?php if ($mensagemSucesso !== ""): ?>

    <div
        class="alert alert-success alert-dismissible fade show shadow-sm"
        role="alert">

        <i class="bi bi-check-circle-fill me-2"></i>

        <?= financeiroEscape($mensagemSucesso) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Fechar">
        </button>

    </div>

<?php endif; ?>


<!--
|--------------------------------------------------------------------------
| MENSAGEM DE ERRO
|--------------------------------------------------------------------------
-->

<?php if ($mensagemErro !== ""): ?>

    <div
        class="alert alert-danger alert-dismissible fade show shadow-sm"
        role="alert">

        <i class="bi bi-exclamation-triangle-fill me-2"></i>

        <?= financeiroEscape($mensagemErro) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Fechar">
        </button>

    </div>

<?php endif; ?>


<!--
|--------------------------------------------------------------------------
| CARDS
|--------------------------------------------------------------------------
-->

<div class="row g-3 mb-4">

    <!-- RECEITAS -->

    <div class="col-12 col-sm-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-2">
                            Receitas recebidas
                        </div>

                        <h4 class="mb-0 text-success">

                            R$ <?= number_format(
                                $receitas,
                                2,
                                ",",
                                "."
                            ) ?>

                        </h4>

                    </div>

                    <div
                        class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-arrow-up-circle fs-4 text-success"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- DESPESAS -->

    <div class="col-12 col-sm-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-2">
                            Despesas pagas
                        </div>

                        <h4 class="mb-0 text-danger">

                            R$ <?= number_format(
                                $despesas,
                                2,
                                ",",
                                "."
                            ) ?>

                        </h4>

                    </div>

                    <div
                        class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-arrow-down-circle fs-4 text-danger"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- SALDO -->

    <div class="col-12 col-sm-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-2">
                            Saldo realizado
                        </div>

                        <h4
                            class="mb-0 <?= $saldo >= 0 ? "text-primary" : "text-danger" ?>">

                            R$ <?= number_format(
                                $saldo,
                                2,
                                ",",
                                "."
                            ) ?>

                        </h4>

                    </div>

                    <div
                        class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-wallet2 fs-4 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- PENDENTES -->

    <div class="col-12 col-sm-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="text-muted small mb-1">
                            Pendente
                        </div>

                        <div class="small text-success mb-1">

                            A receber:
                            <strong>
                                R$ <?= number_format($receber, 2, ",", ".") ?>
                            </strong>

                        </div>

                        <div class="small text-danger">

                            A pagar:
                            <strong>
                                R$ <?= number_format($pagar, 2, ",", ".") ?>
                            </strong>

                        </div>

                    </div>

                    <div
                        class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:46px;height:46px;">

                        <i class="bi bi-clock-history fs-4 text-warning"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<!--
|--------------------------------------------------------------------------
| TABELA
|--------------------------------------------------------------------------
-->

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>

                <h5 class="mb-1">
                    Lançamentos financeiros
                </h5>

                <small class="text-muted">

                    <?= count($financeiro) ?>

                    <?= count($financeiro) === 1
                        ? "lançamento cadastrado"
                        : "lançamentos cadastrados" ?>

                </small>

            </div>

        </div>

    </div>

    <div class="card-body p-4">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th>Tipo</th>

                        <th>Categoria</th>

                        <th>Descrição</th>

                        <th>Valor</th>

                        <th>Vencimento</th>

                        <th>Pagamento</th>

                        <th>Status</th>

                        <th
                            class="text-end"
                            style="min-width:150px;">

                            Ações

                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php if (!empty($financeiro)): ?>

                    <?php foreach ($financeiro as $f): ?>

                        <?php

                        $idFinanceiro =
                            (int)$f["id_financeiro"];

                        ?>

                        <tr>

                            <!-- TIPO -->

                            <td>

                                <?php if ($f["tipo"] === "Receita"): ?>

                                    <span class="badge bg-success">

                                        <i class="bi bi-arrow-up me-1"></i>

                                        Receita

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-danger">

                                        <i class="bi bi-arrow-down me-1"></i>

                                        Despesa

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- CATEGORIA -->

                            <td>

                                <?php if (!empty($f["categoria"])): ?>

                                    <?= financeiroEscape($f["categoria"]) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- DESCRIÇÃO -->

                            <td>

                                <div class="fw-semibold">

                                    <?= financeiroEscape(
                                        $f["descricao"] ?? ""
                                    ) ?>

                                </div>

                                <?php if (!empty($f["observacoes"])): ?>

                                    <small class="text-muted">

                                        <?= financeiroEscape(
                                            $f["observacoes"]
                                        ) ?>

                                    </small>

                                <?php endif; ?>

                            </td>


                            <!-- VALOR -->

                            <td>

                                <strong>

                                    R$ <?= number_format(
                                        (float)$f["valor"],
                                        2,
                                        ",",
                                        "."
                                    ) ?>

                                </strong>

                            </td>


                            <!-- VENCIMENTO -->

                            <td>

                                <?php if (!empty($f["data_vencimento"])): ?>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime($f["data_vencimento"])
                                    ) ?>

                                    <?php

                                    $vencido =
                                        $f["status"] === "Pendente" &&
                                        $f["data_vencimento"] < date("Y-m-d");

                                    ?>

                                    <?php if ($vencido): ?>

                                        <div>

                                            <span class="badge bg-danger mt-1">
                                                Vencido
                                            </span>

                                        </div>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- PAGAMENTO -->

                            <td>

                                <?php if (!empty($f["data_pagamento"])): ?>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime($f["data_pagamento"])
                                    ) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ($f["status"] === "Pago"): ?>

                                    <span class="badge rounded-pill bg-success">

                                        <i class="bi bi-check-circle me-1"></i>

                                        Pago

                                    </span>

                                <?php elseif ($f["status"] === "Cancelado"): ?>

                                    <span class="badge rounded-pill bg-secondary">

                                        <i class="bi bi-x-circle me-1"></i>

                                        Cancelado

                                    </span>

                                <?php else: ?>

                                    <span class="badge rounded-pill bg-warning text-dark">

                                        <i class="bi bi-clock me-1"></i>

                                        Pendente

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- AÇÕES -->

                            <td class="text-end">

                                <div class="d-inline-flex gap-1">

                                    <!-- PAGAR -->

                                    <?php if ($f["status"] === "Pendente"): ?>

                                        <form
                                            action="actions/pagar_financeiro.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Confirmar o pagamento deste lançamento?');">

                                            <input
                                                type="hidden"
                                                name="id_financeiro"
                                                value="<?= $idFinanceiro ?>">

                                            <button
                                                type="submit"
                                                class="btn btn-success btn-sm"
                                                title="Marcar como pago">

                                                <i class="bi bi-check-lg"></i>

                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <!-- EDITAR -->

                                    <a
                                        href="actions/editar_financeiro.php?id=<?= $idFinanceiro ?>"
                                        class="btn btn-primary btn-sm"
                                        title="Editar lançamento">

                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    <!-- EXCLUIR -->

                                    <form
                                        action="actions/excluir_financeiro.php"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Deseja realmente excluir este lançamento? Esta ação não poderá ser desfeita.');">

                                        <input
                                            type="hidden"
                                            name="id_financeiro"
                                            value="<?= $idFinanceiro ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                            title="Excluir lançamento">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="8"
                            class="text-center text-muted py-5">

                            <i class="bi bi-wallet2 fs-1 d-block mb-3"></i>

                            <strong>
                                Nenhum lançamento financeiro cadastrado
                            </strong>

                            <div class="small mt-1">
                                Utilize "Novo Lançamento" para começar.
                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!--
|--------------------------------------------------------------------------
| MODAL NOVO LANÇAMENTO
|--------------------------------------------------------------------------
-->

<div
    class="modal fade"
    id="modalFinanceiro"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                action="actions/salvar_financeiro.php"
                method="POST">

                <div class="modal-header">

                    <div>

                        <h5 class="modal-title mb-1">

                            <i class="bi bi-wallet2 me-2"></i>

                            Novo Lançamento

                        </h5>

                        <small class="text-muted">
                            Registre uma receita ou despesa
                        </small>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar">
                    </button>

                </div>


                <div class="modal-body">

                    <!-- TIPO -->

                    <div class="mb-3">

                        <label class="form-label">
                            Tipo *
                        </label>

                        <select
                            name="tipo"
                            class="form-select"
                            required>

                            <option value="">
                                Selecione
                            </option>

                            <option value="Receita">
                                Receita
                            </option>

                            <option value="Despesa">
                                Despesa
                            </option>

                        </select>

                    </div>


                    <!-- CATEGORIA -->

                    <div class="mb-3">

                        <label class="form-label">
                            Categoria *
                        </label>

                        <input
                            type="text"
                            name="categoria"
                            class="form-control"
                            placeholder="Ex: Serviço, Peças, Aluguel..."
                            maxlength="100"
                            required>

                    </div>


                    <!-- DESCRIÇÃO -->

                    <div class="mb-3">

                        <label class="form-label">
                            Descrição *
                        </label>

                        <input
                            type="text"
                            name="descricao"
                            class="form-control"
                            placeholder="Descrição do lançamento"
                            maxlength="255"
                            required>

                    </div>


                    <!-- VALOR -->

                    <div class="mb-3">

                        <label class="form-label">
                            Valor *
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                R$
                            </span>

                            <input
                                type="number"
                                name="valor"
                                class="form-control"
                                min="0.01"
                                step="0.01"
                                required>

                        </div>

                    </div>


                    <!-- VENCIMENTO -->

                    <div class="mb-3">

                        <label class="form-label">
                            Data de vencimento
                        </label>

                        <input
                            type="date"
                            name="data_vencimento"
                            class="form-control">

                    </div>


                    <!-- FORMA DE PAGAMENTO -->

                    <div class="mb-3">

                        <label class="form-label">
                            Forma de pagamento
                        </label>

                        <select
                            name="forma_pagamento"
                            class="form-select">

                            <option value="">
                                Selecione
                            </option>

                            <option value="Pix">
                                Pix
                            </option>

                            <option value="Dinheiro">
                                Dinheiro
                            </option>

                            <option value="Cartão">
                                Cartão
                            </option>

                            <option value="Boleto">
                                Boleto
                            </option>

                            <option value="Transferência">
                                Transferência
                            </option>

                        </select>

                    </div>


                    <!-- OBSERVAÇÕES -->

                    <div>

                        <label class="form-label">
                            Observações
                        </label>

                        <textarea
                            name="observacoes"
                            class="form-control"
                            rows="3"
                            maxlength="1000"
                            placeholder="Informações adicionais..."></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="bi bi-check-lg me-1"></i>

                        Salvar Lançamento

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>