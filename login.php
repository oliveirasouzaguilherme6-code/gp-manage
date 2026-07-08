<?php
session_start();

if(isset($_SESSION['usuario'])){
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - GP Manager</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{

background:#111827;

height:100vh;

display:flex;

justify-content:center;

align-items:center;

font-family:Poppins,sans-serif;

}

.card-login{

width:420px;

border:none;

border-radius:20px;

padding:40px;

box-shadow:0 20px 60px rgba(0,0,0,.35);

}

.logo{

font-size:38px;

font-weight:bold;

color:#F97316;

text-align:center;

margin-bottom:10px;

}

.sub{

text-align:center;

color:#666;

margin-bottom:30px;

}

</style>

</head>

<body>

<div class="card card-login">

<div class="logo">

GP Manager

</div>

<p class="sub">

Faça login para continuar

</p>

<form action="actions/login.php" method="POST">

<div class="mb-3">

<label>Email</label>

<input

type="email"

name="email"

class="form-control"

required>

</div>

<div class="mb-4">

<label>Senha</label>

<input

type="password"

name="senha"

class="form-control"

required>

</div>

<button

class="btn btn-warning w-100">

<i class="bi bi-box-arrow-in-right"></i>

Entrar

</button>

</form>

</div>

</body>

</html>