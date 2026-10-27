<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\EmprestimoInvalidoException;
use App\Exceptions\LimiteEmprestimoException;
use App\Exceptions\MaterialIndisponivelException;
use App\Exceptions\UsuarioBloqueadoException;
use App\Interfaces\MultaInterface;
use App\Models\Aluno;
use App\Models\Emprestimo;
use App\Models\Historico;
use App\Models\Material;
use App\Models\Professor;
use App\Models\Usuario;
use App\Multas\MultaPadrao;
use App\Multas\MultaProfessor;
use DateTimeImmutable;

class EmprestimoService
{
    private int $proximoCodigo = 1;

    public function __construct(private Historico $historico) {}

    public function realizarEmprestimo(
        Usuario $usuario,
        Material $material,
        ?DateTimeImmutable $dataEmprestimo = null
    ): Emprestimo {
        $this->validarUsuario($usuario);
        if (!$material->consultarDisponibilidade()) {
            throw new MaterialIndisponivelException("Material '{$material->getTitulo()}' indisponível.");
        }

        $dataEmprestimo ??= new DateTimeImmutable('today');
        $diasPrazo = $material->calcularPrazoMaximoEmprestimo() + $usuario->diasAdicionaisPrazo();
        $dataPrevista = $dataEmprestimo->modify("+{$diasPrazo} days");

        // Atualizações só acontecem depois das validações.
        $material->emprestarExemplar();
        try {
            $usuario->registrarEmprestimoAtivo();
        } catch (\Throwable $e) {
            $material->devolverExemplar();
            throw $e;
        }

        $emprestimo = new Emprestimo(
            'EMP' . str_pad((string) $this->proximoCodigo++, 4, '0', STR_PAD_LEFT),
            $usuario,
            $material,
            $dataEmprestimo,
            $dataPrevista
        );
        $this->historico->registrar('EMPRÉSTIMO REALIZADO', $usuario, $material, 'Código: ' . $emprestimo->getCodigo());
        return $emprestimo;
    }

    public function registrarDevolucao(Emprestimo $emprestimo, ?DateTimeImmutable $dataDevolucao = null): array
    {
        if ($emprestimo->getStatus() === Emprestimo::DEVOLVIDO) {
            throw new EmprestimoInvalidoException('Este empréstimo já foi devolvido.');
        }
        if ($emprestimo->getStatus() === Emprestimo::CANCELADO) {
            throw new EmprestimoInvalidoException('Um empréstimo cancelado não pode ser devolvido.');
        }
        if ($emprestimo->getStatus() !== Emprestimo::ATIVO) {
            throw new EmprestimoInvalidoException('O empréstimo não está ativo.');
        }

        $dataDevolucao ??= new DateTimeImmutable('today');
        $diasAtraso = max(0, (int) $emprestimo->getDataPrevistaDevolucao()->diff($dataDevolucao)->format('%r%a'));
        $estrategiaMulta = $emprestimo->getUsuario() instanceof Professor
            ? new MultaProfessor()
            : new MultaPadrao();

        // Multa calculada por polimorfismo através da interface.
        $multa = $this->calcularMultaComEstrategia($estrategiaMulta, $diasAtraso);

        $emprestimo->getMaterial()->devolverExemplar();
        $emprestimo->getUsuario()->registrarDevolucao();
        $emprestimo->marcarDevolvido($dataDevolucao, $diasAtraso, $multa);

        if ($diasAtraso > 0) {
            $this->historico->registrar('MATERIAL DEVOLVIDO COM ATRASO', $emprestimo->getUsuario(), $emprestimo->getMaterial(), "{$diasAtraso} dia(s) de atraso");
            $this->historico->registrar('MULTA APLICADA', $emprestimo->getUsuario(), $emprestimo->getMaterial(), 'R$ ' . number_format($multa, 2, ',', '.'));
        }
        $this->historico->registrar('DEVOLUÇÃO REALIZADA', $emprestimo->getUsuario(), $emprestimo->getMaterial(), 'Código: ' . $emprestimo->getCodigo());

        return ['diasAtraso' => $diasAtraso, 'multa' => $multa, 'status' => $emprestimo->getStatus()];
    }

    public function cancelarEmprestimo(Emprestimo $emprestimo): void
    {
        if ($emprestimo->getStatus() !== Emprestimo::ATIVO) {
            throw new EmprestimoInvalidoException('Somente empréstimos ativos podem ser cancelados.');
        }
        $emprestimo->getMaterial()->devolverExemplar();
        $emprestimo->getUsuario()->registrarDevolucao();
        $emprestimo->marcarCancelado();
        $this->historico->registrar('EMPRÉSTIMO CANCELADO', $emprestimo->getUsuario(), $emprestimo->getMaterial());
    }

    private function validarUsuario(Usuario $usuario): void
    {
        if (!$usuario->isAtivo()) {
            throw new UsuarioBloqueadoException("O usuário '{$usuario->getNome()}' está bloqueado.");
        }
        if ($usuario->consultarQuantidadeEmprestimosAtivos() >= $usuario->consultarLimiteEmprestimos()) {
            throw new LimiteEmprestimoException("O usuário '{$usuario->getNome()}' atingiu o limite de empréstimos.");
        }
    }

    private function calcularMultaComEstrategia(MultaInterface $estrategia, int $diasAtraso): float
    {
        return $estrategia->calcularMulta($diasAtraso);
    }
}
