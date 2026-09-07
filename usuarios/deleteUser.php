<?php

require_once "../database/conexao.php";

$id = $_GET['id'] ?? null;

if ($id) {
    // D - DELETE: Remover do banco
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

header('Location: cadastro.php');
exit;
?>