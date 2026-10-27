<?php
declare(strict_types=1);

namespace App\Models;

class EBook extends Material
{
    public function __construct(
        string $codigo,
        string $titulo,
        string $autor,
        int $anoPublicacao,
        int $maximoAcessosSimultaneos,
        private string $formatoArquivo,
        private float $tamanhoArquivo,
        private string $linkAcesso
    ) {
        if ($maximoAcessosSimultaneos < 0) {
            throw new \InvalidArgumentException('O limite de acessos não pode ser negativo.');
        }
        // Para e-book, a quantidade cadastrada representa acessos simultâneos.
        parent::__construct($codigo, $titulo, $autor, $anoPublicacao, $maximoAcessosSimultaneos);
    }

    protected function prazoMaximoEmprestimo(): int { return 21; }

    public function getFormatoArquivo(): string { return $this->formatoArquivo; }
    public function getTamanhoArquivo(): float { return $this->tamanhoArquivo; }
    public function getLinkAcesso(): string { return $this->linkAcesso; }
    public function getMaximoAcessosSimultaneos(): int { return $this->quantidadeExemplares; }

    public function exibirInformacoes(): string
    {
        return parent::exibirInformacoes() . " | Formato: {$this->formatoArquivo} | Tamanho: {$this->tamanhoArquivo} MB | Link: {$this->linkAcesso}";
    }
}
