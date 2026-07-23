<?php

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

/* Gera número da O.S. */

$ultimo = $conn->query("
SELECT id_os
FROM ordens_servico
ORDER BY id_os DESC
LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$numero = 1;

if($ultimo){
    $numero = $ultimo['id_os'] + 1;
}

$numeroOS = "OS".str_pad($numero,6,"0",STR_PAD_LEFT);

/* Valores */

$valorPecas = isset($_POST['valor_pecas']) ? floatval($_POST['valor_pecas']) : 0;

$valorMaoObra = isset($_POST['valor_mao_obra']) ? floatval($_POST['valor_mao_obra']) : 0;

$valorTotal = isset($_POST['valor_total']) ? floatval($_POST['valor_total']) : ($valorPecas + $valorMaoObra);

/* Salva Ordem */

$sql = $conn->prepare("

INSERT INTO ordens_servico(

numero_os,
id_cliente,
id_veiculo,
entrada,
previsao,
status,
valor_pecas,
valor_mao_obra,
valor_total,
observacoes

)

VALUES(

?,?,?,?,?,?,?,?,?,?

)

");

$sql->execute([

$numeroOS,
$_POST['id_cliente'],
$_POST['id_veiculo'],
$_POST['entrada'],
$_POST['previsao'],
$_POST['status'],
$valorPecas,
$valorMaoObra,
$valorTotal,
$_POST['observacoes']

]);

$idOS = $conn->lastInsertId();




/* Histórico */

$sqlHistorico = $conn->prepare("
INSERT INTO historico_os
(
    id_os,
    descricao,
    usuario
)
VALUES
(
    ?,?,?
)
");

$sqlHistorico->execute([

    $idOS,

    'Ordem de Serviço criada no sistema.',

    'Administrador'

]);

/* Salvar peças da O.S. */

if(isset($_POST['peca'])){

    $sqlItem = $conn->prepare("
    INSERT INTO os_pecas
    (
        id_os,
        id_peca,
        quantidade
    )
    VALUES
    (
        ?,?,?
    )
    ");

    foreach($_POST['peca'] as $i => $peca){

        if(empty($peca)){
            continue;
        }

        $sqlItem->execute([

            $idOS,
            $peca,
            $_POST['quantidade'][$i]

        ]);

    }

}


/* Salvar serviços da O.S. */

if(isset($_POST['descricao_servico'])){

    $sqlServico = $conn->prepare("
        INSERT INTO os_servicos
        (
            id_os,
            descricao,
            funcionario,
            horas,
            valor,
            status
        )
        VALUES
        (
            ?,?,?,?,?,?
        )
    ");

    foreach($_POST['descricao_servico'] as $i => $descricao){

        if(trim($descricao) == ''){
            continue;
        }

        $sqlServico->execute([

            $idOS,

            $descricao,

            $_POST['funcionario'][$i],

            $_POST['horas'][$i],

            $_POST['valor_servico'][$i],

            $_POST['status_servico'][$i]

        ]);

    }

}

header("Location: ../index.php?page=ordens_servico");

exit;