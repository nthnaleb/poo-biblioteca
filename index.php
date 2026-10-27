<?php
declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use App\Models\Aluno;
use App\Models\EBook;
use App\Models\Historico;
use App\Models\Livro;
use App\Models\Professor;
use App\Models\Revista;
use App\Services\EmprestimoService;

$historico = new Historico();
$service = new EmprestimoService($historico);

$aluna = new Aluno('A001', 'Ana Souza', 'ana@email.com');
$professor = new Professor('P001', 'Carlos Mendes', 'carlos@email.com');

$livro = new Livro(
    'L001',
    'Programação Orientada a Objetos com PHP',
    'João Silva',
    2024,
    3,
    '978-65-00000-00-0',
    320,
    'Editora Técnica',
    'Programação'
);
$revista = new Revista('R001', 'Tecnologia Hoje', 'Equipe Editorial', 2026, 2, 145, 12, 'Outubro');
$ebook = new EBook('E001', 'APIs REST com PHP', 'Maria Costa', 2025, 10, 'PDF', 8.5, 'https://exemplo.com/apis-rest.pdf');

$dataEmprestimo = new DateTimeImmutable('2026-10-01');
$emprestimoAna = $service->realizarEmprestimo($aluna, $livro, $dataEmprestimo);
$emprestimoCarlos = $service->realizarEmprestimo($professor, $livro, $dataEmprestimo);

echo "==========================================\n";
echo "EMPRÉSTIMO\n";
echo "==========================================\n";
echo "Usuário: {$emprestimoAna->getUsuario()->getNome()}\n";
echo "Tipo: Aluno\n";
echo "Material: {$emprestimoAna->getMaterial()->getTitulo()}\n";
echo "Data do empréstimo: " . $emprestimoAna->getDataEmprestimo()->format('d/m/Y') . "\n";
echo "Data prevista: " . $emprestimoAna->getDataPrevistaDevolucao()->format('d/m/Y') . "\n";
echo "Status: {$emprestimoAna->getStatus()}\n\n";

$resultado = $service->registrarDevolucao($emprestimoAna, new DateTimeImmutable('2026-10-20'));
echo "==========================================\n";
echo "DEVOLUÇÃO\n";
echo "==========================================\n";
echo "Usuário: Ana Souza\n";
echo "Material: {$livro->getTitulo()}\n";
echo "Dias de atraso: {$resultado['diasAtraso']}\n";
echo "Multa: R$ " . number_format($resultado['multa'], 2, ',', '.') . "\n";
echo "Status: {$resultado['status']}\n\n";

echo "Prazo do professor para o livro: " . $emprestimoCarlos->getDataPrevistaDevolucao()->format('d/m/Y') . "\n";
echo "Disponibilidade atual do livro: {$livro->consultarQuantidadeDisponivel()}/{$livro->getQuantidadeExemplares()}\n";
echo "\nHISTÓRICO\n";
echo $historico->exibir();

echo "\nMateriais cadastrados:\n";
echo $revista->exibirInformacoes() . "\n";
echo $ebook->exibirInformacoes() . "\n";
