<?php
declare(strict_types=1);

namespace App\Multas;

use App\Interfaces\MultaInterface;

class MultaPadrao implements MultaInterface
{
    public function calcularMulta(int $diasAtraso): float
    {
        return max(0, $diasAtraso) * 2.00;
    }
}
