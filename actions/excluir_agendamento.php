<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

if (!isset($_GET['id'])) {
    header("Location: ../index.php?page=agenda");
    exit;
}

$sql = $conn->prepare("
DELETE FROM agenda
WHERE id_agenda = ?
");

$sql->execute([
    $_GET['id']
]);

header("Location: ../index.php?page=agenda");
exit;