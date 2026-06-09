<?php
// O vetor global $_GET extrai as variáveis empacotadas no URL
echo "<h1>Página do Colega (Simulação)</h1>";
echo "<h2>Dados recebidos da triagem com sucesso:</h2>";
echo "<p><strong>Quarto:</strong> " . $_GET['quarto'] . "</p>";
echo "<p><strong>Check-in:</strong> " . $_GET['check_in'] . "</p>";
echo "<p><strong>Check-out:</strong> " . $_GET['check_out'] . "</p>";
?>