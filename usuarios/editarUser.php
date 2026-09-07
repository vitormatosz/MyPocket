<?php

require_once "../database/conexao.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

// U - UPDATE: Salvar alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $conf = trim($_POST['conf']);

    if (!empty($nome) && !empty($email)) {
        if ($senha !== $conf) {
            $error = "As senhas estão diferentes!";
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha WHERE id = :id");
            $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => password_hash($senha, PASSWORD_DEFAULT), 'id' => $id]);
            header('Location: index.php');
            exit;
        }
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
<html lang="pt-br" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <title>Editar Usuário</title>
</head>

<body>
    <section class="hero is-fullheight">
        <div class="hero-body is-justify-content-center">
            <!-- Definimos uma largura máxima (max-width) para o card do formulário -->
            <div class="container" style="max-width: 700px;">

                <h3 class="title is-3">
                    Editar Usuário <?= $usuario['id'] ?> - <?= htmlspecialchars($usuario['nome']) ?>
                </h3>

                <form method="POST">
                    <input type="hidden" name="acao" value="cadastrar">
                    <div class="field">
                        <div class="control">
                            <input class="input" type="text" name="nome"
                                value="<?= htmlspecialchars($usuario['nome']) ?>" placeholder="Nome" required>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <input class="input" type="email" name="email"
                                value="<?= htmlspecialchars($usuario['email']) ?>" placeholder="E-mail" required>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <input class="input" type="password" name="senha" placeholder="Nova Senha" required>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <input class="input" type="password" name="conf" placeholder="Confirmar Nova Senha" required>
                        </div>
                    </div>

                    <div class="field is-grouped is-align-items-center">
                        <div class="control">
                            <button class="button is-link" type="submit">Atualizar</button>
                        </div>
                        <div class="control">
                            <a href="cadastro.php" class="has-text-link">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>

</html>