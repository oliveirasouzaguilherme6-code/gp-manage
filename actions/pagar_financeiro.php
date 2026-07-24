<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

/* Verifica ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php?page=financeiro&erro=1");
    exit;
}

$id = (int) $_GET['id'];

try {

    /* Verifica se lançamento existe */
    $sql = $conn->prepare("
        SELECT id_financeiro, status
        FROM financeiro
        WHERE id_financeiro = ?
    ");

    $sql->execute([$id]);

    $financeiro = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$financeiro) {
        header("Location: ../index.php?page=financeiro&erro=1");
        exit;
    }

    /* Marca como pago */
    $sql = $conn->prepare("
        UPDATE financeiro
        SET
            status = 'Pago',
            data_pagamento = CURDATE()
        WHERE id_financeiro = ?
    ");

    $sql->execute([$id]);

    header("Location: ../index.php?page=financeiro&sucesso=1");
    exit;

} catch (PDOException $e) {

    header("Location: ../index.php?page=financeiro&erro=1");
    exit;
}