<?php
declare(strict_types=1);

namespace App\Models;

class Aluno extends Usuario
{
    public function __construct(string $matricula, string $nome, string $email, string $telefone = '', bool $ativo = true)
    {
        parent::__construct($matricula, $nome, $email, $telefone, $ativo, 3);
    }

    public function diasAdicionaisPrazo(): int { return 0; }
}
