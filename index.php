<?php
require_once 'classes/Transacao.php';
require_once 'classes/Diario.php';
require_once 'classes/Receita.php';
require_once 'classes/Despesa.php';
require_once 'classes/Carteira.php';

session_start();

require_once 'database/conexao.php';

$carteira = new Carteira();

$stmt = $pdo->query("SELECT * FROM transacoes");
$transacoesWorkbanch = $stmt;

foreach ($transacoesWorkbanch as $param) {
    if ($param['tipo'] === "Entrada") {
        $t = new Receita((float) $param['valor'], $param['descricao'], $param['data']);
    } else if ($param['tipo'] === "Diario") {
        $t = new Diario((float) $param['valor'], $param['descricao'], $param['data']);
    } else {
        $t = new Despesa((float) $param['valor'], $param['descricao'], $param['data']);
    }

    $t->setId((int) $param['id']);

    $carteira->carregarTransacao($t);
}
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

            <div class="is-flex is-align-items-center mb-5">
                <span class="mr-4">
                    <img src="assets/wallet.png" alt="Logo" style="width: 120px; height: 120px;">
                </span>
                <div>
                    <h1 class="title is-1">
                        MyPocket
                    </h1>

                    <p class="subtitle is-5">
                        Controle financeiro pessoal
                    </p>
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <a href="login.php" class="button is-link">Sair</a>
                </div>
            </div>

            <div class="columns mb-5">
                <div class="column is-4 is-flex">
                    <div
                        class="box has-background-link is-flex-grow-1 is-flex is-flex-direction-column is-justify-content-center">
                        <h2 class="subtitle has-text-primary-15-invert">Saldo Atual</h2>
                        <p class="title is-1">
                            R$ <?= number_format($carteira->getSaldo(), 2, ',', '.') ?>
                        </p>
                    </div>
                </div>

                <div class="column is-8 is-flex">
                    <?php if (isset($_SESSION['erro'])): ?>
                        <div class="notification is-danger is-5 is-flex-grow-1 is-flex is-align-items-center">
                            <b><?= $_SESSION['erro']; ?></b>
                        </div>
                        <?php unset($_SESSION['erro']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['mensagem'])): ?>
                        <div class="notification is-success is-5 is-flex-grow-1 is-flex is-align-items-center">
                            <b><?= $_SESSION['mensagem']; ?></b>
                        </div>
                        <?php unset($_SESSION['mensagem']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="columns">

                <div class="column is-4">
                    <div class="box">


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
                                            <option value="Entrada">Receita</option>
                                            <option value="Diario">Diário</option>
                                            <option value="Saida">Despesa</option>
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
                </div>

                <div class="column is-8">
                    <div class="box">

                        <?php if (isset($_GET['filtro'])) {
                            $filtro = $_GET['filtro'];
                        } else {
                            $filtro = '';
                        } ?>

                        <form method="GET" action="#extrato" class="mt-2">
                            <div class="field is-grouped is-align-items-center">
                                <label class="label is-size-5">Filtrar</label>
                                <div class="select">
                                    <select name="filtro">
                                        <option value="">Todos</option>
                                        <option value="Entrada">Receitas</option>
                                        <option value="Diario">Diario</option>
                                        <option value="Saida">Despesas</option>
                                    </select>
                                </div>

                                <button class="button is-link">
                                    Filtrar
                                </button>
                            </div>
                        </form>

                        <div style="max-height: 390px; overflow-y: auto;">

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
                                                <?php if ($t->getTipo() === "Entrada"): ?>
                                                    <span class="tag is-success has-text-primary-15-invert is-size-6">
                                                        Receita
                                                    </span>
                                                <?php elseif ($t->getTipo() === "Diario"): ?>
                                                    <span class="tag is-warning has-text-primary-15-invert is-size-6">
                                                        Diário
                                                    </span>
                                                <?php else: ?>
                                                    <span class="tag is-danger has-text-primary-15-invert is-size-6">
                                                        Despesa
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="subtitle is-5"><?= $t->getDescricao(); ?></td>
                                            <td class="subtitle is-5"><?= (new DateTime($t->getData()))->format('d/m/Y') ?>
                                            </td>
                                            <td>
                                                <a href="editar.php?id=<?= $t->getId() ?>"
                                                    class="button is-small is-warning">Editar</a>

                                                <a href="delete.php?id=<?= $t->getId() ?>" class="button is-small is-danger"
                                                    onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
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
                        $totalDespesas -= $t->getValor();
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
            </div>

            <table class="table is-striped is-hoverable is-fullwidth mt-6">

                <tr>
                    <th class="title is-4">Valor</th>
                    <th class="title is-4">Tipo</th>
                    <th class="title is-4">Descrição</th>
                    <th class="title is-4">Data</th>
                </tr>
                <tr>
                </tr>
            </table>

        </div>
    </section>
</body>

</html>