<?php

session_start();

require_once 'database/conexao.php';

// Se já estiver logado, não faz sentido ver o login de novo
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

// Processa o envio do formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailForm = trim($_POST['email']);
    $senhaForm = trim($_POST['senha']);

    if (!empty($emailForm) && !empty($senhaForm)) {
        // Busca o usuário no banco de dados pelo e-mail
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $emailForm]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && (password_verify($senhaForm, $usuario['senha']))) {
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];

            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos!";
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <title>Login de Usuários</title>
</head>

<body>
    <section class="hero is-fullheight">
        <div class="hero-body">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column is-6-desktop is-6-tablet">

                        <div class="card p-6">
                            <h3 class="title is-3 has-text-start">Entrar</h3>

                            <form method="POST">
                                <input type="hidden" name="acao" value="entrar">

                                <div class="field">
                                    <div class="control">
                                        <input class="input" type="email" name="email" placeholder="E-mail" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="control">
                                        <input class="input" type="password" name="senha" placeholder="Senha" required>
                                    </div>
                                </div>

                                <div class="field is-grouped is-align-items-center">
                                    <div class="control">
                                        <button class="button is-link" type="submit">Entrar</button>
                                    </div>
                                    <div class="control">
                                        <a href="cadastro.php" class="has-text-link">Cadastre-se agora</a>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>