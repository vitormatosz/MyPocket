<?php

require_once 'database/conexao.php';

// C - CREATE: Inserir usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);

    if (!empty($nome) && !empty($email)) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email) VALUES (:nome, :email)");
        $stmt->execute(['nome' => $nome, 'email' => $email]);
        header('Location: login.php');
        exit;
    }
}

// R - READ: Buscar todos os usuários
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br" data-theme="dark">
<head>
    <meta charset="UTF-8" >
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <title>CRUD de Usuários</title>
</head>
<body>
    <section class="section">
        <div class="container">

        <h3 class="title is-3">
            Cadastrar Usuário
        </h3>

    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar">
        <div class="field">
            <div class="control">
             <input class="input" type="text" name="nome" placeholder="Nome" required>
            </div>
        </div>
        <div class="field">
            <div class="control">
        <input class="input" type="email" name="email" placeholder="E-mail" required>
            </div>
        </div>
        <div class="field">
            <div class="control">
        <button class="button is-link" type="submit">Salvar</button>
            </div>
        </div>
    </form>

    <hr>

    <h3 class="title is-3">
        Lista de Usuários
    </h3>

    <table class="table is-striped is-hoverable is-fullwidth mt-3">
        <thead>
            <tr>
                <th class="title is-4">ID</th>
                <th class="title is-4">Nome</th>
                <th class="title is-4">E-mail</th>
                <th class="title is-4">Criado em</th>
                <th class="title is-4">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['criado_em'] ?></td>
                    <td>
                        <a href="editar.php?id=<?= $u['id'] ?>">Editar</a> | 
                        <a href="delete.php?id=<?= $u['id'] ?>" onclick="return confirm('Deseja excluir?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>