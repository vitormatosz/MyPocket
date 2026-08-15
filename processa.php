<?php
declare(strict_types=1);

require_once "classes/Transacao.php";
require_once "classes/Receita.php";
require_once "classes/Despesa.php";
require_once "classes/Carteira.php";

session_start();

require_once 'database/conexao.php';

$carteira = new Carteira();

$stmt = $pdo->query("SELECT * FROM transacoes");

foreach ($stmt as $row) {

    if ($row['tipo'] === "Entrada") {
        $t = new Receita(
            (float) $row['valor'],
            $row['descricao'],
            $row['data']
        );
    } else {
        $t = new Despesa(
            (float) $row['valor'],
            $row['descricao'],
            $row['data']
        );
    }

    $carteira->carregarTransacao($t);
}


try {

    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $valor = (float) $_POST['valor'];
    $data = $_POST['data'];

    $dataMin = ("2026-01-01");
    $dataMax = ("2026-12-31");

    if($data < $dataMin || $data > $dataMax){
        throw new Exception("Data inválida");
    }

    if ($tipo === "Entrada") {
        $transacao = new Receita($valor, $descricao, $data);
    } else {
        $transacao = new Despesa($valor, $descricao, $data);
    }

    $carteira->addTransacoes($transacao);

    $sql = "
        INSERT INTO transacoes (valor, tipo, descricao, data)
        VALUES (:valor, :tipo, :descricao, :data)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':valor' => $valor,
        ':tipo' => $tipo,
        ':descricao' => $descricao,
        ':data' => $data
    ]);


    $_SESSION['mensagem'] = "Transação cadastrada com sucesso!";

} catch (Exception $e) {

    $_SESSION['erro'] = $e->getMessage();

}

header("Location: index.php");
exit;