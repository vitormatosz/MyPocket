<?php

require_once 'conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

// U - UPDATE: Salvar alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);

    if (!empty($nome) && !empty($email)) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'email' => $email, 'id' => $id]);
        header('Location: index.php');
        exit;
    }
}

// Buscar dados atuais do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
</head>
<body>

    <h2>Editar Usuário #<?= $usuario['id'] ?></h2>
    <form method="POST">
        <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
        <button type="submit">Atualizar</button>
        <a href="index.php">Cancelar</a>
    </form>

</body>
</html>