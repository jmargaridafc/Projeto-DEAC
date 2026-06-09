<?php
// scripts/criar_tabelas.php
$db_file = dirname(__DIR__) . '/db.php';
if (!file_exists($db_file)) { die("Erro: db.php não encontrado."); }
require_once $db_file;
global $conn;

try {
    echo "<h2>A estruturar a Base de Dados...</h2>";

    // 1. Tabela de Utilizadores
    $conn->exec("CREATE TABLE IF NOT EXISTS utilizadores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_utilizador TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        tipo TEXT NOT NULL CHECK(tipo IN ('cliente', 'admin'))
    )");
    echo "✅ Tabela 'utilizadores' estruturada.<br>";

    // 2. Tabela de Acessos (EXIGÊNCIA DO GUIÃO W2)
    $conn->exec("CREATE TABLE IF NOT EXISTS acessos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_utilizador INTEGER NOT NULL,
        data_hora TEXT NOT NULL,
        FOREIGN KEY (id_utilizador) REFERENCES utilizadores(id) ON DELETE CASCADE
    )");
    echo "✅ Tabela 'acessos' (Histórico do Guião W2) estruturada.<br>";

    // 3. Tabela de Hotéis
    $conn->exec("CREATE TABLE IF NOT EXISTS hoteis (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        localizacao TEXT NOT NULL,
        preco REAL NOT NULL,
        avaliacao REAL DEFAULT 0.0,
        vagas INTEGER NOT NULL DEFAULT 0
    )");
    echo "✅ Tabela 'hoteis' estruturada.<br>";

    // Inserir dados de teste padrão
    $pass_admin = password_hash('123456', PASSWORD_DEFAULT);
    $pass_cliente = password_hash('senha123', PASSWORD_DEFAULT);
    $conn->exec("INSERT OR IGNORE INTO utilizadores (id, nome_utilizador, password, tipo) VALUES 
        (1, 'admin', '$pass_admin', 'admin'),
        (2, 'joao123', '$pass_cliente', 'cliente')
    ");

    $conn->exec("INSERT OR IGNORE INTO hoteis (id, nome, localizacao, preco, avaliacao, vagas) VALUES 
        (1, 'Hotel Tivoli', 'Lisboa, Portugal', 120.00, 4.7, 5),
        (2, 'Porto Palácio Hotel', 'Porto, Portugal', 95.50, 4.5, 3),
        (3, 'Algarve Beach Resort', 'Albufeira, Portugal', 150.00, 4.8, 0)
    ");
    
    echo "✅ Tudo pronto com os requisitos do Guião W2!<br>";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>