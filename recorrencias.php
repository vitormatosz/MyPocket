<?php

function gerarRecorrenciasDoPeriodo(PDO $pdo, int $idUsuario, string $ano, string $mes): void
{
    // NUNCA gera nada no futuro - isso é o que resolve o problema do saldo
    if ($ano . $mes > date('Ym')) {
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM recorrencias WHERE id_usuario = :id_usuario AND ativa = 1");
    $stmt->execute(['id_usuario' => $idUsuario]);
    $recorrencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recorrencias as $r) {
        if ($r['tipo'] === 'Diario') {
            gerarDiasDoMes($pdo, $r, $ano, $mes);
        } else {
            gerarMensal($pdo, $r, $ano, $mes);
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
        return; // já foi gerado, não duplica
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

    $periodo = new DatePeriod($inicio, new DateInterval('P1D'), (clone $limite)->modify('+1 day'));

    foreach ($periodo as $dia) {
        $dataStr = $dia->format('Y-m-d');

        $check = $pdo->prepare("SELECT id FROM transacoes WHERE id_recorrencia = :id_recorrencia AND data = :data");
        $check->execute(['id_recorrencia' => $r['id'], 'data' => $dataStr]);
        if ($check->fetch()) {
            continue;
        }

        inserirTransacaoDaRecorrencia($pdo, $r, $dataStr);
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

    // Só calcula previsão para o mês atual ou futuro
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

        // Recorrência ainda não começou
        if ($mesInicio < new DateTime($inicio->format('Y-m-01'))) {
            continue;
        }

        // Recorrência já terminou
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

            // No mês atual, considera apenas até hoje
            if ($ano . $mes === date('Ym')) {
                $fimDia = min($fimDia, new DateTime());
            }

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