<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

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

    header(
        "Location: ../index.php?page=pecas&erro=id_invalido"
    );

    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | BUSCAR PEÇA
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT
            id_peca,
            peca,
            foto
        FROM pecas
        WHERE id_peca = ?
        LIMIT 1
    ");

    $sql->execute([$id]);

    $peca = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$peca) {

        header(
            "Location: ../index.php?page=pecas&erro=nao_encontrada"
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE A PEÇA ESTÁ EM ALGUMA O.S.
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        SELECT COUNT(*)
        FROM os_pecas
        WHERE id_peca = ?
    ");

    $sql->execute([$id]);

    $vinculosOS = (int)$sql->fetchColumn();

    if ($vinculosOS > 0) {

        header(
            "Location: ../index.php?page=pecas&erro=peca_em_os"
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | INICIAR TRANSAÇÃO
    |--------------------------------------------------------------------------
    */

    $conn->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR MOVIMENTAÇÕES DA PEÇA
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        DELETE FROM movimentacoes_estoque
        WHERE id_peca = ?
    ");

    $sql->execute([$id]);

    /*
    |--------------------------------------------------------------------------
    | EXCLUIR PEÇA
    |--------------------------------------------------------------------------
    */

    $sql = $conn->prepare("
        DELETE FROM pecas
        WHERE id_peca = ?
    ");

    $sql->execute([$id]);

    if ($sql->rowCount() === 0) {

        $conn->rollBack();

        header(
            "Location: ../index.php?page=pecas&erro=exclusao"
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR BANCO
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    /*
    |--------------------------------------------------------------------------
    | APAGAR FOTO SOMENTE APÓS EXCLUSÃO
    |--------------------------------------------------------------------------
    */

    if (!empty($peca["foto"])) {

        $arquivo = "../uploads/pecas/" .
            basename($peca["foto"]);

        if (is_file($arquivo)) {
            unlink($arquivo);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SUCESSO
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../index.php?page=pecas&sucesso=excluida"
    );

    exit;

} catch (PDOException $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header(
        "Location: ../index.php?page=pecas&erro=exclusao"
    );

    exit;
}