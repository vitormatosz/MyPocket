<?php
// Importa a classe para o PHP saber como lidar com ela, se necessário
require_once 'classes/Carteira.php';

session_start();

// Cria uma nova instância limpa da Carteira na sessão
$_SESSION['carteira'] = new Carteira();

// Redireciona de volta para a página principal
header("Location: index.php");
exit;