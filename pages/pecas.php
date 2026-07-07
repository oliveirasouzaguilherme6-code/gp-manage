<?php

require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

$sql = $conn->query("
    SELECT *
    FROM pecas
    ORDER BY peca ASC
");

$pecas = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold">Peças</h2>
        <?php

$totalPecas = $conn->query("
SELECT COUNT(*) FROM pecas
")->fetchColumn();

$estoqueBaixo = $conn->query("
SELECT COUNT(*)
FROM pecas
WHERE estoque <= estoque_minimo
")->fetchColumn();

$valorCompra = $conn->query("
SELECT SUM(preco_compra * estoque)
FROM pecas
")->fetchColumn();

$valorVenda = $conn->query("
SELECT SUM(venda * estoque)
FROM pecas
")->fetchColumn();

?>

<div class="row mb-4">

<div class="col-md-3">

<div class="card-dashboard p-3 bg-white">

<h6>Total de Peças</h6>

<h2>

<?= $totalPecas ?>

</h2>

</div>

</div>

<div class="col-md-3">

<div class="card-dashboard p-3 bg-white">

<h6>Estoque Baixo</h6>

<h2 class="text-danger">

<?= $estoqueBaixo ?>

</h2>

</div>

</div>

<div class="col-md-3">

<div class="card-dashboard p-3 bg-white">

<h6>Valor Compra</h6>

<h4>

R$

<?= number_format($valorCompra,2,",",".") ?>

</h4>

</div>

</div>

<div class="col-md-3">

<div class="card-dashboard p-3 bg-white">

<h6>Valor Venda</h6>

<h4>

R$

<?= number_format($valorVenda,2,",",".") ?>

</h4>

</div>

</div>

</div>
        <small class="text-muted">Controle de Estoque</small>
    </div>

    <button
        class="btn btn-warning"
        data-bs-toggle="modal"
        data-bs-target="#novaPeca">

        <i class="bi bi-plus-circle"></i>
        Nova Peça

    </button>

</div>

<div class="card-dashboard bg-white p-4">

    <input
        type="text"
id="pesquisaPeca"
class="form-control mb-4"
placeholder="Pesquisar peça...">

<div class="d-flex gap-2 mb-3">

<button
class="btn btn-outline-primary btn-sm"
id="btnTodas">

Todas

</button>

<button
class="btn btn-outline-danger btn-sm"
id="btnBaixo">

Estoque Baixo

</button>

</div>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead class="table-light">

<tr>

<th onclick="ordenarTabela(0)" style="cursor:pointer">Foto</th>

<th onclick="ordenarTabela(1)" style="cursor:pointer">Código ⬍</th>

<th onclick="ordenarTabela(2)" style="cursor:pointer">Peça ⬍</th>

<th onclick="ordenarTabela(3)" style="cursor:pointer">Marca ⬍</th>

<th onclick="ordenarTabela(4)" style="cursor:pointer">Estoque ⬍</th>

<th onclick="ordenarTabela(5)" style="cursor:pointer">Venda ⬍</th>

<th>Ações</th>

</tr>

</thead>

            <tbody>

            <?php foreach($pecas as $peca): ?>

                <tr data-baixo="<?= ($peca['estoque'] <= $peca['estoque_minimo']) ? 1 : 0 ?>">

                    <td>

                        <?php if(!empty($peca['foto'])): ?>

                            <img
                                src="uploads/pecas/<?= $peca['foto']; ?>"
                                style="width:60px;height:60px;object-fit:cover;border-radius:10px;">

                        <?php else: ?>

                            <img
                                src="https://placehold.co/60x60"
                                style="border-radius:10px;">

                        <?php endif; ?>

                    </td>

                    <td><?= htmlspecialchars($peca['codigo']); ?></td>

                    <td>

                        <strong>

                            <?= htmlspecialchars($peca['peca']); ?>

                        </strong>

                    </td>

                    <td><?= htmlspecialchars($peca['marca']); ?></td>

                    <td>

                        <?php if($peca['estoque'] <= $peca['estoque_minimo']){ ?>

                            <span class="badge bg-danger">

                                <?= $peca['estoque']; ?>

                            </span>

                        <?php }else{ ?>

                            <span class="badge bg-success">

                                <?= $peca['estoque']; ?>

                            </span>

                        <?php } ?>

                    </td>

                    <td>

                        R$

                        <?= number_format($peca['venda'],2,",","."); ?>

                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm editarPeca"
                            data-id="<?= $peca['id_peca']; ?>"
                            title="Editar">

                            <i class="bi bi-pencil-square"></i>

                        </button>

                        <button
                            type="button"
                            class="btn btn-success btn-sm movimentarPeca"
                            data-id="<?= $peca['id_peca']; ?>"
                            title="Movimentar">

                            <i class="bi bi-arrow-left-right"></i>

                        </button>

                        <a
                            href="actions/excluir_peca.php?id=<?= $peca['id_peca']; ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Deseja excluir esta peça?')">

                            <i class="bi bi-trash"></i>

                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- Modal -->

<div class="modal fade" id="novaPeca" tabindex="-1">

<div class="modal-dialog modal-xl">

<div class="modal-content">

<form
id="formPeca"
action="actions/salvar_peca.php"
method="POST"
enctype="multipart/form-data">

<input type="hidden" name="id" id="id">
<input type="hidden" name="foto_atual" id="foto_atual">

<div class="modal-header">

<h5 id="tituloModalPeca">

Nova Peça

</h5>

<button
type="button"
class="btn-close"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<div class="row">

<div class="col-md-4">

<label class="form-label">

Código

</label>

<input
type="text"
name="codigo"
id="codigo"
class="form-control"
required>

</div>

<div class="col-md-8">

<label class="form-label">

Nome da Peça

</label>

<input
type="text"
name="peca"
id="peca"
class="form-control"
required>

</div>

<div class="col-md-6 mt-3">

<label class="form-label">

Marca

</label>

<input
type="text"
name="marca"
id="marca"
class="form-control">

</div>

<div class="col-md-6 mt-3">

<label class="form-label">

Fabricante

</label>

<input
type="text"
name="fabricante"
id="fabricante"
class="form-control">

</div>

<div class="col-md-4 mt-3">

<label class="form-label">

Estoque

</label>

<input
type="number"
name="estoque"
id="estoque"
class="form-control"
value="0">

</div>

<div class="col-md-4 mt-3">

<label class="form-label">

Estoque Mínimo

</label>

<input
type="number"
name="estoque_minimo"
id="estoque_minimo"
class="form-control"
value="1">

</div>

<div class="col-md-4 mt-3">

<label class="form-label">

Código de Barras

</label>

<input
type="text"
name="codigo_barras"
id="codigo_barras"
class="form-control">

</div>

<div class="col-md-6 mt-3">

<label class="form-label">

Preço Compra

</label>

<input
type="number"
step="0.01"
name="preco_compra"
id="preco_compra"
class="form-control">

</div>

<div class="col-md-6 mt-3">

<label class="form-label">

Preço Venda

</label>

<input
type="number"
step="0.01"
name="venda"
id="venda"
class="form-control">

</div>

<div class="col-md-6 mt-3">

<label class="form-label">

Localização

</label>

<input
type="text"
name="localizacao"
id="localizacao"
class="form-control">

</div>

<div class="col-md-6 mt-3">

<label class="form-label">

Foto

</label>

<input
type="file"
name="foto"
id="foto"
accept=".jpg,.jpeg,.png,.webp"
class="form-control">

<div class="mt-3 text-center">

<img
id="previewFoto"
src="https://placehold.co/220x220?text=Foto"
style="
width:220px;
height:220px;
object-fit:cover;
border-radius:12px;
border:1px solid #ddd;
">

</div>
</div>

<div class="col-12 mt-3">

<label class="form-label">

Descrição

</label>

<textarea
name="descricao"
id="descricao"
rows="4"
class="form-control"></textarea>

</div>

</div>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancelar

</button>

<button
type="submit"
class="btn btn-warning">

<i class="bi bi-check-circle"></i>

Salvar Peça

</button>

</div>

</form>

</div>

</div>

</div>

<script>

const modalPeca = new bootstrap.Modal(document.getElementById("novaPeca"));

document.getElementById("pesquisaPeca").addEventListener("keyup", function(){

    let texto = this.value.toLowerCase();

    document.querySelectorAll("tbody tr").forEach(function(linha){

        linha.style.display = linha.innerText.toLowerCase().includes(texto)

        ? ""

        : "none";

    });

});

document.querySelectorAll(".editarPeca").forEach(function(botao){

    botao.addEventListener("click", function(){

        fetch("actions/buscar_peca.php?id=" + this.dataset.id)

        .then(response => response.json())

        .then(peca => {

            document.getElementById("tituloModalPeca").innerHTML="Editar Peça";

            document.getElementById("formPeca").action="actions/editar_peca.php";

            document.getElementById("id").value=peca.id_peca;

            document.getElementById("foto_atual").value=peca.foto;

            document.getElementById("codigo").value=peca.codigo;

            document.getElementById("peca").value=peca.peca;

            document.getElementById("marca").value=peca.marca;

            document.getElementById("fabricante").value=peca.fabricante;

            document.getElementById("codigo_barras").value=peca.codigo_barras;

            document.getElementById("estoque").value=peca.estoque;

            document.getElementById("estoque_minimo").value=peca.estoque_minimo;

            document.getElementById("preco_compra").value=peca.preco_compra;

            document.getElementById("venda").value=peca.venda;

            document.getElementById("localizacao").value=peca.localizacao;

            document.getElementById("descricao").value=peca.descricao;

if(peca.foto!=""){

document.getElementById("previewFoto").src="uploads/pecas/"+peca.foto;

}else{

document.getElementById("previewFoto").src="https://placehold.co/220x220?text=Foto";

}

            modalPeca.show();

        });

    });

});

document.getElementById("novaPeca").addEventListener("hidden.bs.modal", function(){

    document.getElementById("formPeca").reset();
    document.getElementById("previewFoto").src="https://placehold.co/220x220?text=Foto";

    document.getElementById("id").value="";

    document.getElementById("foto_atual").value="";

    document.getElementById("tituloModalPeca").innerHTML="Nova Peça";

    document.getElementById("formPeca").action="actions/salvar_peca.php";

});



document.getElementById("foto").addEventListener("change",function(){

    const arquivo=this.files[0];

    if(!arquivo) return;

    const leitor=new FileReader();

    leitor.onload=function(e){

        document.getElementById("previewFoto").src=e.target.result;

    }

    leitor.readAsDataURL(arquivo);

});


document.getElementById("btnBaixo").addEventListener("click",function(){

document.querySelectorAll("tbody tr").forEach(function(linha){

linha.style.display=

linha.dataset.baixo=="1"

? ""

: "none";

});

});

document.getElementById("btnTodas").addEventListener("click",function(){

document.querySelectorAll("tbody tr").forEach(function(linha){

linha.style.display="";

});

});

function ordenarTabela(coluna){

const tabela=document.querySelector("table tbody");

const linhas=Array.from(tabela.querySelectorAll("tr"));

linhas.sort(function(a,b){

let x=a.children[coluna].innerText.toLowerCase();

let y=b.children[coluna].innerText.toLowerCase();

return x.localeCompare(y,'pt-BR',{numeric:true});

});

linhas.forEach(function(linha){

tabela.appendChild(linha);

});

}

</script>