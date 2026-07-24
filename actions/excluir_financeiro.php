<?php

/*
|--------------------------------------------------------------------------
| GP MANAGER - EXCLUIR LANÇAMENTO FINANCEIRO
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
| RECEBER ID
|--------------------------------------------------------------------------
*/

$idFinanceiro = filter_input(
    INPUT_POST,
    "id_financeiro",
    FILTER_VALIDATE_INT
);

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
| CONEXÃO
|--------------------------------------------------------------------------
*/

try {

    $db = new Database();
    $conn = $db->connect();

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE EXISTE
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT
            id_financeiro,
            descricao,
            status
        FROM financeiro
        WHERE id_financeiro = ?
        LIMIT 1
    ");

    $sql->execute([
        $idFinanceiro
    ]);

    $lancamento = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$lancamento) {

        redirecionar(
            "financeiro",
            ["erro" => "lancamento"]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        DELETE FROM financeiro
        WHERE id_financeiro = ?
    ");

    $sql->execute([
        $idFinanceiro
    ]);

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    redirecionar(
        "financeiro",
        ["sucesso" => "excluido"]
    );

} catch (PDOException $e) {

    redirecionar(
        "financeiro",
        ["erro" => "banco"]
    );
}