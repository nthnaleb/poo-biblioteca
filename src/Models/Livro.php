<?php
declare(strict_types=1);

namespace App\Models;

class Livro extends Material
{
    public function __construct(
        string $codigo,
        string $titulo,
        string $autor,
        int $anoPublicacao,
        int $quantidadeExemplares,
        private string $isbn,
        private int $numeroPaginas,
        private string $editora,
        private string $genero
    ) {
        parent::__construct($codigo, $titulo, $autor, $anoPublicacao, $quantidadeExemplares);
    }

    protected function prazoMaximoEmprestimo(): int { return 14; }

    public function getIsbn(): string { return $this->isbn; }
    public function getNumeroPaginas(): int { return $this->numeroPaginas; }
    public function getEditora(): string { return $this->editora; }
    public function getGenero(): string { return $this->genero; }

    public function exibirInformacoes(): string
    {
        return parent::exibirInformacoes() . " | ISBN: {$this->isbn} | Editora: {$this->editora} | Gênero: {$this->genero}";
    }
}
