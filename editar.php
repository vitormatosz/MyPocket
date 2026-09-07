<?php

require_once "database/conexao.php";

session_start();

$id = $_GET["id"] ?? null;

// U - UPDATE: Salvar alterações
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $valor = trim($_POST["valor"]);
    $tipo = trim($_POST["tipo"]);
    $descricao = trim($_POST["descricao"]);
    $data = trim($_POST["data"]);

    if (!empty($valor) && !empty($tipo) && !empty($descricao) && !empty($data)) {

        $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id AND id_usuario = :id_usuario");
        $stmt->execute(["id" => $id, "id_usuario" => $_SESSION["usuario_id"]]);
        $antiga = $stmt->fetch(PDO::FETCH_ASSOC);

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

        if ($antiga["tipo"] === "Entrada") {
            $saldoAtual -= $antiga["valor"];
        } else {
            $saldoAtual += $antiga["valor"];
        }

        if ($tipo === "Entrada") {
            $saldoAposEditar = $saldoAtual + (float) $valor;
        } else {
            $saldoAposEditar = $saldoAtual - (float) $valor;
        }

        if ($saldoAposEditar < 0) {
            $_SESSION["erro"] = "Não é possível salvar: o saldo ficaria negativo!";
            header("Location: index.php");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE transacoes SET valor = :valor, tipo = :tipo, descricao = :descricao, data = :data WHERE id = :id AND id_usuario = :id_usuario");
        $stmt->execute(['valor' => $valor, 'tipo' => $tipo, 'descricao' => $descricao, 'data' => $data, 'id' => $id, 'id_usuario' => $_SESSION['usuario_id']]);
        $_SESSION['mensagem'] = "Transação atualizada com sucesso!";
        header('Location: index.php');
        exit;
    }
}

// Buscar dados atuais do usuário
$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id");
$stmt->execute(["id" => $id]);
$transacao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transacao) {
    header("Location: index.php");
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
    <section class="section">
        <div class="container">
            <?php if (isset($_SESSION['erro'])): ?>
                <div class="notification is-danger is-5">
                    <b><?= $_SESSION['erro']; ?></b>
                </div>

                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="notification is-success is-5">
                    <b><?= $_SESSION['mensagem']; ?></b>
                </div>

                <?php unset($_SESSION['mensagem']); ?>
            <?php endif; ?>
            <div class="box mt-6">
                <h3 class="title is-3">
                    Editar Transação - <?= $transacao["id"] ?>
                </h3>
                <form method="POST">
                    <div class="field">
                        <label class="label">Valor</label>
                        <div class="control">
                            <input class="input" step="0.01" type="number" name="valor"
                                value="<?= htmlspecialchars($transacao["valor"]) ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Tipo</label>
                        <div class="control">
                            <div class="select">
                                <select name="tipo" value="<?= htmlspecialchars($transacao["tipo"]) ?>" required>
                                    <option value="Entrada" <?= $transacao["tipo"] === "Entrada" ? "selected" : ""?>>
                                        Receita
                                    </option>

                                    <option value="Diario" <?= $transacao["tipo"] === "Diario" ? "selected" : ""?>>
                                        Diario
                                    </option>

                                    <option value="Saida" <?= $transacao["tipo"] === "Saida" ? "selected" : ""?>>
                                        Despesa
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Descrição</label>
                        <div class="control"><input class="input" type="text" name="descricao"
                                value="<?= htmlspecialchars($transacao["descricao"]) ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Data</label>
                        <div class="control">
                            <input class="input" type="date" name="data"
                                value="<?= htmlspecialchars($transacao["data"]) ?>" required>
                        </div>
                    </div>

                    <div class="field ">
                        <div class="buttons">
                            <button type="submit" class="button is-link" style="width: 200px;">Adicionar</button>

                            <a href="index.php" class="button is-danger">Cancelar</a>
                        </div>

                    </div>
                </form>
            </div>
            </form>

        </div>


        </div>
    </section>

</body>

</html>