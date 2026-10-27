<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/autoload.php';

use App\Exceptions\EmprestimoInvalidoException;
use App\Exceptions\LimiteEmprestimoException;
use App\Exceptions\MaterialIndisponivelException;
use App\Exceptions\UsuarioBloqueadoException;
use App\Models\Aluno;
use App\Models\EBook;
use App\Models\Emprestimo;
use App\Models\Historico;
use App\Models\Livro;
use App\Models\Professor;
use App\Multas\MultaPadrao;
use App\Multas\MultaProfessor;
use App\Services\EmprestimoService;

$passou = 0;
$falhou = 0;

function verificar(bool $condicao, string $nome): void
{
    global $passou, $falhou;
    if ($condicao) {
        $passou++;
        echo "[OK] {$nome}\n";
    } else {
        $falhou++;
        echo "[FALHOU] {$nome}\n";
    }
}

function esperaExcecao(string $classe, callable $acao, string $nome): void
{
    try {
        $acao();
        verificar(false, $nome . ' (exceção não lançada)');
    } catch (Throwable $e) {
        verificar($e instanceof $classe, $nome);
    }
}

function novoLivro(int $quantidade = 3): Livro
{
    return new Livro('L1', 'Livro de Teste', 'Autor', 2024, $quantidade, 'ISBN-1', 100, 'Editora', 'Técnico');
}

$hoje = new DateTimeImmutable('2026-10-01');

// Teste 1: material indisponível
$service = new EmprestimoService(new Historico());
$aluno = new Aluno('A1', 'Ana', 'ana@email.com');
$livroZero = novoLivro(0);
esperaExcecao(MaterialIndisponivelException::class, fn() => $service->realizarEmprestimo($aluno, $livroZero, $hoje), '1. Bloquear empréstimo sem estoque');

// Teste 2: quarto empréstimo de aluno
$service2 = new EmprestimoService(new Historico());
$aluno2 = new Aluno('A2', 'Bia', 'bia@email.com');
$l1 = novoLivro(4);
$ativos = [];
for ($i = 0; $i < 3; $i++) {
    $ativos[] = $service2->realizarEmprestimo($aluno2, $l1, $hoje);
}
esperaExcecao(LimiteEmprestimoException::class, fn() => $service2->realizarEmprestimo($aluno2, $l1, $hoje), '2. Bloquear quarto empréstimo do aluno');

// Teste 3: usuário bloqueado
$bloqueado = new Aluno('A3', 'Caio', 'caio@email.com');
$bloqueado->bloquear();
esperaExcecao(UsuarioBloqueadoException::class, fn() => $service->realizarEmprestimo($bloqueado, novoLivro(), $hoje), '3. Bloquear usuário inativo');

// Teste 4: devolução repetida
$service4 = new EmprestimoService(new Historico());
$a4 = new Aluno('A4', 'Dani', 'dani@email.com');
$e4 = $service4->realizarEmprestimo($a4, novoLivro(), $hoje);
$service4->registrarDevolucao($e4, new DateTimeImmutable('2026-10-10'));
esperaExcecao(EmprestimoInvalidoException::class, fn() => $service4->registrarDevolucao($e4, new DateTimeImmutable('2026-10-11')), '4. Bloquear segunda devolução');

// Teste 5: limite de acesso simultâneo de e-book
$service5 = new EmprestimoService(new Historico());
$ebook = new EBook('E1', 'E-book', 'Autora', 2025, 1, 'PDF', 2.0, 'https://example.com');
$service5->realizarEmprestimo(new Aluno('A5', 'Eva', 'eva@email.com'), $ebook, $hoje);
esperaExcecao(MaterialIndisponivelException::class, fn() => $service5->realizarEmprestimo(new Aluno('A6', 'Fred', 'fred@email.com'), $ebook, $hoje), '5. Respeitar acessos simultâneos do e-book');

// Teste 6: devolução no prazo
$service6 = new EmprestimoService(new Historico());
$a6 = new Aluno('A7', 'Gabi', 'gabi@email.com');
$e6 = $service6->realizarEmprestimo($a6, novoLivro(), $hoje);
$r6 = $service6->registrarDevolucao($e6, new DateTimeImmutable('2026-10-15'));
verificar($r6['diasAtraso'] === 0 && $r6['multa'] === 0.0, '6. Devolução no prazo sem multa');

// Teste 7: atraso de cinco dias
$service7 = new EmprestimoService(new Historico());
$a7 = new Aluno('A8', 'Helena', 'helena@email.com');
$e7 = $service7->realizarEmprestimo($a7, novoLivro(), $hoje); // vence 15/10
$r7 = $service7->registrarDevolucao($e7, new DateTimeImmutable('2026-10-20'));
verificar($r7['diasAtraso'] === 5 && $r7['multa'] === 10.0, '7. Atraso de cinco dias gera multa de R$ 10,00');

// Teste 8: multa de aluno e professor
verificar((new MultaPadrao())->calcularMulta(5) === 10.0 && (new MultaProfessor())->calcularMulta(5) === 5.0, '8. Multas diferentes para aluno e professor');

// Prazo adicional de professor
$service9 = new EmprestimoService(new Historico());
$prof = new Professor('P1', 'Carlos', 'carlos@email.com');
$ep = $service9->realizarEmprestimo($prof, novoLivro(), $hoje);
verificar($ep->getDataPrevistaDevolucao()->format('d/m/Y') === '22/10/2026', '9. Professor recebe sete dias adicionais');

// Cancelamento não pode ser devolvido
$service10 = new EmprestimoService(new Historico());
$ec = $service10->realizarEmprestimo(new Aluno('A9', 'Ivo', 'ivo@email.com'), novoLivro(), $hoje);
$service10->cancelarEmprestimo($ec);
esperaExcecao(EmprestimoInvalidoException::class, fn() => $service10->registrarDevolucao($ec, $hoje), '10. Empréstimo cancelado não pode ser devolvido');

echo "\nResultado: {$passou} passaram; {$falhou} falharam.\n";
exit($falhou === 0 ? 0 : 1);
