<?php
// db.php

// Define o caminho absoluto para o ficheiro hotel.db na raiz do projeto
define('DB_PATH', __DIR__ . '/hotel.db');

function getDB(): SQLite3 {
    static $db = null;

    if ($db === null) {
        try {
            $db = new SQLite3(DB_PATH);
            $db->enableExceptions(true);
            $db->exec('PRAGMA foreign_keys = ON;');
        } catch (Exception $e) {
            die("Erro ao abrir a base de dados: " . $e->getMessage());
        }
    }

    return $db;
}

// Cria a ligação global que os scripts vão herdar
$conn = getDB();
?>