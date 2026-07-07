<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql=$conn->prepare("SELECT foto FROM pecas WHERE id_peca=?");
$sql->execute([$_GET['id']]);

$peca=$sql->fetch(PDO::FETCH_ASSOC);

if($peca){

    if($peca['foto']!=""){

        $arquivo="../uploads/pecas/".$peca['foto'];

        if(file_exists($arquivo)){

            unlink($arquivo);

        }

    }

}

$conn->prepare("DELETE FROM pecas WHERE id_peca=?")
->execute([$_GET['id']]);

header("Location: ../index.php?page=pecas");
exit;