<?php
declare(strict_types=1);

namespace App\Models;

class Revista extends Material
{
    public function __construct(
        string $codigo,
        string $titulo,
        string $autor,
        int $anoPublicacao,
        int $quantidadeExemplares,
        private int $edicao,
        private int $volume,
        private string $mesPublicacao
    ) {
        parent::__construct($codigo, $titulo, $autor, $anoPublicacao, $quantidadeExemplares);
    }

    protected function prazoMaximoEmprestimo(): int { return 7; }

    public function getEdicao(): int { return $this->edicao; }
    public function getVolume(): int { return $this->volume; }
    public function getMesPublicacao(): string { return $this->mesPublicacao; }

    public function exibirInformacoes(): string
    {
        return parent::exibirInformacoes() . " | Edição: {$this->edicao} | Volume: {$this->volume} | Mês: {$this->mesPublicacao}";
    }
}
