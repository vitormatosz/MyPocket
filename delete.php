<?php

session_start();

require_once "database/conexao.php";

$id = $_GET["id"] ?? null;

if ($id) {

    $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id AND id_usuario = :id_usuario AND cancelada = 0");
    $stmt->execute(["id" => $id, "id_usuario" => $_SESSION["usuario_id"]]);
    $transacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transacao) {
        $stmtSaldo = $pdo->prepare("
        SELECT tipo, valor
        FROM transacoes
        WHERE id_usuario = :id_usuario
        AND cancelada = 0
        AND data <= CURDATE()");
        
        $stmtSaldo->execute(['id_usuario' => $_SESSION['usuario_id']]);

        $totalEntradas = 0;
        $totalSaidas = 0;

        foreach ($stmtSaldo as $row) {
            if ($row['tipo'] === 'Entrada') {
                $totalEntradas += (float) $row['valor'];
            } else {
                $totalSaidas += (float) $row['valor'];
            }
        }

        if ($saldoAposExcluir < 0) {
            $_SESSION["erro"] = "Não é possível excluir: o saldo ficaria negativo!";
        } else {
            if (!empty($transacao['id_recorrencia'])) {
                $stmt = $pdo->prepare("
                    UPDATE transacoes
                    SET cancelada = 1
                    WHERE id = :id AND id_usuario = :id_usuario
                ");
                $stmt->execute([
                    "id" => $id,
                    "id_usuario" => $_SESSION['usuario_id']
                ]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = :id AND id_usuario = :id_usuario");
                $stmt->execute(["id" => $id, "id_usuario" => $_SESSION['usuario_id']]);
            }

            $_SESSION["mensagem"] = "Transação excluída com sucesso!";
        }
    }
}

header('Location: index.php');
exit;
?>