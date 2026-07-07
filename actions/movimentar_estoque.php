<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$id = $_POST['id_peca'];
$tipo = $_POST['tipo'];
$qtd = (int)$_POST['quantidade'];
$obs = $_POST['observacao'];

$conn->beginTransaction();

if($tipo == "Entrada"){

    $conn->prepare("
    UPDATE pecas
    SET estoque = estoque + ?
    WHERE id_peca = ?
    ")->execute([$qtd,$id]);

}else{

    $estoque = $conn->prepare("
    SELECT estoque
    FROM pecas
    WHERE id_peca=?
    ");

    $estoque->execute([$id]);

    $atual = $estoque->fetch(PDO::FETCH_ASSOC);

    if($atual['estoque'] < $qtd){

        die("Estoque insuficiente.");

    }

    $conn->prepare("
    UPDATE pecas
    SET estoque = estoque - ?
    WHERE id_peca = ?
    ")->execute([$qtd,$id]);

}

$conn->prepare("
INSERT INTO movimentacoes_estoque
(id_peca,tipo,quantidade,observacao)
VALUES (?,?,?,?)
")->execute([

$id,
$tipo,
$qtd,
$obs

]);

$conn->commit();

header("Location: ../index.php?page=pecas");
exit;