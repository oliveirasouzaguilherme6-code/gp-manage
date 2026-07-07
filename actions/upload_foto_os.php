<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$idOS = $_POST["id_os"];

$descricao = $_POST["descricao"];

$nomeFoto = "";

if(isset($_FILES["foto"]) && $_FILES["foto"]["error"] == 0){

    $ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);

    $nomeFoto = uniqid().".".$ext;

    move_uploaded_file(

        $_FILES["foto"]["tmp_name"],

        "../uploads/os/".$nomeFoto

    );

}

$sql = $conn->prepare("

INSERT INTO os_fotos(

id_os,
foto,
descricao

)

VALUES(

?,?,?

)

");

$sql->execute([

$idOS,
$nomeFoto,
$descricao

]);

header("Location: ../index.php?page=ver_os&id=".$idOS);

exit;