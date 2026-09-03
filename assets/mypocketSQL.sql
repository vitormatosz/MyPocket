
create database mypocket;

use mypocket;

create table usuarios(
id int auto_increment primary key not null,
nome varchar(100) not null,
email varchar(100) not null unique,
senha varchar(255) not null,
criado_em date
);

select * from usuarios;

create table transacoes(
id int auto_increment primary key not null ,
valor decimal(10,2) not null,
tipo enum('Entrada', 'Saida', 'Diario') not null,
descricao varchar(255) not null,
data date not null,
id_usuario int not null,
foreign key (id_usuario) references usuarios(id)
);

select * from transacoes;
