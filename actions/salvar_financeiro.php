<?php

/*
|--------------------------------------------------------------------------
| GP MANAGER - SALVAR LANÇAMENTO FINANCEIRO
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

$tipo = trim((string)($_POST["tipo"] ?? ""));
$descricao = trim((string)($_POST["descricao"] ?? ""));
$valor = $_POST["valor"] ?? "";
$dataVencimento = trim((string)($_POST["data_vencimento"] ?? ""));
$formaPagamento = trim((string)($_POST["forma_pagamento"] ?? ""));
$observacoes = trim((string)($_POST["observacoes"] ?? ""));

/*
|--------------------------------------------------------------------------
| VALIDAR CAMPOS OBRIGATÓRIOS
|--------------------------------------------------------------------------
*/

if (
    $tipo === "" ||
    $descricao === "" ||
    $valor === ""
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
| FORMAS DE PAGAMENTO
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
| CONEXÃO
|--------------------------------------------------------------------------
*/

try {

    $db = new Database();

    $conn = $db->connect();

    /*
    |--------------------------------------------------------------------------
    | INSERIR LANÇAMENTO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        INSERT INTO financeiro
        (
            tipo,
            descricao,
            valor,
            data_vencimento,
            forma_pagamento,
            observacoes,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'Pendente'
        )
    ");

    $sql->execute([
        $tipo,
        $descricao,
        $valor,
        $dataVencimento,
        $formaPagamento,
        $observacoes
    ]);

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    redirecionar(
        "financeiro",
        ["sucesso" => "cadastrado"]
    );

} catch (PDOException $e) {

    redirecionar(
        "financeiro",
        ["erro" => "banco"]
    );
}