<?php

declare(strict_types=1);

abstract class Transacao
{
    protected int $id;
    protected float $valor;
    protected string $descricao;
    protected string $data;

    public function __construct(float $valor, string $descricao, string $data)
    {
        if ($valor <=  0) {
            throw new Exception("O valor deve ser maior que zero!");
        }
        $this->valor = $valor;
        $this->descricao = $descricao;
        $this->data = $data;
    }

public function getValor(): float{
    return $this->valor;
}

public function getDescricao(): string{
    return $this->descricao;
}

public function getData(): string{
    return $this->data;
}

abstract public function getTipo(): string;

public function getId(): int{
    return $this->id;
}

public function setId(int $id): void
{
    $this->id = $id;
}
}
?>