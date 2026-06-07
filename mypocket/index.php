<?php

require_once 'classes/Transacao.php';
require_once 'classes/Receita.php';
require_once 'classes/Despesa.php';
require_once 'classes/Carteira.php';

session_start();

if (!isset($_SESSION['carteira'])) {
    $_SESSION['carteira'] = new Carteira();
}

$carteira = $_SESSION['carteira'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <title>Projeto Final PW2 - MyPocket</title>
</head>

<body>
    <section class="section">
        <div class="container">

            <h1 class="title is-1"> MyPocket</h1>

            <div class="box">
                <h2 class="subtitle">Saldo Atual</h2>

                <p class="title has-text-link">
                    R$ <?= number_format($carteira->getSaldo(), 2, ',', '.') ?>
                </p>
            </div>

            <div class="box mt-6">

                <h3 class="title is-3">
                    Nova Transação
                </h3>

                <form action="processa.php" method="POST">
                    <div class="field">
                        <label class="label">Valor</label>

                        <div class="control">
                            <input class="input" step="0.01" type="number" name="valor" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Tipo</label>

                        <div class="control">
                            <div class="select">
                                <select name="tipo" required>
                                    <option value="receita">Receita</option>
                                    <option value="despesa">Despesa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Descrição</label>
                        <div class="control"><input class="input" type="text" name="descricao" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Data</label>
                        <div class="control">
                            <input class="input" type="date" name="data" required>
                        </div>
                    </div>

                    <div class="field ">
                        <div class="control">
                            <button class="button is-link" style="width: 200px;">Adicionar</button>
                        </div>
                    </div>


                </form>
            </div>

            <div class="mt-6">
                <h3 class="title is-3">Extrato</h3>

                <table class="table is-striped is-hoverable is-fullwidth">
                    <tr>
                        <th class="title is-4">Valor</th>
                        <th class="title is-4">Tipo</th>
                        <th class="title is-4">Descrição</th>
                        <th class="title is-4">Data</th>
                    </tr>

                    <tr>
                        <?php foreach ($carteira->getTransacoes() as $t): ?>

                        <tr>
                            <td class="subtitle is-6"><b><?php $r = number_format($t->getValor(), 2, ',', '.'); 
                            echo ("R$$r");?></b></td>
                            <td class="subtitle is-6">
                                <?php if ($t->getTipo() == "Entrada"): ?>
                                    <span class="tag is-success">
                                        Receita
                                    </span>
                                <?php else: ?>
                                    <span class="tag is-danger">
                                        Despesa
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="subtitle is-6"><?= $t->getDescricao(); ?></td>
                            <td class="subtitle is-6"><?= (new DateTime($t->getData()))->format('d/m/Y') ?></td>
                        </tr>

                    <?php endforeach; ?>

                    </tr>
                </table>

                <a href="limpar.php" class="btn btn-danger">Resetar Carteira</a>
            </div>
        </div>
    </section>
</body>

</html>