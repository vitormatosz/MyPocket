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
* Armazenar e exibir o histórico de movimentações.
* Trabalhar com recorrências automáticas.
* Exibir previsões de entradas e saídas futuras.
* Aplicar conceitos de Programação Orientada a Objetos.

---

### ⚙️ Funcionalidades

#### 💰 Cadastro de Entradas e Saídas
Permite registrar receitas, despesas e transações do tipo **Diario**, informando descrição, valor e data.

#### 🔄 Recorrências
Permite cadastrar transações recorrentes com frequência **fixa** ou **parcelada**. As recorrências ficam registradas separadamente e podem gerar suas ocorrências na tabela de transações.

#### 📅 Geração de Ocorrências
O sistema verifica as recorrências ativas, identifica as datas que devem existir e evita criar uma ocorrência duplicada para a mesma recorrência e data.

#### 📈 Previsão de Recorrências
O sistema calcula entradas e saídas recorrentes que ainda não foram registradas, permitindo exibir uma previsão por mês sem criar uma nova transação no banco apenas para realizar o cálculo.

#### ✏️ Edição de Transações
Permite alterar valor, tipo, descrição e data de uma transação cadastrada.

#### 🗑️ Exclusão de Transações
Transações normais podem ser excluídas. Em transações ligadas a uma recorrência, o sistema utiliza o campo `cancelada` para marcar a ocorrência como cancelada sem removê-la fisicamente.

#### 🔎 Filtros e Extratos
Permite consultar as movimentações e filtrar o extrato por período e tipo.

#### 🔒 Autenticação
Cada usuário possui suas próprias transações e recorrências, identificadas pelo `id_usuario`.

---

### 🗄️ Banco de Dados

O projeto utiliza **MySQL**, acessado via **PDO**, com a conexão configurada em `database/conexao.php`.

**Banco:** `mypocket`

**Tabelas:** `usuarios`, `recorrencias` e `transacoes`.

#### `usuarios`

Armazena os usuários do sistema, incluindo nome, e-mail, senha e data de criação.

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
| `dia_vencimento` | Dia usado em recorrências mensais |
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

* **Encapsulamento:** atributos protegidos e acesso por métodos.
* **Herança:** `Receita`, `Despesa` e `Diario` herdam de `Transacao`.
* **Abstração:** `Transacao` é uma classe abstrata.
* **Polimorfismo:** diferentes tipos de transação podem ser tratados por meio da classe base.

### 📂 Principais Classes

* **Carteira:** gerencia o saldo e as transações carregadas.
* **Transacao:** classe abstrata base das movimentações.
* **Receita:** representa entradas.
* **Despesa:** representa saídas.
* **Diario:** representa transações do tipo diário.

---

### 🖥️ Tecnologias Utilizadas

* PHP
* MySQL via PDO
* HTML5
* CSS3
* Bulma
* Programação Orientada a Objetos (POO)
