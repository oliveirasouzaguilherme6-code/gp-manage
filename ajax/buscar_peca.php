<?php

require_once "../config/database.php";

header("Content-Type: application/json; charset=UTF-8");

try {

    /*
    |--------------------------------------------------------------------------
    | VALIDAR ID
    |--------------------------------------------------------------------------
    */

    $id = filter_input(
        INPUT_GET,
        "id",
        FILTER_VALIDATE_INT
    );

    if (!$id || $id <= 0) {

        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "ID da peça inválido."
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CONEXÃO
    |--------------------------------------------------------------------------
    */

    $db = new Database();
    $conn = $db->connect();

    /*
    |--------------------------------------------------------------------------
    | BUSCAR PEÇA
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT
            id_peca,
            codigo,
            peca,
            marca,
            fabricante,
            codigo_barras,
            estoque,
            estoque_minimo,
            preco_compra,
            venda,
            localizacao,
            descricao,
            foto
        FROM pecas
        WHERE id_peca = ?
        LIMIT 1
    ");

    $sql->execute([$id]);

    $peca = $sql->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | PEÇA NÃO ENCONTRADA
    |--------------------------------------------------------------------------
    */

    if (!$peca) {

        http_response_code(404);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Peça não encontrada."
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | RETORNO
    |--------------------------------------------------------------------------
    */

    echo json_encode(
        $peca,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao consultar a peça."
    ]);

    exit;

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno do sistema."
    ]);

    exit;
}