<?php
declare(strict_types=1);

namespace App\Interfaces;

interface MultaInterface
{
    public function calcularMulta(int $diasAtraso): float;
}
