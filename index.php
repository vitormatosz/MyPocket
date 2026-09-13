<?php

require_once 'auten.php';
require_once 'classes/Transacao.php';
require_once 'classes/Diario.php';
require_once 'classes/Receita.php';
require_once 'classes/Despesa.php';
require_once 'classes/Carteira.php';
require_once 'recorrencias.php';

require_once 'database/conexao.php';

$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? date('m');

gerarRecorrenciasDoPeriodo($pdo, $_SESSION['usuario_id'], $ano, $mes);

require_once 'funcoes.php';
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

            <header class="mb-4 py-4 px-4" style="border-bottom: 1px solid #2a2a2a;">
                <div class="is-flex is-align-items-center is-justify-content-space-between">
                    <div class="is-flex is-align-items-center">
                        <img src="assets/wallet.png" alt="Logo" style="width: 100px; height: 110px;" class="mr-4">
                        <div>
                            <h1 class="title is-1 mb-1">MyPocket</h1>
                            <p class="subtitle is-5 mb-0">Controle financeiro pessoal</p>
                        </div>
                    </div>

                    <div class="is-flex is-align-items-center" style="gap: 12px;">
                        <span class="has-text-primary-15-invert">
                            <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
                        </span>
                        <a href="logout.php" class="button is-link">Sair</a>
                    </div>
                </div>
            </header>

            <form method="GET" class="is-flex is-align-items-center is-justify-content-end" style="gap: 15px;">
                <label class="label mb-0">Visualizando:</label>

                <div class="select">
                    <select name="ano" onchange="this.form.submit()">
                        <?php for ($a = 2024; $a <= 2030; $a++): ?>
                            <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>

            <div class="columns mt-3 mb-5">
                <div class="column is-4 is-flex">
                    <div
                        class="box has-background-link is-flex-grow-1 is-flex is-flex-direction-column is-justify-content-center">
                        <h2 class="subtitle has-text-primary-15-invert">Saldo Atual</h2>
                        <p class="title is-1">
                            R$ <?= number_format($saldoAtual, 2, ',', '.') ?>
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
                                <label class="label">Frequência</label>
                                <div class="control">
                                    <div class="select">
                                        <select name="frequencia" id="frequencia-select" required
                                            onchange="atualizarCamposRecorrencia()">
                                            <option value="unica">Única</option>
                                            <option value="fixa">Fixa</option>
                                            <option value="parc">Parcelada</option>
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
                            <div class="field" id="campo-data-fim" style="display: none;">
                                <label class="label">Repetir até</label>
                                <div class="control">
                                    <input class="input" type="date" name="data_fim" id="data-fim-input">
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

                        <div class="is-flex is-justify-content-space-between is-align-items-center is-flex-wrap-wrap mb-4"
                            style="gap: 12px;">
                            <div>
                                <h3 class="title is-3 mb-1">Transações</h3>
                                <p class="has-text-grey-light is-size-6">Consulte, filtre e gerencie suas movimentações</p>
                            </div>

                            <form method="GET" action="#transacoes-gerais"
                                class="is-flex is-align-items-center is-flex-wrap-wrap" style="gap: 8px;">
                                <input type="hidden" name="ano" value="<?= htmlspecialchars($ano) ?>">
                                <input type="hidden" name="mes" value="<?= htmlspecialchars($mes) ?>">

                                <div class="select is-small">
                                    <select name="mes_geral" onchange="this.form.submit()" aria-label="Filtrar por mês">
                                        <option value="">Todos os meses</option>
                                        <?php foreach ($nomesMeses as $numMes => $nomeMes): ?>
                                            <option value="<?= $numMes ?>" <?= $mesGeral === $numMes ? 'selected' : '' ?>>
                                                <?= $nomeMes ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="select is-small">
                                    <select name="filtro" onchange="this.form.submit()" aria-label="Filtrar por tipo">
                                        <option value="">Todos os tipos</option>
                                        <option value="Entrada" <?= $filtro === 'Entrada' ? 'selected' : '' ?>>Receitas
                                        </option>
                                        <option value="Diario" <?= $filtro === 'Diario' ? 'selected' : '' ?>>Diário
                                        </option>
                                        <option value="Saida" <?= $filtro === 'Saida' ? 'selected' : '' ?>>Despesas
                                        </option>
                                    </select>
                                </div>
                            </form>
                        </div>

                        <div style="max-height: 450px; overflow-y: auto;">
                            <table class="table is-fullwidth is-hoverable mt-2">
                                <thead>
                                    <tr>
                                        <th>Valor</th>
                                        <th>Tipo</th>
                                        <th>Recorrência</th>
                                        <th>Descrição</th>
                                        <th>Data</th>
                                        <th class="has-text-centered">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transacoesGerais)): ?>
                                        <tr>
                                            <td colspan="6" class="has-text-centered has-text-grey-light py-6">
                                                Nenhuma transação encontrada com esses filtros.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transacoesGerais as $t): ?>
                                            <?php $valorFormatado = number_format($t->getValor(), 2, ',', '.'); ?>
                                            <tr>
                                                <td class="has-text-weight-semibold">R$ <?= $valorFormatado ?></td>
                                                <td>
                                                    <?php if ($t->getTipo() === "Entrada"): ?>
                                                        <span class="tag is-success">Receita</span>
                                                    <?php elseif ($t->getTipo() === "Diario"): ?>
                                                        <span class="tag is-warning">Diário</span>
                                                    <?php else: ?>
                                                        <span class="tag is-danger">Despesa</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($t->getFrequenciaRecorrencia() === 'fixa'): ?>
                                                        <span class="tag is-link is-light">Fixa</span>
                                                    <?php elseif ($t->getFrequenciaRecorrencia() === 'parc'): ?>
                                                        <span class="tag is-info is-light">Parcelada</span>
                                                    <?php else: ?>
                                                        <span class="tag is-light">Única</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($t->getDescricao()) ?></td>
                                                <td><?= (new DateTime($t->getData()))->format('d/m/Y') ?></td>
                                                <td class="has-text-centered">
                                                    <a href="editar.php?id=<?= $t->getId() ?>"
                                                        class="button is-small is-warning">Editar</a>
                                                    <a href="delete.php?id=<?= $t->getId() ?>" class="button is-small is-danger"
                                                        onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6" id="extrato">
                <h3 class="title is-3">Extrato de <?= $ano ?></h3>

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


            <div class="mt-6" id="resumo-mensal">
                <div class="is-flex is-flex-wrap-wrap is-align-items-center is-justify-content-space-between mb-4"
                    style="gap: 12px;">
                    <h3 class="title is-3 mb-0">Resumo Mensal de <?= $ano ?></h3>

                    <div class="field is-grouped is-align-items-center mb-0">
                        <label class="label mb-0 mr-2">Filtrar transações</label>
                        <div class="control">
                            <div class="select">
                                <select id="filtroAcordeao" onchange="filtrarAcordeao()">
                                    <option value="Todos">Todas</option>
                                    <option value="Entrada">Receitas</option>
                                    <option value="Diario">Diário</option>
                                    <option value="Saida">Despesas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

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

                $transacoesPorMes = [];
                foreach ($carteira->getTransacoes() as $t) {
                    $mesChave = (new DateTime($t->getData()))->format('m');
                    $transacoesPorMes[$mesChave][] = $t;
                }

                $resumoPorMes = [];
                foreach ($nomesMeses as $numMes => $nome) {
                    $resumoPorMes[$numMes] = ['entradas' => 0, 'saidas' => 0, 'diario' => 0];
                }

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
                    <thead>
                        <tr>
                            <th class="title is-4">Mês</th>
                            <th class="title is-4">Entradas</th>
                            <th class="title is-4">Diário</th>
                            <th class="title is-4">Saídas</th>
                            <th class="title is-4">Performance</th>
                            <th class="title is-4">Saldo Acumulado</th>
                            <th class="title is-4 has-text-centered">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumoPorMes as $mesChave => $totais): ?>
                            <?php
                            $saidasTotais = $totais['saidas'] + $totais['diario'];
                            $performance = $totais['entradas'] - $saidasTotais;
                            $saldoAcumulado += $performance;
                            $transacoesDoMes = $transacoesPorMes[$mesChave] ?? [];
                            ?>
                            <tr>
                                <td class="subtitle is-5"><?= $nomesMeses[$mesChave] ?></td>
                                <td class="subtitle is-5 has-text-success">R$
                                    <?= number_format($totais['entradas'], 2, ',', '.') ?>
                                </td>
                                <td class="subtitle is-5 has-text-warning">R$
                                    <?= number_format($totais['diario'], 2, ',', '.') ?>
                                </td>
                                <td class="subtitle is-5 has-text-danger">R$
                                    <?= number_format($saidasTotais, 2, ',', '.') ?>
                                </td>
                                <td class="subtitle is-5">
                                    <span
                                        class="tag <?= $performance >= 0 ? 'is-success' : 'is-danger' ?> has-text-primary-15-invert is-size-6">
                                        R$ <?= number_format($performance, 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="subtitle is-5">
                                    <b>R$ <?= number_format($saldoAcumulado, 2, ',', '.') ?></b>
                                </td>
                                <td class="has-text-centered">
                                    <button class="button is-small is-link"
                                        onclick="toggleAcordeon('mes-<?= $mesChave ?>', this)">
                                        Ver Transações
                                    </button>
                                </td>
                            </tr>

                            <tr id="mes-<?= $mesChave ?>" style="display: none;">
                                <td colspan="7" style="background: transparent;">
                                    <div class="box my-3 has-background-black-bis" style="border: 1px solid #2f2f2f;">
                                        <div class="is-flex is-justify-content-space-between is-align-items-center is-flex-wrap-wrap mb-4"
                                            style="gap: 10px;">
                                            <div>
                                                <p class="title is-5 mb-1">Transações de
                                                    <?= $nomesMeses[$mesChave] ?>/<?= $ano ?>
                                                </p>
                                                <p class="is-size-6 has-text-grey-light">Movimentações registradas neste
                                                    mês.</p>
                                            </div>
                                            <div class="tags has-addons mb-0">
                                                <span class="tag is-dark">Total</span>
                                                <span class="tag is-link is-link"><?= count($transacoesDoMes) ?></span>
                                            </div>
                                        </div>

                                        <?php if (empty($transacoesDoMes)): ?>
                                            <div class="notification is-dark mb-0">Nenhuma transação registrada neste mês.</div>
                                        <?php else: ?>
                                            <div style="overflow-x: auto;">
                                                <table class="table is-fullwidth is-hoverable mb-0"
                                                    style="background: transparent;">
                                                    <thead>
                                                        <tr>
                                                            <th>Valor</th>
                                                            <th>Tipo</th>
                                                            <th>Recorrência</th>
                                                            <th>Descrição</th>
                                                            <th>Data</th>
                                                            <th class="has-text-centered">Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($transacoesDoMes as $t): ?>
                                                            <tr>

                                                                <td class="has-text-weight-semibold">R$
                                                                    <?= number_format($t->getValor(), 2, ',', '.') ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($t->getTipo() === "Entrada"): ?>
                                                                        <span class="tag is-success">Receita</span>
                                                                    <?php elseif ($t->getTipo() === "Diario"): ?>
                                                                        <span class="tag is-warning">Diário</span>
                                                                    <?php else: ?>
                                                                        <span class="tag is-danger">Despesa</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($t->getFrequenciaRecorrencia() === 'fixa'): ?>
                                                                        <span class="tag is-link is-light">Fixa</span>
                                                                    <?php elseif ($t->getFrequenciaRecorrencia() === 'parc'): ?>
                                                                        <span class="tag is-info is-light">Parcelada</span>
                                                                    <?php else: ?>
                                                                        <span class="tag is-light">Única</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($t->getDescricao()) ?></td>
                                                                <td><?= (new DateTime($t->getData()))->format('d/m/Y') ?></td>
                                                                <td class="has-text-centered">
                                                                    <a href="editar.php?id=<?= $t->getId() ?>"
                                                                        class="button is-small is-warning">Editar</a>
                                                                    <a href="delete.php?id=<?= $t->getId() ?>"
                                                                        class="button is-small is-danger"
                                                                        onclick="return confirm('Tem certeza?')">Excluir</a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="box mt-6">
                <h3 class="title is-3">Previsão de Saldo</h3>
                <p class="subtitle is-6 has-text-grey-light">Estimativa considerando recorrências que ainda vão
                    acontecer</p>

                <div class="columns is-multiline mt-3">
                    <?php foreach ($nomesMeses as $numMes => $nomeMes): ?>
                        <?php
                        $totais = $resumoPorMes[$numMes];

                        $entradaRealMes = $totais['entradas'];
                        $saidaRealMes = $totais['saidas'] + $totais['diario'];
                        $saldoRealMesCard = $entradaRealMes - $saidaRealMes;

                        $previsaoMes = calcularPrevisaoRecorrencias($pdo, $_SESSION['usuario_id'], $ano, $numMes);

                        $entradaPrevistaMes = $entradaRealMes + $previsaoMes['entrada'];
                        $saidaPrevistaMes = $saidaRealMes + $previsaoMes['saida'];
                        $saldoPrevistoMesCard = $entradaPrevistaMes - $saidaPrevistaMes;

                        $temPrevisaoExtra = $previsaoMes['entrada'] > 0 || $previsaoMes['saida'] > 0;
                        ?>
                        <div class="column is-3">
                            <div class="box has-background-black-ter" style="height: 100%;">
                                <p class="title is-5 mb-3"><?= $nomeMes ?></p>

                                <p class="mb-1 has-text-grey-light is-size-7">Saldo Previsto</p>
                                <p
                                    class="title is-4 mb-3 <?= $saldoPrevistoMesCard >= 0 ? 'has-text-success' : 'has-text-danger' ?>">
                                    R$ <?= number_format($saldoPrevistoMesCard, 2, ',', '.') ?>
                                </p>

                                <p class="is-size-7 has-text-success mb-1">
                                    Receita prevista: R$ <?= number_format($entradaPrevistaMes, 2, ',', '.') ?>
                                </p>
                                <p class="is-size-7 has-text-danger">
                                    Despesa prevista: R$ <?= number_format($saidaPrevistaMes, 2, ',', '.') ?>
                                </p>

                                <?php if ($temPrevisaoExtra): ?>
                                    <p class="is-size-7 has-text-grey-light mt-2">
                                        📈 inclui recorrências futuras
                                    </p>
                                <?php else: ?>
                                    <p class="is-size-7 has-text-grey-light mt-2">
                                        sem recorrências pendentes
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </section>
</body>
<script>
    function atualizarCamposRecorrencia() {
        const freq = document.getElementById('frequencia-select').value;
        const campo = document.getElementById('campo-data-fim');
        const input = document.getElementById('data-fim-input');

        if (freq === 'parc') {
            campo.style.display = 'block';
            input.required = true;
        } else {
            campo.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    function toggleAcordeon(idLinha, botao) {
        const linha = document.getElementById(idLinha);

        if (linha.style.display === 'none') {
            linha.style.display = '';
            botao.textContent = 'Ocultar';
            botao.classList.remove('is-link');
            botao.classList.add('is-light');
        } else {
            linha.style.display = 'none';
            botao.textContent = 'Ver Transações';
            botao.classList.remove('is-light');
            botao.classList.add('is-link');
        }
    }
</script>

</html>