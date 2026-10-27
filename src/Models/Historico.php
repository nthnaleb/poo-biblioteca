<?php
declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class Historico
{
    /** @var array<int, array{data:string,hora:string,acao:string,usuario:string,material:string,observacao:string}> */
    private array $registros = [];

    public function registrar(string $acao, Usuario $usuario, Material $material, string $observacao = ''): void
    {
        $agora = new DateTimeImmutable();
        $this->registros[] = [
            'data' => $agora->format('d/m/Y'),
            'hora' => $agora->format('H:i:s'),
            'acao' => $acao,
            'usuario' => $usuario->getNome(),
            'material' => $material->getTitulo(),
            'observacao' => $observacao,
        ];
    }

    public function listarRegistros(): array { return $this->registros; }

    public function exibir(): string
    {
        if ($this->registros === []) {
            return "Histórico vazio.\n";
        }

        $saida = '';
        foreach ($this->registros as $registro) {
            $saida .= sprintf(
                "[%s %s] %s | Usuário: %s | Material: %s%s\n",
                $registro['data'],
                $registro['hora'],
                $registro['acao'],
                $registro['usuario'],
                $registro['material'],
                $registro['observacao'] !== '' ? ' | ' . $registro['observacao'] : ''
            );
        }
        return $saida;
    }
}
