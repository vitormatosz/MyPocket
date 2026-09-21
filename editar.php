<?php

require_once "auten.php";
require_once "database/conexao.php";

$id = $_GET["id"] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $valor = (float) ($_POST["valor"] ?? 0);
    $tipo = trim($_POST["tipo"] ?? '');
    $descricao = trim($_POST["descricao"] ?? '');
    $data = trim($_POST["data"] ?? '');

    if ($valor > 0 && in_array($tipo, ['Entrada', 'Saida', 'Diario'], true) && $descricao !== '' && $data !== '') {

        $stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id AND id_usuario = :id_usuario AND cancelada = 0");
        $stmt->execute(["id" => $id, "id_usuario" => $_SESSION["usuario_id"]]);
        $antiga = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$antiga) {
            header("Location: index.php");
            exit;
        }

        $stmtSaldo = $pdo->prepare("SELECT tipo, valor FROM transacoes WHERE id_usuario = :id_usuario AND cancelada = 0 AND data <= CURDATE() ");
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

        $saldoAtual = $totalEntradas - $totalSaidas;
        $hoje = date('Y-m-d');

        $saldoAposEditar = $saldoAtual;

        if ($antiga['data'] <= $hoje) {
            if ($antiga['tipo'] === 'Entrada') {
                $saldoAposEditar -= (float) $antiga['valor'];
            } else {
                $saldoAposEditar += (float) $antiga['valor'];
            }
        }

        if ($data <= $hoje) {
            if ($tipo === "Entrada") {
                $saldoAposEditar += $valor;
            } else {
                $saldoAposEditar -= $valor;
            }
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

    $_SESSION['erro'] = "Preencha todos os campos com valores válidos!";
}

// Buscar dados atuais da transação
$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = :id AND id_usuario = :id_usuario AND cancelada = 0");
$stmt->execute(["id" => $id, "id_usuario" => $_SESSION["usuario_id"]]);
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
                                    <option value="Entrada" <?= $transacao["tipo"] === "Entrada" ? "selected" : "" ?>>
                                        Receita
                                    </option>

                                    <option value="Diario" <?= $transacao["tipo"] === "Diario" ? "selected" : "" ?>>
                                        Diario
                                    </option>

                                    <option value="Saida" <?= $transacao["tipo"] === "Saida" ? "selected" : "" ?>>
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
        </div>
    </section>

</body>

</html>