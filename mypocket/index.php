<?php

session_start();

require_once 'processa.php';

if (!isset($_SESSION['carteira'])) {
    require_once 'classes/Carteira.php';
    $_SESSION['carteira'] = new Carteira();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projeto Final PW2 - MyPocket</title>
</head>

<body>
    <div>

    </div>

    <div>
        <form action="processa.php" method="POST">
            <div>
                <label>Valor</label>
                <input type="number">
            </div>

            <div>
                <label>Tipo</label>
                <select name="tipo" required>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                </select>
            </div>

            <div>
                <label>Descrição</label>
                <input type="text">
            </div>

            <div>
                <label>Data</label>
                <input type="date">
            </div>

        </form>
    </div>
</body>

</html>