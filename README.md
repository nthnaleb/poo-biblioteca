# Sistema de Biblioteca com PHP e POO

## Descrição
Aplicação de demonstração para gerenciar materiais, usuários, empréstimos, devoluções, atrasos, multas e histórico. O projeto foi organizado em camadas e usa PHP orientado a objetos.

## Requisitos
- PHP 8.1 ou superior
- Terminal/Prompt de Comando
- Não exige banco de dados: os dados são mantidos em memória durante a execução.

## Como executar
Na pasta `biblioteca-poo`, execute:

```bash
php index.php
```

Para executar os testes automatizados:

```bash
php tests/run.php
```

Opcionalmente, com Composer instalado:

```bash
composer dump-autoload
composer test
composer demo
```

O projeto inclui `autoload.php` próprio com `spl_autoload_register()`, portanto o Composer não é obrigatório.

## Estrutura de diretórios

```text
biblioteca-poo/
├── src/
│   ├── Models/
│   │   ├── Material.php
│   │   ├── Livro.php
│   │   ├── Revista.php
│   │   ├── EBook.php
│   │   ├── Usuario.php
│   │   ├── Aluno.php
│   │   ├── Professor.php
│   │   ├── Emprestimo.php
│   │   └── Historico.php
│   ├── Interfaces/MultaInterface.php
│   ├── Multas/
│   │   ├── MultaPadrao.php
│   │   └── MultaProfessor.php
│   ├── Services/EmprestimoService.php
│   └── Exceptions/
│       ├── MaterialIndisponivelException.php
│       ├── LimiteEmprestimoException.php
│       ├── UsuarioBloqueadoException.php
│       └── EmprestimoInvalidoException.php
├── tests/run.php
├── docs/diagrama-uml.md
├── autoload.php
├── composer.json
├── index.php
└── README.md
```

## Classes implementadas
- `Material`: classe abstrata com dados comuns, controle de disponibilidade e prazo abstrato.
- `Livro`, `Revista` e `EBook`: especializações de material com atributos próprios e prazos de 14, 7 e 21 dias.
- `Usuario`: classe abstrata com atributos protegidos, status e controle de empréstimos ativos.
- `Aluno`: limite de 3 empréstimos ativos.
- `Professor`: limite de 5 empréstimos ativos e 7 dias adicionais no prazo.
- `Emprestimo`: associa um usuário e um material, datas, status e multa.
- `Historico`: registra data, hora, ação, usuário, material e observação.
- `EmprestimoService`: centraliza validações, criação, devolução, cálculo de atraso, multa e cancelamento.
- `MultaInterface`, `MultaPadrao` e `MultaProfessor`: cálculo polimórfico das multas.
- Exceções personalizadas: indisponibilidade, limite, usuário bloqueado e empréstimo inválido.

## Regras de negócio
1. Não emprestar materiais sem exemplares/acessos disponíveis.
2. Respeitar limite de 3 empréstimos para alunos e 5 para professores.
3. Não permitir novo empréstimo a usuário bloqueado.
4. Não permitir devolver duas vezes o mesmo empréstimo.
5. Não permitir devolver empréstimo cancelado.
6. Atualizar estoque/acessos na retirada e na devolução.
7. Prazos: livro 14 dias, revista 7 dias e e-book 21 dias.
8. Acrescentar 7 dias ao prazo de professor.
9. Calcular multa apenas se houver atraso: R$ 2,00/dia para aluno e R$ 1,00/dia para professor.
10. Não permitir quantidade disponível negativa nem acessos simultâneos acima do limite.

## Conceitos de POO utilizados
- **Encapsulamento:** atributos não são públicos; o acesso ocorre por métodos.
- **Herança:** `Livro`, `Revista` e `EBook` herdam de `Material`; `Aluno` e `Professor` herdam de `Usuario`.
- **Abstração:** `Material` e `Usuario` são classes abstratas; `Material` define o método abstrato de prazo.
- **Polimorfismo:** o serviço calcula multa por meio de `MultaInterface`, usando implementações diferentes.
- **Composição/associação:** `Emprestimo` mantém referências a objetos `Usuario` e `Material`.
- **Interfaces:** `MultaInterface` estabelece o contrato `calcularMulta()`.
- **Exceções:** violações de regras geram exceções específicas e podem ser capturadas com `try/catch`.
- **Namespaces e autoload:** classes organizadas em `App\Models`, `App\Services`, `App\Interfaces`, `App\Multas` e `App\Exceptions`; carregamento automático com `spl_autoload_register()`.

## Testes realizados
O arquivo `tests/run.php` executa testes para:
1. Material indisponível.
2. Quarto empréstimo de aluno.
3. Usuário bloqueado.
4. Devolução duplicada.
5. Limite de acessos de e-book.
6. Devolução dentro do prazo.
7. Atraso de cinco dias e multa de R$ 10,00.
8. Comparação de multas de aluno e professor.
9. Prazo adicional de professor.
10. Devolução de empréstimo cancelado.

## Observação sobre armazenamento
Este projeto é didático e mantém os dados apenas em memória. Ao encerrar o script, os registros são perdidos. Persistência em banco de dados não foi exigida na atividade.

## Saída esperada do cenário principal
Para a aluna Ana Souza, o empréstimo do livro em 01/10/2026 vence em 15/10/2026. A devolução em 20/10/2026 resulta em 5 dias de atraso e multa de R$ 10,00. Para o professor Carlos Mendes, o prazo do mesmo livro é de 21 dias, com vencimento em 22/10/2026.
