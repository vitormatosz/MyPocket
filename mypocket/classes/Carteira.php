<?php

declare(strict_types=1);

require_once "Despesa.php";
require_once "Receita.php";

class Carteira{
    private float $saldo;
    private array $transacoes;
    public function __construct(){
        $this->saldo = 0;
        $this->transacoes = [];
    }

    public function addTransacoes(Transacao $transacao): void{
        if($transacao instanceof Receita){
            $this->saldo += $transacao->getValor();
        }

        if($transacao instanceof Despesa){

            if($transacao->getValor() > $this->saldo){
               throw new Exception("Saldo Insuficente!");
            }

            $this->saldo -= $transacao->getValor();
        }

    $this->transacoes[] = $transacao;

    }

    public function getTransacoes(): array{
        return $this->transacoes;
    }
}
?>