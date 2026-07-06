<?php

$page = $_GET['page'] ?? 'dashboard';

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

$pageFile = "pages/{$page}.php";

if(file_exists($pageFile)){
    include $pageFile;
}else{
    include "pages/dashboard.php";
}

include 'includes/footer.php';