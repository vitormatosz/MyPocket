<?php

function gerarRecorrenciasDoPeriodo(PDO $pdo, int $idUsuario, string $ano, string $mes): void
{
    $stmt = $pdo->prepare("SELECT * FROM recorrencias WHERE id_usuario = :id_usuario AND ativa = 1");

    $stmt->execute([
        'id_usuario' => $idUsuario
    ]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $recorrencia) {

        $mes = new DateTime($recorrencia['data_inicio']);
        $mes->modify('first day of this month');
        $fim = new DateTime('first day of this month');

        if (!empty($recorrencia['data_fim'])) {
            $dataFim = new DateTime($recorrencia['data_fim']);
            $dataFim->modify('first day of this month');

            if ($dataFim < $fim) {
                $fim = $dataFim;
            }
        }

        while ($mes <= $fim) {
            $anoGerado = $mes->format('Y');
            $mesGerado = $mes->format('m');

            if ($recorrencia['tipo'] === 'Diario') {
                gerarDiario($pdo,$recorrencia,$anoGerado,$mesGerado);
            } else {
                gerarMensal( $pdo,$recorrencia, $anoGerado,$mesGerado);
            }

            $mes->modify('+1 month');
        }
    }
}

function gerarMensal(PDO $pdo, array $recorrencia, string $ano, string $mes): void
{
    $competencia = new DateTime("$ano-$mes-01");

    $ultimoDia = (int) $competencia->format('t');
    $dia = (int) $recorrencia['dia_vencimento'];
    $dia = min($dia, $ultimoDia);

    $data = sprintf('%s-%s-%02d', $ano, $mes, $dia);

    if ($data > date('Y-m-d')) {
        return;
    }

    if ($data < $recorrencia['data_inicio']) {
        return;
    }

    if (!empty($recorrencia['data_fim']) && $data > $recorrencia['data_fim']) {
        return;
    }

    if (ocorrenciaExiste($pdo, (int) $recorrencia['id'], $data)) {
        return;
    }

    inserirTransacaoDaRecorrencia($pdo, $recorrencia, $data);
}


function gerarDiario(PDO $pdo, array $recorrencia, string $ano, string $mes): void
{
    $primeiroDia = new DateTime("$ano-$mes-01");

    $ultimoDia = clone $primeiroDia;
    $ultimoDia->modify('last day of this month');

    $dia = new DateTime($recorrencia['data_inicio']);

    if ($dia < $primeiroDia) {
        $dia = clone $primeiroDia;
    }

    $fim = clone $ultimoDia;

    if (empty($recorrencia['data_fim'])) {

        $hoje = new DateTime();

        if ($hoje < $fim) {
            $fim = $hoje;
        }

    } else {

        $dataFim = new DateTime($recorrencia['data_fim']);

        if ($dataFim < $fim) {
            $fim = $dataFim;
        }
    }

    if ($dia > $fim) {
        return;
    }

    while ($dia <= $fim) {

        $data = $dia->format('Y-m-d');

        if (!ocorrenciaExiste($pdo, (int) $recorrencia['id'],$data)) {
            inserirTransacaoDaRecorrencia($pdo,$recorrencia,$data);
        }
        $dia->modify('+1 day');
    }
}

function ocorrenciaExiste(PDO $pdo, int $idRecorrencia, string $data): bool {
    $stmt = $pdo->prepare("SELECT id FROM transacoes WHERE id_recorrencia = :id_recorrencia AND data_recorrencia = :data_recorrencia");

    $stmt->execute([
        'id_recorrencia' => $idRecorrencia,
        'data_recorrencia' => $data
    ]);

    return $stmt->fetch() !== false;
}

function inserirTransacaoDaRecorrencia(PDO $pdo, array $recorrencia, string $data): void {

    $stmt = $pdo->prepare("INSERT INTO transacoes (valor, tipo, descricao, data, id_usuario, id_recorrencia, data_recorrencia) 
        VALUES (:valor, :tipo, :descricao,:data,:id_usuario, :id_recorrencia,:data_recorrencia)");

    $stmt->execute([
        'valor' => $recorrencia['valor'],
        'tipo' => $recorrencia['tipo'],
        'descricao' => $recorrencia['descricao'],
        'data' => $data,
        'id_usuario' => $recorrencia['id_usuario'],
        'id_recorrencia' => $recorrencia['id'],
        'data_recorrencia' => $data
    ]);
}

function calcularPrevisaoRecorrencias(PDO $pdo, int $idUsuario, string $ano, string $mes): array {
    $entrada = 0;
    $saida = 0;

    $mesEscolhido = new DateTime("$ano-$mes-01");
    $mesAtual = new DateTime('first day of this month');

    if ($mesEscolhido < $mesAtual) {
        return [
            'entrada' => 0,
            'saida' => 0
        ];
    }

    $stmt = $pdo->prepare("SELECT * FROM recorrencias WHERE id_usuario = :id_usuario AND ativa = 1");

    $stmt->execute([
        'id_usuario' => $idUsuario
    ]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $recorrencia) {

        $inicio = new DateTime($recorrencia['data_inicio']);

        $mesInicio = new DateTime(
            $inicio->format('Y-m-01')
        );

        if ($mesEscolhido < $mesInicio) {
            continue;
        }

        if (!empty($recorrencia['data_fim'])) {
            $mesFim = new DateTime($recorrencia['data_fim']);
            $mesFim->modify('first day of this month');

            if ($mesEscolhido > $mesFim) {
                continue;
            }
        }

        if ($recorrencia['tipo'] === 'Diario') {
            calcularPrevisaoDiaria($pdo, $recorrencia, $ano, $mes, $entrada, $saida);
        } else {
            calcularPrevisaoMensal($pdo,$recorrencia,$ano,$mes,$entrada,$saida);
        }
    }
    return [
        'entrada' => $entrada,
        'saida' => $saida
    ];
}

function calcularPrevisaoDiaria(PDO $pdo,array $recorrencia,string $ano,string $mes,float &$entrada,float &$saida): void {

    $dia = new DateTime("$ano-$mes-01");
    $inicio = new DateTime($recorrencia['data_inicio']);

    if ($inicio > $dia) {
        $dia = $inicio;
    }

    $fim = new DateTime("$ano-$mes-01");
    $fim->modify('last day of this month');

    if (!empty($recorrencia['data_fim'])) {

        $dataFim = new DateTime($recorrencia['data_fim']);

        if ($dataFim < $fim) {
            $fim = $dataFim;
        }
    }

    while ($dia <= $fim) {
        $data = $dia->format('Y-m-d');
        if (!ocorrenciaExiste( $pdo,(int) $recorrencia['id'],$data)
        ) {
            if ($recorrencia['tipo'] === 'Entrada') {
                $entrada += (float) $recorrencia['valor'];
            } else {
                $saida += (float) $recorrencia['valor'];
            }
        }
        $dia->modify('+1 day');
    }
}

function calcularPrevisaoMensal(PDO $pdo,array $recorrencia,string $ano,string $mes,float &$entrada,float &$saida): void {

    $mesEscolhido = new DateTime("$ano-$mes-01");

    $ultimoDia = (int) $mesEscolhido->format('t');
    $dia = (int) $recorrencia['dia_vencimento'];
    $dia = min($dia, $ultimoDia);

    $data = sprintf('%s-%s-%02d',$ano,$mes,$dia);

    if ($data < $recorrencia['data_inicio']) {
        return;
    }

    if (!empty($recorrencia['data_fim']) &&  $data > $recorrencia['data_fim']) {
        return;
    }

    if (ocorrenciaExiste($pdo,(int) $recorrencia['id'], $data)
    ) {
        return;
    }

    if ($recorrencia['tipo'] === 'Entrada') {
        $entrada += (float) $recorrencia['valor'];

    } else {
        $saida += (float) $recorrencia['valor'];
    }
}
?>