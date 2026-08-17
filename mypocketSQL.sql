create database sistema_crud;

use sistema_crud;

create table transacoes(
id int auto_increment primary key not null ,
valor decimal(10,2) not null,
tipo enum('Entrada', 'Despesa') not null,
descricao varchar(255) not null,
data date not null
);

select * from transacoes;