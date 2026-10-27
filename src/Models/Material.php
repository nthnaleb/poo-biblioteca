<?php
declare(strict_types=1);

namespace App\Models;

use App\Exceptions\MaterialIndisponivelException;

abstract class Material
{
    protected string $codigo;
    protected string $titulo;
    protected string $autor;
    protected int $anoPublicacao;
    protected int $quantidadeExemplares;
    protected int $quantidadeDisponivel;

    public function __construct(
        string $codigo,
        string $titulo,
        string $autor,
        int $anoPublicacao,
        int $quantidadeExemplares
    ) {
        if ($quantidadeExemplares < 0) {
            throw new \InvalidArgumentException('A quantidade de exemplares não pode ser negativa.');
        }

        $this->codigo = $codigo;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->anoPublicacao = $anoPublicacao;
        $this->quantidadeExemplares = $quantidadeExemplares;
        $this->quantidadeDisponivel = $quantidadeExemplares;
    }

    public function consultarDisponibilidade(): bool
    {
        return $this->consultarQuantidadeDisponivel() > 0;
    }

    public function emprestarExemplar(): void
    {
        if (!$this->consultarDisponibilidade()) {
            throw new MaterialIndisponivelException(
                "O material '{$this->titulo}' não possui exemplares/acessos disponíveis."
            );
        }
        $this->quantidadeDisponivel--;
    }

    public function devolverExemplar(): void
    {
        if ($this->quantidadeDisponivel >= $this->quantidadeExemplares) {
            throw new \LogicException('A devolução ultrapassaria a quantidade cadastrada.');
        }
        $this->quantidadeDisponivel++;
    }

    public function consultarQuantidadeDisponivel(): int
    {
        return $this->quantidadeDisponivel;
    }

    public function calcularPrazoMaximoEmprestimo(): int
    {
        return $this->prazoMaximoEmprestimo();
    }

    abstract protected function prazoMaximoEmprestimo(): int;

    public function exibirInformacoes(): string
    {
        return sprintf(
            '%s | Código: %s | Autor: %s | Ano: %d | Disponíveis: %d/%d',
            $this->titulo,
            $this->codigo,
            $this->autor,
            $this->anoPublicacao,
            $this->consultarQuantidadeDisponivel(),
            $this->quantidadeExemplares
        );
    }

    public function getCodigo(): string { return $this->codigo; }
    public function getTitulo(): string { return $this->titulo; }
    public function getAutor(): string { return $this->autor; }
    public function getAnoPublicacao(): int { return $this->anoPublicacao; }
    public function getQuantidadeExemplares(): int { return $this->quantidadeExemplares; }
}
