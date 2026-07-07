<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$id = $_GET["id"];

$sql = $conn->prepare("

SELECT *

FROM os_fotos

WHERE id_foto=?

");

$sql->execute([$id]);

$foto = $sql->fetch(PDO::FETCH_ASSOC);

@unlink("../uploads/os/".$foto["foto"]);

$conn->prepare("

DELETE FROM os_fotos

WHERE id_foto=?

")->execute([$id]);

header("Location: ../index.php?page=ver_os&id=".$foto["id_os"]);

exit;