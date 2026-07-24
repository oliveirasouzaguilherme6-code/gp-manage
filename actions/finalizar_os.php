<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

/* =========================================
   VALIDAR ID DA O.S.
========================================= */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: ../index.php?page=os&erro=os_invalida");
    exit;
}

try {

    /* =========================================
       INICIAR TRANSAÇÃO
    ========================================= */

    $conn->beginTransaction();

    /* =========================================
       VERIFICAR SE A O.S. EXISTE
    ========================================= */

    $sql = $conn->prepare("
        SELECT id_os, status
        FROM ordens_servico
        WHERE id_os = ?
        LIMIT 1
    ");

    $sql->execute([$id]);

    $os = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$os) {

        $conn->rollBack();

        header("Location: ../index.php?page=os&erro=os_nao_encontrada");
        exit;
    }

    /* =========================================
       IMPEDIR FINALIZAÇÃO DUPLICADA
    ========================================= */

    if ($os['status'] === 'Finalizado') {

        $conn->rollBack();

        header(
            "Location: ../index.php?page=ver_os&id=" .
            $id .
            "&aviso=ja_finalizada"
        );

        exit;
    }

    /* =========================================
       FINALIZAR O.S.
    ========================================= */

    $sql = $conn->prepare("
        UPDATE ordens_servico
        SET status = 'Finalizado'
        WHERE id_os = ?
    ");

    $sql->execute([$id]);

    /* =========================================
       REGISTRAR NO HISTÓRICO
    ========================================= */

    $sql = $conn->prepare("
        INSERT INTO historico_os
        (
            id_os,
            descricao,
            usuario
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $sql->execute([
        $id,
        'Ordem de Serviço finalizada.',
        'Administrador'
    ]);

    /* =========================================
       CONFIRMAR ALTERAÇÕES
    ========================================= */

    $conn->commit();

    /* =========================================
       VOLTAR PARA A O.S.
    ========================================= */

    header(
        "Location: ../index.php?page=ver_os&id=" .
        $id .
        "&sucesso=finalizada"
    );

    exit;

} catch (PDOException $e) {

    /* =========================================
       DESFAZER ALTERAÇÕES EM CASO DE ERRO
    ========================================= */

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die(
        "Erro ao finalizar a Ordem de Serviço: " .
        htmlspecialchars($e->getMessage())
    );
}