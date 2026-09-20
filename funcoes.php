<?php

$stmtRecorrencias = $pdo->prepare("SELECT id, frequencia FROM recorrencias WHERE id_usuario = :id_usuario");
$stmtRecorrencias->execute(['id_usuario' => $_SESSION['usuario_id']]);
$frequenciaPorRecorrencia = [];
foreach ($stmtRecorrencias as $rec) {
    $frequenciaPorRecorrencia[$rec['id']] = $rec['frequencia'];
}

function definirFrequencia(Transacao $t, array $row, array $frequenciaPorRecorrencia): void
{
    if (!empty($row['id_recorrencia']) && isset($frequenciaPorRecorrencia[$row['id_recorrencia']])) {
        $t->setFrequenciaRecorrencia($frequenciaPorRecorrencia[$row['id_recorrencia']]);
    } else {
        $t->setFrequenciaRecorrencia('unica');
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

$carteiraGeral = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario AND cancelada = 0");
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
    definirFrequencia($t, $row, $frequenciaPorRecorrencia);
    $carteiraGeral->carregarTransacao($t);
}

$stmtSaldo = $pdo->prepare(" SELECT tipo, valor FROM transacoes WHERE id_usuario = :id_usuario AND cancelada = 0 AND data <= CURDATE()");
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

$carteira = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario AND cancelada = 0 AND YEAR(data) = :ano");
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
    definirFrequencia($t, $row, $frequenciaPorRecorrencia);
    $carteira->carregarTransacao($t);
}

$carteiraMes = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario AND cancelada = 0 AND YEAR(data) = :ano AND MONTH(data) = :mes");
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
    definirFrequencia($t, $row, $frequenciaPorRecorrencia);
    $carteiraMes->carregarTransacao($t);
}
