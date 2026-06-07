<?php
declare(strict_types=1);

require_once "classes/Transacao.php";
require_once "classes/Receita.php";
require_once "classes/Despesa.php";
require_once "classes/Carteira.php";

session_start();

if (!isset($_SESSION['carteira'])) {
    $_SESSION['carteira'] = new Carteira();
}

$carteira = $_SESSION['carteira'];

try {

    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $valor = (float) $_POST['valor'];
    $data = $_POST['data'];

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

$_SESSION['carteira'] = $carteira;

header("Location: index.php");
exit;