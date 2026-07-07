<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$id = $_POST['id_veiculo'];

if(isset($_FILES['foto']) && $_FILES['foto']['error']==0){

    $ext = pathinfo($_FILES['foto']['name'],PATHINFO_EXTENSION);

    $foto = uniqid().".".$ext;

    move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        "../uploads/veiculos/".$foto
    );

    $sql=$conn->prepare("
    UPDATE veiculos SET

    id_cliente=?,
    marca=?,
    modelo=?,
    ano=?,
    placa=?,
    cor=?,
    km=?,
    foto=?,
    chassi=?,
    renavam=?,
    combustivel=?,
    observacoes=?

    WHERE id_veiculo=?

    ");

    $sql->execute([

    $_POST['id_cliente'],
    $_POST['marca'],
    $_POST['modelo'],
    $_POST['ano'],
    $_POST['placa'],
    $_POST['cor'],
    $_POST['km'],
    $foto,
    $_POST['chassi'],
    $_POST['renavam'],
    $_POST['combustivel'],
    $_POST['observacoes'],
    $id

    ]);

}else{

    $sql=$conn->prepare("
    UPDATE veiculos SET

    id_cliente=?,
    marca=?,
    modelo=?,
    ano=?,
    placa=?,
    cor=?,
    km=?,
    chassi=?,
    renavam=?,
    combustivel=?,
    observacoes=?

    WHERE id_veiculo=?

    ");

    $sql->execute([

    $_POST['id_cliente'],
    $_POST['marca'],
    $_POST['modelo'],
    $_POST['ano'],
    $_POST['placa'],
    $_POST['cor'],
    $_POST['km'],
    $_POST['chassi'],
    $_POST['renavam'],
    $_POST['combustivel'],
    $_POST['observacoes'],
    $id

    ]);

}

header("Location: ../index.php?page=veiculos");
exit;