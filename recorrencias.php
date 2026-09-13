<?php

function gerarRecorrenciasDoPeriodo(PDO $pdo, int $idUsuario, string $ano, string $mes): void
{
    $stmt = $pdo->prepare("SELECT * FROM recorrencias WHERE id_usuario = :id_usuario AND ativa = 1");
    $stmt->execute(['id_usuario' => $idUsuario]);
    $recorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $mesAtual = new DateTime('first day of this month');

    foreach ($recorrencias as $r) {
        $cursor = new DateTime($r['data_inicio']);
        $cursor->modify('first day of this month');

        $fimGeracao = clone $mesAtual;
        if ($r['data_fim']) {
            $dataFimMes = new DateTime($r['data_fim']);
            $dataFimMes->modify('first day of this month');
            if ($dataFimMes < $fimGeracao) {
                $fimGeracao = $dataFimMes;
            }
        }

        while ($cursor <= $fimGeracao) {
            $anoCursor = $cursor->format('Y');
            $mesCursor = $cursor->format('m');

            if ($r['tipo'] === 'Diario') {
                gerarDiasDoMes($pdo, $r, $anoCursor, $mesCursor);
            } else {
                gerarMensal($pdo, $r, $anoCursor, $mesCursor);
            }

            $cursor->modify('+1 month');
        }
    }
}

function gerarMensal(PDO $pdo, array $r, string $ano, string $mes): void
{
    $inicio = new DateTime($r['data_inicio']);
    $competencia = new DateTime("$ano-$mes-01");

    if ($competencia < new DateTime($inicio->format('Y-m-01'))) {
        return;
    }
    if ($r['data_fim'] && $competencia > new DateTime($r['data_fim'])) {
        return;
    }

    $check = $pdo->prepare("
        SELECT id FROM transacoes
        WHERE id_recorrencia = :id_recorrencia AND YEAR(data) = :ano AND MONTH(data) = :mes
    ");
    $check->execute(['id_recorrencia' => $r['id'], 'ano' => $ano, 'mes' => $mes]);
    if ($check->fetch()) {
        return;
    }

    $diaMax = (int) $competencia->format('t');
    $dia = min((int) $r['dia_vencimento'], $diaMax);
    $data = sprintf('%s-%s-%02d', $ano, $mes, $dia);

    inserirTransacaoDaRecorrencia($pdo, $r, $data);
}

function gerarDiasDoMes(PDO $pdo, array $r, string $ano, string $mes): void
{
    $inicio = max(new DateTime($r['data_inicio']), new DateTime("$ano-$mes-01"));
    $fimDoMes = new DateTime("$ano-$mes-01");
    $fimDoMes->modify('last day of this month');

    $limite = $r['data_fim'] ? min($fimDoMes, new DateTime($r['data_fim'])) : min($fimDoMes, new DateTime());

    if ($inicio > $limite) {
        return;
    }

    $dia = clone $inicio;
    while ($dia <= $limite) {
        $dataStr = $dia->format('Y-m-d');

        $check = $pdo->prepare("SELECT id FROM transacoes WHERE id_recorrencia = :id_recorrencia AND data = :data");
        $check->execute(['id_recorrencia' => $r['id'], 'data' => $dataStr]);
        if (!$check->fetch()) {
            inserirTransacaoDaRecorrencia($pdo, $r, $dataStr);
        }

        $dia->modify('+1 day');
    }
}
function inserirTransacaoDaRecorrencia(PDO $pdo, array $r, string $data): void
{
    $stmt = $pdo->prepare("
        INSERT INTO transacoes (valor, tipo, descricao, data, id_usuario, id_recorrencia)
        VALUES (:valor, :tipo, :descricao, :data, :id_usuario, :id_recorrencia)
    ");
    $stmt->execute([
        'valor' => $r['valor'],
        'tipo' => $r['tipo'],
        'descricao' => $r['descricao'],
        'data' => $data,
        'id_usuario' => $r['id_usuario'],
        'id_recorrencia' => $r['id']
    ]);
}

function calcularPrevisaoRecorrencias(PDO $pdo, int $idUsuario, string $ano, string $mes): array
{
    $entrada = 0;
    $saida = 0;

    if ($ano . $mes < date('Ym')) {
        return ['entrada' => 0, 'saida' => 0];
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM recorrencias
        WHERE id_usuario = :id_usuario
        AND ativa = 1
    ");

    $stmt->execute(['id_usuario' => $idUsuario]);

    foreach ($stmt as $r) {

        $inicio = new DateTime($r['data_inicio']);
        $mesInicio = new DateTime("$ano-$mes-01");

        if ($mesInicio < new DateTime($inicio->format('Y-m-01'))) {
            continue;
        }

        if ($r['data_fim'] && $mesInicio > new DateTime($r['data_fim'])) {
            continue;
        }

        if ($r['tipo'] === 'Diario') {

            $inicioDia = max(
                new DateTime($r['data_inicio']),
                new DateTime("$ano-$mes-01")
            );

            $fimDia = new DateTime("$ano-$mes-01");
            $fimDia->modify('last day of this month');

            if ($r['data_fim']) {
                $fimDia = min($fimDia, new DateTime($r['data_fim']));
            }

            $periodo = new DatePeriod(
                $inicioDia,
                new DateInterval('P1D'),
                (clone $fimDia)->modify('+1 day')
            );

            foreach ($periodo as $dia) {

                $data = $dia->format('Y-m-d');

                $check = $pdo->prepare("
                    SELECT id
                    FROM transacoes
                    WHERE id_recorrencia = :id
                    AND data = :data
                ");

                $check->execute([
                    'id' => $r['id'],
                    'data' => $data
                ]);

                if ($check->fetch()) {
                    continue;
                }

                if ($r['tipo'] === 'Entrada') {
                    $entrada += $r['valor'];
                } else {
                    $saida += $r['valor'];
                }
            }

        } else {

            $check = $pdo->prepare("
                SELECT id
                FROM transacoes
                WHERE id_recorrencia = :id
                AND YEAR(data) = :ano
                AND MONTH(data) = :mes
            ");

            $check->execute([
                'id' => $r['id'],
                'ano' => $ano,
                'mes' => $mes
            ]);

            if ($check->fetch()) {
                continue;
            }

            if ($r['tipo'] === 'Entrada') {
                $entrada += $r['valor'];
            } else {
                $saida += $r['valor'];
            }
        }
    }

    return [
        'entrada' => $entrada,
        'saida' => $saida
    ];
}

?>