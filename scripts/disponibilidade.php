<?php
// scripts/disponibilidade.php

// Indica ao PHP para usar a variável $conn criada globalmente pelo db.php
global $conn; 

$lista_hoteis = [];

try {
    if (isset($conn) && $conn !== null) {
        // Puxa os dados reais da tabela 'hoteis' do SQLite
        $result = $conn->query("SELECT id, nome, localizacao, preco, avaliacao, vagas FROM hoteis");
        
        if ($result) {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $lista_hoteis[] = $row;
            }
        }
    }
} catch (Exception $e) {
    // Caso falte criar as tabelas, evita que o site dê erro 500
    $lista_hoteis = [];
}
?>