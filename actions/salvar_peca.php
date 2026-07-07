<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$foto = "";

if(isset($_FILES["foto"]) && $_FILES["foto"]["error"] == 0){

    $foto = uniqid()."_".$_FILES["foto"]["name"];

    move_uploaded_file(

        $_FILES["foto"]["tmp_name"],

        "../uploads/pecas/".$foto

    );

}

$sql = $conn->prepare("

INSERT INTO pecas(

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

)

VALUES(

?,?,?,?,?,?,?,?,?,?,?,?

)

");

$sql->execute([

$_POST["codigo"],
$_POST["peca"],
$_POST["marca"],
$_POST["fabricante"],
$_POST["codigo_barras"],
$_POST["estoque"],
$_POST["estoque_minimo"],
$_POST["preco_compra"],
$_POST["venda"],
$_POST["localizacao"],
$_POST["descricao"],
$foto

]);

header("Location: ../index.php?page=pecas");

exit;