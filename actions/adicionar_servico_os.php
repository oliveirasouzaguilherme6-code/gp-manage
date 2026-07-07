<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->prepare("

INSERT INTO os_servicos(

id_os,
servico,
quantidade,
valor_unitario,
valor_total

)

VALUES(

?,?,?,?,?

)

");

$total = $_POST["quantidade"] * $_POST["valor_unitario"];

$sql->execute([

$_POST["id_os"],
$_POST["servico"],
$_POST["quantidade"],
$_POST["valor_unitario"],
$total

]);

$conn->prepare("

UPDATE ordens_servico

SET valor_servicos=(

SELECT IFNULL(SUM(valor_total),0)

FROM os_servicos

WHERE id_os=?

)

WHERE id_os=?

")->execute([

$_POST["id_os"],
$_POST["id_os"]

]);

$conn->prepare("

UPDATE ordens_servico

SET valor_total=

valor_pecas+valor_servicos

WHERE id_os=?

")->execute([

$_POST["id_os"]

]);

header("Location: ../index.php?page=ver_os&id=".$_POST["id_os"]);

exit;