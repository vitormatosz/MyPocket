<?php

session_start();

if (!isset($_SESSION['carteira'])) {
    require_once 'classes/Carteira.php';
    $_SESSION['carteira'] = new Carteira();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projeto Final PW2 - MyPocket</title>
</head>

<body>
    <h3>Saldo:
        R$ <?= number_format($carteira->getSaldo(), 2, ',', '.') ?>
    </h3>

    <div>
        <form action="processa.php" method="POST">
            <div>
                <label>Valor</label>
                <input type="number" name="valor">
            </div>

            <div>
                <label>Tipo</label>
                <select name="tipo" required>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                </select>
            </div>

            <div>
                <label>Descrição</label>
                <input type="text" name="descricao">
            </div>

            <div>
                <label>Data</label>
                <input type="date" name="data">
            </div>

            <button>Salvar</button>

        </form>
    </div>

    <div>
        <h5>Extrato</h5>
        <table>
            <tr>
                <th>Valor</th>
                <th>Tipo</th>
                <th>Descrição</th>
                <th>Data</th>
            </tr>

            <tr>
                <?php foreach ($carteira->getTransacoes() as $t): ?>

                <tr>
                    <td><?= $t->getValor() ?></td>
                    <td><?= $t->getTipo(); ?></td>
                    <td><?= $t->getDescricao(); ?></td>
                    <td><?= $t->getData(); ?></td>
                </tr>

            <?php endforeach; ?>

            </tr>
        </table>
    </div>
</body>

</html>