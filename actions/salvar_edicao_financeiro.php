<?php

/*
|--------------------------------------------------------------------------
| GP MANAGER - SALVAR EDIÇÃO FINANCEIRA
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/database.php";

/*
|--------------------------------------------------------------------------
| SEGURANÇA
|--------------------------------------------------------------------------
*/

exigirLogin();

exigirNivel([
    "Administrador Geral",
    "Administrador",
    "Financeiro"
]);

exigirPost();

/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$idFinanceiro = filter_input(
    INPUT_POST,
    "id_financeiro",
    FILTER_VALIDATE_INT
);

$tipo = trim((string)($_POST["tipo"] ?? ""));
$descricao = trim((string)($_POST["descricao"] ?? ""));
$valor = $_POST["valor"] ?? "";
$formaPagamento = trim((string)($_POST["forma_pagamento"] ?? ""));
$status = trim((string)($_POST["status"] ?? ""));
$dataVencimento = trim((string)($_POST["data_vencimento"] ?? ""));

/*
|--------------------------------------------------------------------------
| VALIDAR ID
|--------------------------------------------------------------------------
*/

if (!$idFinanceiro || $idFinanceiro <= 0) {

    redirecionar(
        "financeiro",
        ["erro" => "lancamento"]
    );
}

/*
|--------------------------------------------------------------------------
| CAMPOS OBRIGATÓRIOS
|--------------------------------------------------------------------------
*/

if (
    $tipo === "" ||
    $descricao === "" ||
    $valor === "" ||
    $status === ""
) {

    redirecionar(
        "financeiro",
        ["erro" => "campos"]
    );
}

/*
|--------------------------------------------------------------------------
| VALIDAR TIPO
|--------------------------------------------------------------------------
*/

$tiposPermitidos = [
    "Receita",
    "Despesa"
];

if (!in_array($tipo, $tiposPermitidos, true)) {

    redirecionar(
        "financeiro",
        ["erro" => "tipo"]
    );
}

/*
|--------------------------------------------------------------------------
| VALIDAR VALOR
|--------------------------------------------------------------------------
*/

if (
    !is_numeric($valor) ||
    (float)$valor <= 0
) {

    redirecionar(
        "financeiro",
        ["erro" => "valor"]
    );
}

$valor = round((float)$valor, 2);

/*
|--------------------------------------------------------------------------
| VALIDAR STATUS
|--------------------------------------------------------------------------
*/

$statusPermitidos = [
    "Pendente",
    "Pago",
    "Cancelado"
];

if (!in_array($status, $statusPermitidos, true)) {

    redirecionar(
        "financeiro",
        ["erro" => "status"]
    );
}

/*
|--------------------------------------------------------------------------
| VALIDAR FORMA DE PAGAMENTO
|--------------------------------------------------------------------------
*/

$formasPermitidas = [
    "Pix",
    "Dinheiro",
    "Cartão",
    "Boleto",
    "Transferência"
];

if (
    $formaPagamento !== "" &&
    !in_array(
        $formaPagamento,
        $formasPermitidas,
        true
    )
) {

    redirecionar(
        "financeiro",
        ["erro" => "pagamento"]
    );
}

/*
|--------------------------------------------------------------------------
| VALIDAR DATA
|--------------------------------------------------------------------------
*/

if ($dataVencimento === "") {

    $dataVencimento = null;

} else {

    $data = DateTime::createFromFormat(
        "Y-m-d",
        $dataVencimento
    );

    if (
        !$data ||
        $data->format("Y-m-d") !== $dataVencimento
    ) {

        redirecionar(
            "financeiro",
            ["erro" => "data"]
        );
    }
}

/*
|--------------------------------------------------------------------------
| CONEXÃO
|--------------------------------------------------------------------------
*/

try {

    $db = new Database();
    $conn = $db->connect();

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE LANÇAMENTO EXISTE
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT id_financeiro
        FROM financeiro
        WHERE id_financeiro = ?
        LIMIT 1
    ");

    $sql->execute([
        $idFinanceiro
    ]);

    if (!$sql->fetch(PDO::FETCH_ASSOC)) {

        redirecionar(
            "financeiro",
            ["erro" => "lancamento"]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        UPDATE financeiro
        SET
            tipo = ?,
            descricao = ?,
            valor = ?,
            forma_pagamento = ?,
            status = ?,
            data_vencimento = ?
        WHERE id_financeiro = ?
    ");

    $sql->execute([
        $tipo,
        $descricao,
        $valor,
        $formaPagamento,
        $status,
        $dataVencimento,
        $idFinanceiro
    ]);

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    redirecionar(
        "financeiro",
        ["sucesso" => "editado"]
    );

} catch (PDOException $e) {

    redirecionar(
        "financeiro",
        ["erro" => "banco"]
    );
}