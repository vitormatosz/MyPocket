<?php
declare(strict_types=1);

session_start();

require_once "classes/Carteira.php";

if (!isset($_SESSION['carteira'])) {
    $_SESSION['carteira'] = new Carteira();
}

try {

    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $valor = (float) $_POST['valor'];
    $data = date('d/m/Y');
    $carteira = $_SESSION['carteira'];

    if ($tipo === "receita") {
        $transacao = new Receita($valor, $descricao, $data);
    } else {
        $transacao = new Despesa($valor, $descricao, $data);
    }

    $carteira->addTransacoes($transacao);

    $_SESSION['mensagem'] = "Transação cadastrada com sucesso!";

} catch (Exception $e) {

    $_SESSION['erro'] = $e->getMessage();

}

header("Location: index.php");
exit;