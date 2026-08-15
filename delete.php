<?php

session_start();

require_once "database/conexao.php";

$id = $_GET["id"] ?? null;

if ($id) {
    // Busca a transação que será excluída
    $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id");
    $stmt->execute(["id" => $id]);
    $transacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transacao) {
        // Calcula o saldo atual somando todas as transações do banco
        $saldoAtual = 0;
        $todas = $pdo->query("SELECT * FROM transacoes");
        foreach ($todas as $param) {
            if ($param["tipo"] === "Entrada") {
                $saldoAtual += $param["valor"];
            } else {
                $saldoAtual -= $param["valor"];
            }
        }

        // Calcula como o saldo ficaria sem essa transação
        if ($transacao["tipo"] === "Entrada") {
            $saldoAposExcluir = $saldoAtual - $transacao["valor"];
        } else {
            $saldoAposExcluir = $saldoAtual + $transacao["valor"];
        }

        if ($saldoAposExcluir < 0) {
            $_SESSION["erro"] = "Não é possível excluir: o saldo ficaria negativo!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = :id");
            $stmt->execute(["id" => $id]);
            $_SESSION["mensagem"] = "Transação excluída com sucesso!";
        }
    }
}

header("Location: index.php");
exit;
?>