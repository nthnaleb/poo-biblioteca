<?php
declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class Emprestimo
{
    public const ATIVO = 'ATIVO';
    public const DEVOLVIDO = 'DEVOLVIDO';
    public const ATRASADO = 'ATRASADO';
    public const CANCELADO = 'CANCELADO';

    private string $status = self::ATIVO;
    private ?DateTimeImmutable $dataRealDevolucao = null;
    private float $multa = 0.0;

    public function __construct(
        private string $codigo,
        private Usuario $usuario,
        private Material $material,
        private DateTimeImmutable $dataEmprestimo,
        private DateTimeImmutable $dataPrevistaDevolucao
    ) {}

    public function marcarDevolvido(DateTimeImmutable $data, int $diasAtraso, float $multa): void
    {
        $this->dataRealDevolucao = $data;
        $this->multa = $multa;
        $this->status = self::DEVOLVIDO;
    }

    public function marcarCancelado(): void { $this->status = self::CANCELADO; }
    public function getCodigo(): string { return $this->codigo; }
    public function getUsuario(): Usuario { return $this->usuario; }
    public function getMaterial(): Material { return $this->material; }
    public function getDataEmprestimo(): DateTimeImmutable { return $this->dataEmprestimo; }
    public function getDataPrevistaDevolucao(): DateTimeImmutable { return $this->dataPrevistaDevolucao; }
    public function getDataRealDevolucao(): ?DateTimeImmutable { return $this->dataRealDevolucao; }
    public function getStatus(): string { return $this->status; }
    public function getMulta(): float { return $this->multa; }
}
