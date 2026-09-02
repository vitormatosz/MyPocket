<?php
declare(strict_types=1);

require_once "classes/Transacao.php";
require_once "classes/Receita.php";
require_once "classes/Despesa.php";
require_once "classes/Diario.php";
require_once "classes/Carteira.php";

session_start();

require_once 'database/conexao.php';
require_once 'auten.php';

$carteira = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario");
$stmt->execute(['id_usuario' => $_SESSION['usuario_id']]);

foreach ($stmt as $row) {

    if ($row['tipo'] === "Entrada") {
        $t = new Receita(
            (float) $row['valor'],
            $row['descricao'],
            $row['data']
        );
    } else if ($row['tipo'] === "Diario") {
        $t = new Diario(
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

    if (empty($data)) {
        throw new Exception("Data inválida");
    }

    if ($tipo === "Entrada") {
        $transacao = new Receita($valor, $descricao, $data);
    } else if ($tipo === "Diario") {
        $transacao = new Diario($valor, $descricao, $data);
    } else {
        $transacao = new Despesa($valor, $descricao, $data);
    }

    $carteira->addTransacoes($transacao);

    $sql = "
        INSERT INTO transacoes (valor, tipo, descricao, data, id_usuario)
        VALUES (:valor, :tipo, :descricao, :data, :id_usuario)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':valor' => $valor,
        ':tipo' => $tipo,
        ':descricao' => $descricao,
        ':data' => $data,
        ':id_usuario' => $_SESSION['usuario_id']
    ]);


    $_SESSION['mensagem'] = "Transação cadastrada com sucesso!";

} catch (Exception $e) {
    $_SESSION['erro'] = $e->getMessage();
}

header("Location: index.php");
exit;