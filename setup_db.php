<?php
// 1. Cria a ligação e o ficheiro físico 'hotel.db' na raiz
$db = new SQLite3(__DIR__ . '/hotel.db');

// 2. Cria a tabela de Reservas
$db->exec("CREATE TABLE IF NOT EXISTS reservas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_hospede TEXT,
    quarto TEXT,
    check_in TEXT,
    check_out TEXT,
    status TEXT,
    preco REAL
)");


echo "SUCESSO: Base de dados e tabelas essenciais criadas com sucesso na raiz!\n";
?>
