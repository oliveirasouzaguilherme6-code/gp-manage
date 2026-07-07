<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$id = $_POST['id_cliente'];

$foto = "";

if(isset($_FILES['foto']) && $_FILES['foto']['error']==0){

    $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);

    $nomeFoto = uniqid().".".$extensao;

    move_uploaded_file(

        $_FILES['foto']['tmp_name'],

        "../uploads/clientes/".$nomeFoto

    );

    $foto = ", foto='".$nomeFoto."'";

}

$sql = $conn->prepare("

UPDATE clientes

SET

nome=?,

telefone=?,

email=?,

cpf=?,

cidade=?,

observacoes=?

".$foto."

WHERE id_cliente=?

");

$sql->execute([

$_POST['nome'],

$_POST['telefone'],

$_POST['email'],

$_POST['cpf'],

$_POST['cidade'],

$_POST['observacoes'],

$id

]);

header("Location: ../index.php?page=clientes");
exit;