<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$id = intval($_GET['id']);

$sql = $conn->prepare("
DELETE FROM financeiro
WHERE id_financeiro=?
");

$sql->execute([$id]);

header("Location: ../index.php?page=financeiro");
exit;