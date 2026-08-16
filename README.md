# MyPocket

## Projeto Final do 2º Bimestre – PW2

### 📌 Sobre o Projeto

O **MyPocket** é um sistema de gestão financeira pessoal desenvolvido em PHP com foco na aplicação dos conceitos de **Programação Orientada a Objetos (POO)**. O sistema foi criado para auxiliar usuários no controle de suas finanças, permitindo o registro de receitas e despesas de maneira simples, organizada e segura.

A aplicação simula uma carteira financeira digital, onde todas as movimentações são registradas em um histórico e refletem automaticamente no saldo disponível. O projeto foi desenvolvido com o objetivo de colocar em prática conceitos fundamentais de desenvolvimento de software, como modelagem de classes, encapsulamento de dados, herança, abstração e polimorfismo.

---

### 🎯 Objetivos

* Facilitar o controle financeiro pessoal.
* Registrar receitas e despesas de forma organizada.
* Calcular automaticamente o saldo da carteira.
* Armazenar e exibir o histórico de movimentações.
* Garantir a integridade dos dados por meio de validações.
* Aplicar conceitos de Programação Orientada a Objetos em um projeto prático.

---

### ⚙️ Funcionalidades

#### 💰 Cadastro de Receitas

Permite registrar entradas de dinheiro, informando descrição, valor e data da movimentação.

#### 💸 Cadastro de Despesas

Permite registrar gastos realizados pelo usuário, atualizando automaticamente o saldo da carteira.

#### ✏️ Edição de Transações

Permite alterar o valor, tipo, descrição ou data de uma transação já cadastrada, recalculando o saldo automaticamente.

#### 🗑️ Exclusão de Transações

Permite remover uma transação do histórico, com o saldo sendo atualizado automaticamente após a exclusão.

#### 🔎 Filtro de Extrato

Permite filtrar o histórico de transações exibindo apenas receitas, apenas despesas, ou todas as movimentações.

#### 📊 Controle Automático de Saldo

O saldo é atualizado sempre que uma nova receita ou despesa é adicionada, evitando cálculos manuais.

#### 📜 Histórico de Transações

Todas as movimentações ficam registradas em uma lista organizada, permitindo o acompanhamento financeiro ao longo do tempo.

#### ✅ Validação de Dados

O sistema impede operações inválidas, como valores negativos, informações incompletas ou datas fora do período permitido, garantindo maior segurança e confiabilidade.

#### 🔒 Proteção contra Saldo Negativo

Antes de confirmar uma nova transação, uma edição ou uma exclusão, o sistema recalcula o saldo resultante da operação. Caso o saldo ficasse negativo, a operação é bloqueada e uma mensagem de erro é exibida ao usuário.

#### 🗑️ Gerenciamento de Registros

Possibilita visualizar e organizar as transações registradas pelo usuário.

---

### 🗄️ Banco de Dados

O projeto utiliza **MySQL**, acessado via **PDO**, com a conexão configurada em `database/conexao.php`.

Banco: `sistema_crud`

Tabela principal: `transacoes`, com as colunas:

| Coluna     | Tipo                  | Descrição                          |
|------------|-----------------------|-------------------------------------|
| id         | INT (auto increment)  | Identificador único da transação    |
| valor      | DECIMAL / FLOAT        | Valor da movimentação               |
| tipo       | VARCHAR                | `"Entrada"` (receita) ou `"Saida"` (despesa) |
| descricao  | VARCHAR                | Descrição da movimentação           |
| data       | DATE                    | Data da movimentação                |

O saldo da carteira **não é armazenado diretamente no banco** — ele é sempre recalculado a partir da soma de todas as transações cadastradas, garantindo que o valor exibido esteja sempre consistente com o histórico.

---

### 🔄 CRUD

O sistema implementa as quatro operações básicas sobre a tabela `transacoes`:

* **Create**: cadastra uma nova receita ou despesa, validando o valor, a data e se a operação não deixaria o saldo negativo antes de inserir no banco.
* **Read**: busca todas as transações do banco e exibe o saldo atual, os totais de receitas/despesas e o extrato completo, com opção de filtro.
* **Update**: permite alterar uma transação existente, recalculando o saldo que resultaria da alteração antes de confirmar a atualização.
* **Delete**: remove uma transação do banco, verificando antes se a exclusão não deixaria o saldo negativo.

---

### 🏗️ Estrutura Orientada a Objetos

O projeto foi desenvolvido seguindo os princípios da Programação Orientada a Objetos:

* **Encapsulamento:** proteção dos atributos das classes por meio de métodos getters e setters.
* **Herança:** reutilização de características comuns entre classes relacionadas.
* **Abstração:** representação dos elementos do sistema através de classes específicas.
* **Polimorfismo:** tratamento uniforme de diferentes tipos de transações financeiras.

---

### 📂 Principais Classes

* **Carteira:** responsável pelo gerenciamento do saldo e do histórico financeiro.
* **Transacao:** classe base que representa uma movimentação financeira.
* **Receita:** especialização de transação para entradas de dinheiro.
* **Despesa:** especialização de transação para saídas de dinheiro.

---

### 🖥️ Tecnologias Utilizadas

* PHP
* MySQL (via PDO)
* HTML5
* CSS3
* Bulma
* Programação Orientada a Objetos (POO)

---

### 📈 Benefícios do Sistema

O MyPocket oferece uma maneira prática de acompanhar receitas e despesas, auxiliando na organização financeira e permitindo uma visão clara da situação econômica do usuário. Além disso, o projeto serve como ferramenta de aprendizado para o desenvolvimento de aplicações orientadas a objetos.

### 👨‍💻 Desenvolvido por

Vitor Matos – Projeto acadêmico desenvolvido para a disciplina de Programação Web II (PW2).
