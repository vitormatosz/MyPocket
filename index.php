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
<html lang="pt-br" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <title>Projeto Final PW2 - MyPocket</title>
</head>

<body>
    <section class="section">
        <div class="container">

            <h1 class="title is-1">MyPocket</h1>

            <div class="box has-background-link-65">
                <h2 class="subtitle has-text-primary-15-invert">Saldo Atual</h2>

                <p class="title is-1">
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
                            <button class="button is-link"
                                style="width: 200px;">Adicionar</button>
                        </div>
                    </div>


                </form>
            </div>

            <div class="mt-6" id="extrato">
                <h3 class="title is-3">Extrato</h3>

                <?php
                $totalReceitas = 0;
                $totalDespesas = 0;

                foreach ($carteira->getTransacoes() as $t) {

                    if ($t->getTipo() == "Entrada") {
                        $totalReceitas += $t->getValor();
                    } else {
                        $totalDespesas += $t->getValor();
                    }
                }
                ?>

                <div class="columns">
                    <div class="column">
                        <div class="notification is-success has-text-primary-15-invert">
                            <b>Total Receitas:<br>
                            <span class="title is-3">R$ <?= number_format($totalReceitas, 2, ',', '.') ?></span></b>
                        </div>
                    </div>

                    <div class="column">
                        <div class="notification is-danger has-text-primary-15-invert">
                            <b>Total Despesas:<br>
                            <span class="title is-3">R$ <?= number_format($totalDespesas, 2, ',', '.') ?></span></b>
                        </div>
                    </div>
                </div>



                <?php $filtro = $_GET['filtro'] ?? ''; ?>

                <form method="GET" action="#extrato" class="mt-6">

                    <div class="field">
                        <label class="label is-size-4">Filtrar</label>

                        <div class="select is-medium">
                            <select name="filtro">
                                <option value="">Todos</option>
                                <option value="Entrada">Receitas</option>
                                <option value="Saida">Despesas</option>
                            </select>
                        </div>

                        <button class="button is-medium is-link">
                            Filtrar
                        </button>

                    </div>

                </form>

                <table class="table is-striped is-hoverable is-fullwidth mt-3">
                    <tr>
                        <th class="title is-4">Valor</th>
                        <th class="title is-4">Tipo</th>
                        <th class="title is-4">Descrição</th>
                        <th class="title is-4">Data</th>
                    </tr>

                    <tr>

                        <?php foreach ($carteira->getTransacoes() as $t): ?>

                            <?php if ($filtro == '' || $t->getTipo() == $filtro): ?>

                            <tr>
                                <td class="subtitle is-5"><?php $r = number_format($t->getValor(), 2, ',', '.');
                                echo ("R$$r"); ?></td>
                                <td class="subtitle is-5">
                                    <?php if ($t->getTipo() == "Entrada"): ?>
                                        <span class="tag is-success has-text-primary-15-invert is-size-6">
                                            Receita
                                        </span>
                                    <?php else: ?>
                                        <span class="tag is-danger has-text-primary-15-invert is-size-6">
                                            Despesa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="subtitle is-5"><?= $t->getDescricao(); ?></td>
                                <td class="subtitle is-5"><?= (new DateTime($t->getData()))->format('d/m/Y') ?></td>
                            </tr>

                        <?php endif; ?>

                    <?php endforeach; ?>

                    </tr>
                </table>

                <a href="limpar.php" class="btn btn-danger">Resetar Carteira</a>
            </div>
        </div>
    </section>
</body>

</html>