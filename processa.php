<?php
declare(strict_types=1);

require_once "classes/Transacao.php";
require_once "classes/Receita.php";
require_once "classes/Despesa.php";
require_once "classes/Diario.php";
require_once "classes/Carteira.php";

require_once 'auten.php'; 
require_once 'database/conexao.php';
require_once 'recorrencias.php';

function criarTransacao(string $tipo, float $valor, string $descricao, string $data): Transacao
{
    if ($tipo === "Entrada") {
        return new Receita($valor, $descricao, $data);
    }

    if ($tipo === "Diario") {
        return new Diario($valor, $descricao, $data);
    }

    return new Despesa($valor, $descricao, $data);
}

$carteira = new Carteira();

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id_usuario = :id_usuario AND cancelada = 0 AND data <= CURDATE()");
$stmt->execute(['id_usuario' => $_SESSION['usuario_id']]);

foreach ($stmt as $param) {
    $carteira->carregarTransacao(
        criarTransacao($param['tipo'], (float) $param['valor'], $param['descricao'], $param['data'])
    );
}

try {
    $tipo = $_POST['tipo'] ?? '';
    $descricao = trim($_POST['descricao'] ?? '');
    $valor = (float) ($_POST['valor'] ?? 0);
    $data = $_POST['data'] ?? '';
    
    $frequencia = $_POST['frequencia'] ?? 'unica';
    $dataFim = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $hoje = date('Y-m-d');

    if (empty($data)) {
        throw new Exception("Data inválida");
    }

    if (!in_array($tipo, ['Entrada', 'Saida', 'Diario'], true)) {
        throw new Exception("Tipo inválido");
    }

    if (!in_array($frequencia, ['unica', 'fixa', 'parc'], true)) {
        throw new Exception("Frequência inválida");
    }

    if ($frequencia === 'parc' && $dataFim === null) {
        throw new Exception("Informe até quando a transação parcelada se repete!");
    }

    $transacao = criarTransacao($tipo, $valor, $descricao, $data);

    if ($frequencia === 'unica') {

        if ($data <= $hoje) {
            $carteira->addTransacoes($transacao);
        }

        $stmt = $pdo->prepare("
        INSERT INTO transacoes (valor, tipo, descricao, data, id_usuario)
        VALUES (:valor, :tipo, :descricao, :data, :id_usuario)
    ");
        $stmt->execute([
            'valor' => $valor,
            'tipo' => $tipo,
            'descricao' => $descricao,
            'data' => $data,
            'id_usuario' => $_SESSION['usuario_id']
        ]);

    } else {
        $diaVencimento = $tipo === 'Diario' ? null : (int) (new DateTime($data))->format('d');

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
        INSERT INTO recorrencias (descricao, valor, tipo, frequencia, data_inicio, data_fim, dia_vencimento, id_usuario)
        VALUES (:descricao, :valor, :tipo, :frequencia, :data_inicio, :data_fim, :dia_vencimento, :id_usuario)
    ");
            $stmt->execute([
                'descricao' => $descricao,
                'valor' => $valor,
                'tipo' => $tipo,
                'frequencia' => $frequencia,
                'data_inicio' => $data,
                'data_fim' => $frequencia === 'fixa' ? null : $dataFim,
                'dia_vencimento' => $diaVencimento,
                'id_usuario' => $_SESSION['usuario_id']
            ]);

            $idRecorrencia = $pdo->lastInsertId();

            if ($data <= $hoje) {
                $stmt = $pdo->prepare("
        INSERT INTO transacoes (valor, tipo, descricao, data, id_usuario, id_recorrencia, data_recorrencia)
        VALUES (:valor, :tipo, :descricao, :data, :id_usuario, :id_recorrencia, :data_recorrencia)
    ");
                $stmt->execute([
                    'valor' => $valor,
                    'tipo' => $tipo,
                    'descricao' => $descricao,
                    'data' => $data,
                    'id_usuario' => $_SESSION['usuario_id'],
                    'id_recorrencia' => $idRecorrencia,
                    'data_recorrencia' => $data
                ]);
            }

            gerarRecorrenciasDoPeriodo($pdo, (int) $_SESSION['usuario_id']);

            $stmtSaldo = $pdo->prepare("SELECT tipo, valor FROM transacoes WHERE id_usuario = :id_usuario AND cancelada = 0 AND data <= CURDATE()");
            $stmtSaldo->execute(['id_usuario' => $_SESSION['usuario_id']]);

            $saldo = 0;

            foreach ($stmtSaldo as $row) {
                if ($row['tipo'] === 'Entrada') {
                    $saldo += (float) $row['valor'];
                } else {
                    $saldo -= (float) $row['valor'];
                }
            }

            if ($tipo !== 'Entrada' && $saldo< 0) {
                throw new Exception("Saldo insuficiente: as parcelas passadas dessa recorrência deixariam seu saldo negativo!");
            }

            $pdo->commit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    $_SESSION['mensagem'] = "Transação cadastrada com sucesso!";

} catch (Exception $e) {
    $_SESSION['erro'] = $e->getMessage();
}

header("Location: index.php");
exit;