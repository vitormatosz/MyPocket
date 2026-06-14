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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Projeto Final PW2 - MyPocket</title>
</head>

<body class="text-bg-dark">

    <div class="container py-5">
        <div class="card-body mb-4 gap-2 d-flex align-items-end">
            <span>
                <img src="wallet.png" alt="Logo" style="width: 130px; height: 130px;">
            </span>

            <div>
                <h1 class="h1"><b>MyPocket</b></h1>
                <p class="fs-5">
                    Controle financeiro pessoal
                </p>
            </div>
        </div>

        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger fs-6">
                <?= $_SESSION['erro']; ?>
            </div>

            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-success fs-6">
                <?= $_SESSION['mensagem']; ?>
            </div>

            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>


        <div class="card bg-primary text-white shadow-lg mb-5 rounded m-0">
            <div class="card-body p-3">
                <p class="mb-0">
                    <span class="fs-5">Saldo Atual:</span><br>
                    <span class="h1"><b>R$ <?= number_format($carteira->getSaldo(), 2, ',', '.') ?></b></span>
                </p>
            </div>
        </div>

        <div class="card text-bg-dark shadow-lg rounded mb-4 p-2">
            <div class="card-body">

                <h3 class="h3">
                    <b>Nova Transação</b>
                </h3>

                <form data-bs-theme="dark" class="bg-dark text-white" action="processa.php" method="POST">
                    <div class="mb-3">
                        <label class="label">Valor</label>
                        <input class="form-control" step="0.01" type="number" name="valor" required>
                    </div>

                    <div class="mb-3">
                        <label class="label">Tipo</label>
                        <select class="form-select" name="tipo" required>
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="label">Descrição</label>
                        <input class="form-control" type="text" name="descricao" required>
                    </div>

                    <div class="mb-3">
                        <label class="label">Data</label>
                        <input class="form-control" type="date" name="data" required>
                    </div>

                    <div class="mb-3">
                        <button class="btn btn-primary">Adicionar</button>
                    </div>

                </form>
            </div>
        </div>

        <div class="mt-6" id="extrato">
            <h3 class="mb-4"><b>Extrato</b></h3>

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

            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <b>Total Receitas:<br></b>
                        <span class="h3"><b>R$ <?= number_format($totalReceitas, 2, ',', '.') ?></b></span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="alert alert-danger">
                        <b>Total Despesas:<br></b>
                        <span class="h3"><b>R$ <?= number_format($totalDespesas, 2, ',', '.') ?></span></b>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['filtro'])) {
                $filtro = $_GET['filtro'];
            } else {
                $filtro = '';
            } ?>

            <form data-bs-theme="dark" class="bg-dark text-white" method="GET" action="#extrato" class="mt-6">

                <div class="mb-3 d-flex-flex-column">
                    <label class="label">Filtrar</label>

                    <div class="mt-2 d-flex gap-2 align-items-center">
                        <select class="form-select w-auto d-inline-block" name="filtro">
                            <option value="">Todos</option>
                            <option value="Entrada">Receitas</option>
                            <option value="Saida">Despesas</option>
                        </select>

                        <button class="btn btn-primary">
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-hover mt-3 table-dark">
                <tr>
                    <th class="h4">Valor</th>
                    <th class="h4">Tipo</th>
                    <th class="h4">Descrição</th>
                    <th class="h4">Data</th>
                </tr>
                <tr>
                    <?php foreach ($carteira->getTransacoes() as $t): ?>
                        <?php if ($filtro == '' || $t->getTipo() == $filtro): ?>

                        <tr>
                            <td class="fs-6"><?php $r = number_format($t->getValor(), 2, ',', '.');
                            echo ("R$$r"); ?></td>
                            <td class="fs-6">
                                <?php if ($t->getTipo() == "Entrada"): ?>
                                    <span class="badge bg-success">
                                        Receita
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        Despesa
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-6"><?= $t->getDescricao(); ?></td>
                            <td class="fs-6"><?= (new DateTime($t->getData()))->format('d/m/Y') ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tr>
            </table>
        </div>
    </div>
    </section>
</body>

</html>