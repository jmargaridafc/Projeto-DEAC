<?php
// scripts/atualizar_preco.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueia o acesso direto caso quem aceda não seja o admin autenticado
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem atualizar preços.");
}

// Carrega o db.php
require_once dirname(__DIR__) . '/db.php';
global $conn;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$preco = isset($_GET['preco']) ? (float)$_GET['preco'] : 0.0;

if ($id > 0 && $preco >= 0) {
    try {
        // Atualiza o preço com base no ID recebido
        $stmt = $conn->prepare("UPDATE hoteis SET preco = :preco WHERE id = :id");
        $stmt->bindValue(':preco', $preco, SQLITE3_FLOAT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        
        // Redireciona de volta para a homepage atualizada
        header("Location: ../index.php");
        exit();
    } catch (Exception $e) {
        die("Erro ao atualizar o preço no SQLite: " . $e->getMessage());
    }
} else {
    die("Parâmetros inválidos.");
}
?>