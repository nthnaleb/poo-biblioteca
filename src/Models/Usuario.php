<?php
declare(strict_types=1);

namespace App\Models;

abstract class Usuario
{
    protected string $matricula;
    protected string $nome;
    protected string $email;
    protected string $telefone;
    protected bool $ativo;
    protected int $quantidadeMaximaEmprestimos;
    protected int $quantidadeEmprestimosAtivos = 0;

    public function __construct(
        string $matricula,
        string $nome,
        string $email,
        string $telefone,
        bool $ativo,
        int $quantidadeMaximaEmprestimos
    ) {
        $this->matricula = $matricula;
        $this->nome = $nome;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->ativo = $ativo;
        $this->quantidadeMaximaEmprestimos = $quantidadeMaximaEmprestimos;
    }

    public function consultarDados(): string
    {
        return sprintf(
            '%s (%s) | E-mail: %s | Status: %s',
            $this->nome,
            $this->matricula,
            $this->email,
            $this->ativo ? 'ATIVO' : 'BLOQUEADO'
        );
    }

    public function podeRealizarNovoEmprestimo(): bool
    {
        return $this->ativo && $this->quantidadeEmprestimosAtivos < $this->quantidadeMaximaEmprestimos;
    }

    public function consultarLimiteEmprestimos(): int { return $this->quantidadeMaximaEmprestimos; }
    public function consultarQuantidadeEmprestimosAtivos(): int { return $this->quantidadeEmprestimosAtivos; }
    public function getMatricula(): string { return $this->matricula; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function isAtivo(): bool { return $this->ativo; }
    public function bloquear(): void { $this->ativo = false; }
    public function desbloquear(): void { $this->ativo = true; }

    public function registrarEmprestimoAtivo(): void
    {
        if ($this->quantidadeEmprestimosAtivos >= $this->quantidadeMaximaEmprestimos) {
            throw new \LogicException('O limite de empréstimos já foi atingido.');
        }
        $this->quantidadeEmprestimosAtivos++;
    }

    public function registrarDevolucao(): void
    {
        if ($this->quantidadeEmprestimosAtivos <= 0) {
            throw new \LogicException('Não há empréstimos ativos para reduzir.');
        }
        $this->quantidadeEmprestimosAtivos--;
    }

    abstract public function diasAdicionaisPrazo(): int;
}
