<?php
require_once "../auten.php";
require_once "../database/conexao.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: editarUser.php');
    exit;
}

$id = (int) $_SESSION['usuario_id'];

if ($id) {
    
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

header('Location: cadastro.php');
exit;
?>