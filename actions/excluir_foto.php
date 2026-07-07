<?php

require_once "../config/database.php";

$db=new Database();
$conn=$db->connect();

$sql=$conn->prepare("
SELECT arquivo
FROM anexos
WHERE id_anexo=?
");

$sql->execute([$_GET['id']]);

$foto=$sql->fetch();

unlink("../uploads/os/".$foto['arquivo']);

$conn->prepare("
DELETE FROM anexos
WHERE id_anexo=?
")->execute([$_GET['id']]);

header("Location: ../index.php?page=os_detalhes&id=".$_GET['os']);
exit;