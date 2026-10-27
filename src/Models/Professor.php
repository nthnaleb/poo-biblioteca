<?php
declare(strict_types=1);

namespace App\Models;

class Professor extends Usuario
{
    public function __construct(string $matricula, string $nome, string $email, string $telefone = '', bool $ativo = true)
    {
        parent::__construct($matricula, $nome, $email, $telefone, $ativo, 5);
    }

    public function diasAdicionaisPrazo(): int { return 7; }
}
