<?php
require_once 'classes/Transacao.php';
require_once 'auten.php';
require_once 'classes/Diario.php';
require_once 'classes/Receita.php';
require_once 'classes/Despesa.php';
require_once 'classes/Carteira.php';

require_once 'database/conexao.php';

require_once 'database/conexao.php';

$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? date('m');

$carteiraGeral = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario");
$stmt->execute(['id_usuario' => $_SESSION['usuario_id']]);

foreach ($stmt as $row) {
    if ($row['tipo'] === "Entrada") {
        $t = new Receita((float) $row['valor'], $row['descricao'], $row['data']);
    } elseif ($row['tipo'] === "Diario") {
        $t = new Diario((float) $row['valor'], $row['descricao'], $row['data']);
    } else {
        $t = new Despesa((float) $row['valor'], $row['descricao'], $row['data']);
    }
    $t->setId((int) $row['id']);
    $carteiraGeral->carregarTransacao($t);
}

$carteira = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario AND YEAR(data) = :ano");
$stmt->execute(['id_usuario' => $_SESSION['usuario_id'], 'ano' => $ano]);

foreach ($stmt as $row) {
    if ($row['tipo'] === "Entrada") {
        $t = new Receita((float) $row['valor'], $row['descricao'], $row['data']);
    } elseif ($row['tipo'] === "Diario") {
        $t = new Diario((float) $row['valor'], $row['descricao'], $row['data']);
    } else {
        $t = new Despesa((float) $row['valor'], $row['descricao'], $row['data']);
    }
    $t->setId((int) $row['id']);
    $carteira->carregarTransacao($t);
}

$carteiraMes = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario AND YEAR(data) = :ano AND MONTH(data) = :mes");
$stmt->execute(['id_usuario' => $_SESSION['usuario_id'], 'ano' => $ano, 'mes' => $mes]);

foreach ($stmt as $row) {
    if ($row['tipo'] === "Entrada") {
        $t = new Receita((float) $row['valor'], $row['descricao'], $row['data']);
    } elseif ($row['tipo'] === "Diario") {
        $t = new Diario((float) $row['valor'], $row['descricao'], $row['data']);
    } else {
        $t = new Despesa((float) $row['valor'], $row['descricao'], $row['data']);
    }
    $t->setId((int) $row['id']);
    $carteiraMes->carregarTransacao($t);
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
        <div class="container is-fluid">

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
            <div class="field is-grouped is-align-items-center mb-5">
                <p class="mb-2">Olá, <b><?= htmlspecialchars($_SESSION['usuario_nome']) ?></b></p>
                <div class="control">
                    <a href="logout.php" class="button is-link">Sair</a>
                </div>
            </div>

            <div class="columns mb-5">
                <div class="column is-4 is-flex">
                    <div
                        class="box has-background-link is-flex-grow-1 is-flex is-flex-direction-column is-justify-content-center">
                        <h2 class="subtitle has-text-primary-15-invert">Saldo Atual</h2>
                        <p class="title is-1">
                            R$ <?= number_format($carteiraGeral->getSaldo(), 2, ',', '.') ?>
                        </p>
                    </div>
                </div>

                <div class="column is-8 is-flex">
                    <?php if (isset($_SESSION['erro'])): ?>
                        <div
                            class="notification is-danger is-1 is-flex-grow-1 is-flex is-align-items-center is-justify-content-center has-text-centered">
                            <b class="mb-0"><?= $_SESSION['erro']; ?></b>
                        </div>
                        <?php unset($_SESSION['erro']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['mensagem'])): ?>
                        <div
                            class="notification is-success is-1 is-flex-grow-1 is-flex is-align-items-center is-justify-content-center has-text-centered">
                            <b class="mb-0"><?= $_SESSION['mensagem']; ?></b>
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
                                    <?php foreach ($carteiraGeral->getTransacoes() as $t): ?>
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
                $totalDiario = 0;
                $totalDespesas = 0;

                foreach ($carteira->getTransacoes() as $t) {
                    if ($t->getTipo() == "Entrada") {
                        $totalReceitas += $t->getValor();
                    } else if ($t->getTipo() == "Diario") {
                        $totalDiario += $t->getValor();
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
                        <div class="notification is-warning has-text-primary-15-invert">
                            <b>Total Diario:<br>
                                <span class="title is-3">R$ <?= number_format($totalDiario, 2, ',', '.') ?></span></b>
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



            <div class="mt-6" id="transacoes-mes">

                <div class="is-flex is-align-items-center is-justify-content-space-between">
                    <h3 class="title is-3 mb-0">Transações de <?= $nomesMeses[$mes] ?? $mes ?>/<?= $ano ?></h3>

                    <div class="is-flex is-align-items-center is-justify-content-end " style="gap: 10px;">
                        <form method="GET" id="mes" action="#transacoes-mes">
                            <input type="hidden" name="mes" value="<?= htmlspecialchars($mes) ?>">
                            <div class="select">
                                <select name="ano" onchange="this.form.submit()">
                                    <?php
                                    $anos = [
                                        '2030' => '2030',
                                        '2029' => '2029',
                                        '2028' => '2028',
                                        '2027' => '2027',
                                        '2026' => '2026',
                                        '2025' => '2025',
                                        '2024' => '2024',
                                    ];
                                    foreach ($anos as $num => $nome): ?>
                                        <option value="<?= $num ?>" <?= $num == $ano ? 'selected' : '' ?>><?= $nome ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>

                        <form method="GET" action="#transacoes-mes">
                            <input type="hidden" name="ano" value="<?= htmlspecialchars($ano) ?>">
                            <div class="select">
                                <select name="mes" onchange="this.form.submit()">
                                    <?php
                                    $nomesMeses = [
                                        '01' => 'Janeiro',
                                        '02' => 'Fevereiro',
                                        '03' => 'Março',
                                        '04' => 'Abril',
                                        '05' => 'Maio',
                                        '06' => 'Junho',
                                        '07' => 'Julho',
                                        '08' => 'Agosto',
                                        '09' => 'Setembro',
                                        '10' => 'Outubro',
                                        '11' => 'Novembro',
                                        '12' => 'Dezembro'
                                    ];
                                    foreach ($nomesMeses as $num => $nome): ?>
                                        <option value="<?= $num ?>" <?= $num == $mes ? 'selected' : '' ?>><?= $nome ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (empty($carteiraMes->getTransacoes())): ?>
                    <p class="subtitle is-5 mt-3">Nenhuma transação em <?= $nomesMeses[$mes] ?? $mes ?>/<?= $ano ?>.</p>
                <?php else: ?>
                    <table class="table is-striped is-hoverable is-fullwidth mt-3">
                        <tr>
                            <th class="title is-4">Valor</th>
                            <th class="title is-4">Tipo</th>
                            <th class="title is-4">Descrição</th>
                            <th class="title is-4">Data</th>
                            <th></th>
                        </tr>
                        <?php foreach ($carteiraMes->getTransacoes() as $t): ?>
                            <tr>
                                <td class="subtitle is-5">R$<?= number_format($t->getValor(), 2, ',', '.') ?></td>
                                <td class="subtitle is-5">
                                    <?php if ($t->getTipo() === "Entrada"): ?>
                                        <span class="tag is-success has-text-primary-15-invert is-size-6">Receita</span>
                                    <?php elseif ($t->getTipo() === "Diario"): ?>
                                        <span class="tag is-warning has-text-primary-15-invert is-size-6">Diário</span>
                                    <?php else: ?>
                                        <span class="tag is-danger has-text-primary-15-invert is-size-6">Despesa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="subtitle is-5"><?= $t->getDescricao(); ?></td>
                                <td class="subtitle is-5"><?= (new DateTime($t->getData()))->format('d/m/Y') ?></td>
                                <td>
                                    <a href="editar.php?id=<?= $t->getId() ?>" class="button is-small is-warning">Editar</a>
                                    <a href="delete.php?id=<?= $t->getId() ?>" class="button is-small is-danger"
                                        onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>

            <div class="mt-6" id="resumo-mensal">
                <h3 class="title is-3">Resumo Mensal de <?= $ano ?></h3>

                <?php
                $stmtAnterior = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario AND data < :inicio_ano");
                $stmtAnterior->execute([
                    'id_usuario' => $_SESSION['usuario_id'],
                    'inicio_ano' => $ano . '-01-01'
                ]);

                $saldoAcumulado = 0;
                foreach ($stmtAnterior as $row) {
                    if ($row['tipo'] === 'Entrada') {
                        $saldoAcumulado += $row['valor'];
                    } else {
                        $saldoAcumulado -= $row['valor'];
                    }
                }

                $nomesMeses = [
                    '01' => 'Janeiro',
                    '02' => 'Fevereiro',
                    '03' => 'Março',
                    '04' => 'Abril',
                    '05' => 'Maio',
                    '06' => 'Junho',
                    '07' => 'Julho',
                    '08' => 'Agosto',
                    '09' => 'Setembro',
                    '10' => 'Outubro',
                    '11' => 'Novembro',
                    '12' => 'Dezembro'
                ];

                // Pré-popula os 12 meses zerados
                $resumoPorMes = [];
                foreach ($nomesMeses as $numMes => $nome) {
                    $resumoPorMes[$numMes] = ['entradas' => 0, 'saidas' => 0, 'diario' => 0];
                }

                // Soma as transações do ano em cima dos meses já criados
                foreach ($carteira->getTransacoes() as $t) {
                    $mesChave = (new DateTime($t->getData()))->format('m');

                    if ($t->getTipo() == "Entrada") {
                        $resumoPorMes[$mesChave]['entradas'] += $t->getValor();
                    } elseif ($t->getTipo() == "Diario") {
                        $resumoPorMes[$mesChave]['diario'] += $t->getValor();
                    } else {
                        $resumoPorMes[$mesChave]['saidas'] += $t->getValor();
                    }
                }
                ?>

                <table class="table is-striped is-hoverable is-fullwidth mt-2">
                    <tr>
                        <th class="title is-4">Mês</th>
                        <th class="title is-4">Entradas</th>
                        <th class="title is-4">Saídas</th>
                        <th class="title is-4">Diário</th>
                        <th class="title is-4">Performance</th>
                        <th class="title is-4">Saldo Acumulado</th>
                    </tr>
                    <?php foreach ($resumoPorMes as $mesChave => $totais): ?>
                        <?php
                        $performance = $totais['entradas'] - ($totais['saidas'] + $totais['diario']);
                        $saldoAcumulado += $performance;
                        ?>
                        <tr>
                            <td class="subtitle is-5"><?= $nomesMeses[$mesChave] ?></td>
                            <td class="subtitle is-5 has-text-success">R$
                                <?= number_format($totais['entradas'], 2, ',', '.') ?></td>
                            <td class="subtitle is-5 has-text-danger">R$
                                <?= number_format($totais['saidas'], 2, ',', '.') ?></td>
                            <td class="subtitle is-5 has-text-warning">R$
                                <?= number_format($totais['diario'], 2, ',', '.') ?></td>
                            <td class="subtitle is-5">
                                <span
                                    class="tag <?= $performance >= 0 ? 'is-success' : 'is-danger' ?> has-text-primary-15-invert is-size-6">
                                    R$ <?= number_format($performance, 2, ',', '.') ?>
                                </span>
                            </td>
                            <td class="subtitle is-5">
                                <b>R$ <?= number_format($saldoAcumulado, 2, ',', '.') ?></b>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </section>
</body>

</html>