<?php
// scripts/processar_registo.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Importa a ligação à base de dados SQLite da raiz
require_once dirname(__DIR__) . '/db.php';
global $conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Define por padrão que novos registos pelo site são sempre do tipo 'cliente'
    $tipo = 'cliente'; 

    try {
        // 2. Validação Exclusiva do Servidor: Verifica se o utilizador já existe
        $checkStmt = $conn->prepare("SELECT id FROM utilizadores WHERE nome_utilizador = :user");
        $checkStmt->bindValue(':user', $user, SQLITE3_TEXT);
        $checkResult = $checkStmt->execute();
        
        if ($checkResult->fetchArray()) {
            echo "<script>alert('Esse nome de utilizador já está registado. Escolha outro.'); window.history.back();</script>";
            exit();
        }

        // 3. Encripta a palavra-passe com hash seguro para a BD
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        // 4. Insere o novo utilizador na tabela 'utilizadores' (Guardando o Hash Seguro)
        $stmt = $conn->prepare("INSERT INTO utilizadores (nome_utilizador, password, tipo) VALUES (:user, :pass, :tipo)");
        $stmt->bindValue(':user', $user, SQLITE3_TEXT);
        $stmt->bindValue(':pass', $hashed_password, SQLITE3_TEXT);
        $stmt->bindValue(':tipo', $tipo, SQLITE3_TEXT);
        $stmt->execute();

        // 5. Registo feito! Redireciona o utilizador diretamente para a página de login
        echo "<script>alert('Conta criada com sucesso! Faça login para continuar.'); window.location.href='../login.php';</script>";
        exit();

    } catch (Exception $e) {
        die("Erro ao registar conta no SQLite: " . $e->getMessage());
    }
} else {
    header("Location: novoregisto.php");
    exit();
}
?>