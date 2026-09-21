# MyPocket

## Projeto Final do 2º Bimestre – PW2

### 📌 Sobre o Projeto

O **MyPocket** é um sistema de gestão financeira pessoal desenvolvido em PHP com foco na aplicação dos conceitos de **Programação Orientada a Objetos (POO)**. O sistema permite registrar entradas, saídas e transações do tipo **Diario**, além de trabalhar com transações únicas, recorrências fixas e parceladas.

A aplicação simula uma carteira financeira digital. As movimentações são armazenadas no banco de dados e utilizadas para calcular o saldo, os resumos por período e as previsões de recorrências que ainda não aconteceram.

---

### 🎯 Objetivos

* Facilitar o controle financeiro pessoal.
* Registrar receitas e despesas de forma organizada.
* Calcular o saldo a partir das transações registradas.
* Impedir que o saldo fique negativo.
* Armazenar e exibir o histórico de movimentações.
* Trabalhar com recorrências automáticas.
* Exibir previsões de entradas e saídas futuras.
* Aplicar conceitos de Programação Orientada a Objetos.

---

### ⚙️ Funcionalidades

#### 🔒 Cadastro, Login e Minha Conta
Cada pessoa cria a sua conta e entra com e-mail e senha. As senhas são guardadas com `password_hash` e conferidas com `password_verify`. Na tela **Minha conta** o usuário pode alterar nome, e-mail e senha (deixando a senha em branco, a atual é mantida) ou excluir a própria conta, o que apaga também todas as suas transações e recorrências. Cada usuário só enxerga e altera os próprios dados, identificados pelo `id_usuario`.

#### 💰 Cadastro de Entradas e Saídas
Permite registrar receitas, despesas e transações do tipo **Diario**, informando valor, tipo, frequência, descrição e data.

#### 🔄 Recorrências
Permite cadastrar transações recorrentes com frequência **fixa** (repete todo mês, sem data final) ou **parcelada** (repete todo mês até uma data final). As recorrências ficam registradas separadamente e geram suas ocorrências na tabela de transações.

#### 📅 Geração de Ocorrências
Sempre que o painel é aberto, o sistema verifica as recorrências ativas, identifica as datas que já deveriam ter acontecido e cria as ocorrências que faltam. Ele evita criar uma ocorrência duplicada para a mesma recorrência e data, e nunca gera ocorrências de datas futuras (nas mensais).

#### 📈 Previsão de Recorrências
O sistema calcula entradas e saídas recorrentes que ainda não foram registradas, permitindo exibir uma previsão de saldo por mês sem criar nenhuma transação no banco apenas para realizar o cálculo.

#### ✏️ Edição de Transações
Permite alterar valor, tipo, descrição e data de uma transação cadastrada. A edição só é salva se o saldo não ficar negativo.

#### 🗑️ Exclusão de Transações
Transações normais são excluídas de verdade. Em transações ligadas a uma recorrência, o sistema utiliza o campo `cancelada` para marcar a ocorrência como cancelada sem removê-la fisicamente, evitando que ela seja gerada novamente. A exclusão só acontece se o saldo não ficar negativo.

#### 🔎 Filtros, Extrato e Resumo Mensal
* Lista de transações com filtro por **tipo** e por **mês**.
* **Extrato do ano** com o total de receitas, diário e despesas.
* **Resumo mensal** com entradas, diário, saídas, performance e saldo acumulado de cada mês, com o detalhe das transações de cada um.
* **Previsão de saldo** mês a mês, somando o que já aconteceu ao que as recorrências ainda vão gerar.

---

### 📏 Regras de Negócio

* **Saldo atual:** soma das entradas menos as saídas de todas as transações com data **igual ou anterior a hoje** e que **não estão canceladas**. Transações futuras ainda não afetam o saldo.
* **Diario:** no cálculo do saldo é tratado como saída. Quando é recorrente, gera **uma ocorrência por dia**.
* **Saldo nunca negativo:** cadastrar, editar ou excluir uma transação é bloqueado se o saldo de hoje ficar negativo.
* **Recorrência no passado:** ao cadastrar uma recorrência que começa em uma data passada, o sistema gera todas as parcelas que já venceram. Se o saldo ficar negativo com elas, o cadastro é desfeito por completo (o banco volta ao estado anterior) e uma mensagem de erro é exibida.
* **Dia de vencimento:** nas recorrências mensais, o dia é o da data de início. Em meses mais curtos, o sistema usa o último dia do mês (um vencimento no dia 31 cai no dia 28 em fevereiro).
* **Valor:** precisa ser maior que zero.

---

### 🗄️ Banco de Dados

O projeto utiliza **MySQL**, acessado via **PDO**, com a conexão configurada em `database/conexao.php`. O script de criação das tabelas está em `assets/mypocketSQL.sql`.

**Banco:** `mypocket`

**Tabelas:** `usuarios`, `recorrencias` e `transacoes`.

#### `usuarios`

Armazena os usuários do sistema, incluindo nome, e-mail (único), senha (em hash) e data de criação.

#### `recorrencias`

Armazena as regras das recorrências:

| Coluna | Descrição |
|---|---|
| `id` | Identificador da recorrência |
| `descricao` | Descrição da recorrência |
| `valor` | Valor da movimentação |
| `tipo` | `Entrada`, `Saida` ou `Diario` |
| `frequencia` | `fixa` ou `parc` |
| `data_inicio` | Data inicial |
| `data_fim` | Data final, quando houver |
| `dia_vencimento` | Dia usado em recorrências mensais (vazio nas do tipo `Diario`) |
| `ativa` | Indica se a recorrência está ativa |
| `id_usuario` | Usuário dono da recorrência |

#### `transacoes`

Armazena as ocorrências efetivamente registradas:

| Coluna | Descrição |
|---|---|
| `id` | Identificador da transação |
| `valor` | Valor da movimentação |
| `tipo` | `Entrada`, `Saida` ou `Diario` |
| `descricao` | Descrição da movimentação |
| `data` | Data da transação |
| `id_usuario` | Usuário dono da transação |
| `id_recorrencia` | Recorrência de origem, quando existir |
| `data_recorrencia` | Data original da ocorrência da recorrência |
| `cancelada` | Indica se a ocorrência foi cancelada |

---

### 🔗 Relação entre as tabelas

```text
usuarios
   ├── 1:N → transacoes
   └── 1:N → recorrencias

recorrencias
   └── 1:N → transacoes
```

Uma recorrência representa a **regra**. Uma transação representa uma **ocorrência** dessa regra.

---

### 🏗️ Estrutura Orientada a Objetos

O projeto utiliza:

* **Encapsulamento:** atributos protegidos e acesso por métodos (`getValor()`, `getData()`, etc.). O construtor de `Transacao` valida que o valor seja maior que zero.
* **Herança:** `Receita`, `Despesa` e `Diario` herdam de `Transacao`.
* **Abstração:** `Transacao` é uma classe abstrata, com o método abstrato `getTipo()`.
* **Polimorfismo:** a `Carteira` trabalha com qualquer `Transacao`, sem precisar saber se ela é uma `Receita`, `Despesa` ou `Diario`. Cada subclasse responde ao seu jeito em `getTipo()`.

### 📂 Principais Classes

* **Carteira:** gerencia o saldo e as transações carregadas. O método `addTransacoes()` aplica uma nova transação **validando** o saldo (lança exceção se for insuficiente), enquanto `carregarTransacao()` apenas carrega uma transação já existente no banco, sem validar.
* **Transacao:** classe abstrata base das movimentações.
* **Receita:** representa entradas (`Entrada`).
* **Despesa:** representa saídas (`Saida`).
* **Diario:** representa transações do tipo diário (`Diario`).

---

### 📁 Estrutura de Pastas

```text
MyPocket/
├── assets/
│   ├── mypocketSQL.sql      # script do banco de dados
│   └── wallet.png           # logo
├── classes/
│   ├── Transacao.php        # classe abstrata
│   ├── Receita.php
│   ├── Despesa.php
│   ├── Diario.php
│   └── Carteira.php
├── database/
│   └── conexao.php          # conexão PDO com o MySQL
├── usuarios/
│   ├── editarUser.php       # tela "Minha conta"
│   └── deleteUser.php       # exclusão da própria conta
├── auten.php                # barra quem não está logado
├── cadastro.php
├── login.php
├── logout.php
├── index.php                # painel principal
├── processa.php             # cadastro de transações e recorrências
├── editar.php
├── delete.php
├── recorrencias.php         # geração e previsão de recorrências
├── funcoes.php              # carrega saldo e carteiras do painel
└── README.md
```

| Arquivo | Função |
|---|---|
| `index.php` | Painel: saldo, formulário, lista, extrato, resumo mensal e previsão |
| `processa.php` | Recebe o formulário e cadastra a transação única ou a recorrência |
| `recorrencias.php` | Gera as ocorrências que faltam e calcula a previsão dos meses futuros |
| `funcoes.php` | Monta as carteiras (geral, do ano e do mês) e calcula o saldo atual |
| `editar.php` / `delete.php` | Edição e exclusão de transações, com a regra do saldo |
| `auten.php` | Inicia a sessão e redireciona para o login quem não estiver autenticado |

---

### 🔐 Segurança

* Senhas com `password_hash` / `password_verify`.
* Consultas com **prepared statements** (PDO), evitando SQL Injection.
* Saída com `htmlspecialchars`, evitando XSS.
* Páginas protegidas por sessão (`auten.php`).
* Todas as consultas filtram por `id_usuario`, então cada usuário só acessa os próprios dados.
* A exclusão de conta só aceita requisição **POST** e só apaga a conta de quem está logado.

---

### 🚀 Como Executar

1. Instale o **XAMPP** (ou outro servidor com PHP e MySQL) e inicie o **Apache** e o **MySQL**.
2. Copie a pasta `MyPocket` para `htdocs`.
3. No phpMyAdmin, importe o arquivo `assets/mypocketSQL.sql` para criar o banco e as tabelas.
4. Confira os dados de conexão em `database/conexao.php` (padrão: host `localhost`, usuário `root`, senha vazia, banco `mypocket`).
5. Acesse `http://localhost/MyPocket/cadastro.php`, crie sua conta e faça login.

---

### 🔮 Limitações e Melhorias Futuras

* As exclusões de transações ainda são feitas por link (`GET`); o ideal seria usar `POST` com confirmação.
* Adicionar recuperação de senha e confirmação de e-mail.

---

### 🖥️ Tecnologias Utilizadas

* PHP
* MySQL via PDO
* HTML5
* CSS3
* Bulma
* Programação Orientada a Objetos (POO)
