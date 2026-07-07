<?php

require_once "../config/database.php";

$db=new Database();
$conn=$db->connect();

$id_os=$_POST['id_os'];
$etapa=$_POST['etapa'];

$nome=uniqid()."_".$_FILES['foto']['name'];

move_uploaded_file(

$_FILES['foto']['tmp_name'],

"../uploads/os/".$nome

);

$sql=$conn->prepare("
INSERT INTO anexos
(
id_os,
tipo,
categoria,
arquivo,
etapa
)
VALUES
(
?,
'Foto',
?,
?,
?
)
");

$sql->execute([

$id_os,

$etapa,

$nome,

$etapa

]);

header("Location: ../index.php?page=os_detalhes&id=".$id_os);

<form

action="actions/upload_foto_os.php"

method="POST"

enctype="multipart/form-data">

<input
type="hidden"
name="id_os"
value="<?=$id?>">

<label>Etapa</label>

<select
name="etapa"
class="form-select">

<option>Antes</option>

<option>Durante</option>

<option>Depois</option>

</select>

<input

type="file"

name="foto"

class="form-control mt-3"

required>

<button

class="btn btn-warning mt-3">

Enviar Foto

</button>

</form>

<?php

$sql=$conn->prepare("
SELECT *
FROM anexos
WHERE id_os=?
ORDER BY id_anexo DESC
");

$sql->execute([$id]);

?>

<div class="row mt-4">

<?php foreach($sql as $foto): ?>

<div class="col-md-3 mb-3">

<div class="card">

<img

src="uploads/os/<?=$foto['arquivo']?>"

class="card-img-top"

style="height:180px;object-fit:cover;">

<div class="card-body">

<b><?=$foto['categoria']?></b>

<br>

<?=$foto['etapa']?>

<br><br>

<a

href="actions/excluir_foto.php?id=<?=$foto['id_anexo']?>&os=<?=$id?>"

class="btn btn-danger btn-sm w-100">

Excluir

</a>

</div>

</div>

</div>

<?php endforeach; ?>

</div>