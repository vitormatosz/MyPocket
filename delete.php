<?php

session_start();

require_once "database/conexao.php";

$id = $_GET["id"] ?? null;
$idUser = $_GET["id"] ?? null;


if ($id) {

    $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id AND id_usuario = :id_usuario");
    $stmt->execute(["id" => $id, "id_usuario" => $_SESSION["usuario_id"]]);
    $transacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transacao) {
        
        $saldoAtual = 0;
        $todas = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario");
        $todas->execute(["id_usuario" => $_SESSION['usuario_id']]);
        foreach ($todas as $param) {
            if ($param["tipo"] === "Entrada") {
                $saldoAtual += $param["valor"];
            } else {
                $saldoAtual -= $param["valor"];
            }
        }

        if ($transacao["tipo"] === "Entrada") {
            $saldoAposExcluir = $saldoAtual - $transacao["valor"];
        } else {
            $saldoAposExcluir = $saldoAtual + $transacao["valor"];
        }

        if ($saldoAposExcluir < 0) {
            $_SESSION["erro"] = "Não é possível excluir: o saldo ficaria negativo!";
        } else {
            $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = :id AND id_usuario = :id_usuario");
            $stmt->execute(["id" => $id, "id_usuario" => $_SESSION['usuario_id']]);
            $_SESSION["mensagem"] = "Transação excluída com sucesso!";
        }
    }
}

header('Location: index.php');
exit;
?>