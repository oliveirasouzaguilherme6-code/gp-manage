<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$conn->prepare("
DELETE FROM historico_os
WHERE id_os=?
")->execute([$_GET['id']]);

$conn->prepare("
DELETE FROM os_servicos
WHERE id_os=?
")->execute([$_GET['id']]);

$conn->prepare("
DELETE FROM os_pecas
WHERE id_os=?
")->execute([$_GET['id']]);

$conn->prepare("
DELETE FROM ordens_servico
WHERE id_os=?
")->execute([$_GET['id']]);

header("Location: ../index.php?page=os");
exit;