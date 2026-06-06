<?php

session_start();

require_once 'processa.php';

if (!isset($_SESSION['carteira'])) {
    require_once 'classes/Carteira.php';
    $_SESSION['carteira'] = new Carteira();
}
?>

<html></html>