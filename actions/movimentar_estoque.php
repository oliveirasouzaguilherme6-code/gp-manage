<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| ACEITAR SOMENTE POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php?page=pecas");
    exit;
}

/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    "id_peca",
    FILTER_VALIDATE_INT
);

$tipo = trim($_POST["tipo"] ?? "");

$quantidade = filter_input(
    INPUT_POST,
    "quantidade",
    FILTER_VALIDATE_INT
);

$observacao = trim(
    $_POST["observacao"] ?? ""
);

/*
|--------------------------------------------------------------------------
| VALIDAR DADOS
|--------------------------------------------------------------------------
*/

if (!$id || $id <= 0) {

    header(
        "Location: ../index.php?page=pecas&erro=peca_invalida"
    );

    exit;
}

if (
    !$quantidade ||
    $quantidade <= 0
) {

    header(
        "Location: ../index.php?page=pecas&erro=quantidade"
    );

    exit;
}

$tiposPermitidos = [
    "Entrada",
    "Saida"
];

if (!in_array(
    $tipo,
    $tiposPermitidos,
    true
)) {

    header(
        "Location: ../index.php?page=pecas&erro=tipo"
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| TRANSAÇÃO
|--------------------------------------------------------------------------
*/

try {

    $conn->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | BUSCAR PEÇA E BLOQUEAR REGISTRO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT
            id_peca,
            peca,
            estoque
        FROM pecas
        WHERE id_peca = ?
        FOR UPDATE
    ");

    $sql->execute([$id]);

    $peca = $sql->fetch(
        PDO::FETCH_ASSOC
    );

    /*
    |--------------------------------------------------------------------------
    | PEÇA NÃO ENCONTRADA
    |--------------------------------------------------------------------------
    */

    if (!$peca) {

        $conn->rollBack();

        header(
            "Location: ../index.php?page=pecas&erro=nao_encontrada"
        );

        exit;
    }

    $estoqueAtual = (int)$peca["estoque"];

    /*
    |--------------------------------------------------------------------------
    | ENTRADA
    |--------------------------------------------------------------------------
    */

    if ($tipo === "Entrada") {

        $novoEstoque =
            $estoqueAtual + $quantidade;

    }

    /*
    |--------------------------------------------------------------------------
    | SAÍDA
    |--------------------------------------------------------------------------
    */

    else {

        if ($estoqueAtual < $quantidade) {

            $conn->rollBack();

            header(
                "Location: ../index.php?page=pecas&erro=estoque_insuficiente"
            );

            exit;
        }

        $novoEstoque =
            $estoqueAtual - $quantidade;
    }

    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR ESTOQUE
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        UPDATE pecas
        SET estoque = ?
        WHERE id_peca = ?
    ");

    $sql->execute([
        $novoEstoque,
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | REGISTRAR MOVIMENTAÇÃO
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        INSERT INTO movimentacoes_estoque
        (
            id_peca,
            tipo,
            quantidade,
            observacao
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ");

    $sql->execute([
        $id,
        $tipo,
        $quantidade,
        $observacao !== ""
            ? $observacao
            : null
    ]);

    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    header(
        "Location: ../index.php?page=pecas&sucesso=movimentacao"
    );

    exit;

} catch (PDOException $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header(
        "Location: ../index.php?page=pecas&erro=movimentacao"
    );

    exit;
}