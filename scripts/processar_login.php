<?php
// scripts/processar_login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Carrega a ligação à base de dados SQLite
require_once dirname(__DIR__) . '/db.php';
global $conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($user) || empty($pass)) {
        die("Por favor, preencha todos os campos.");
    }

    try {
        // 2. Procura pelo utilizador na tabela
        $stmt = $conn->prepare("SELECT * FROM utilizadores WHERE nome_utilizador = :user");
        $stmt->bindValue(':user', $user, SQLITE3_TEXT);
        $result = $stmt->execute();
        $utilizador = $result->fetchArray(SQLITE3_ASSOC);

        // 3. Se o utilizador existir e a password estiver correta
        if ($utilizador && password_verify($pass, $utilizador['password'])) {
            
            // Define as variáveis de sessão
            $_SESSION['id'] = $utilizador['id'];
            $_SESSION['nome'] = $utilizador['nome_utilizador'];
            $_SESSION['tipo'] = $utilizador['tipo'];

            // 4. REQUISITO GUIÃO W2: Registar a data/hora atual do acesso na tabela
            // Define o fuso horário de Portugal para a hora ficar certa
            date_default_timezone_set('Europe/Lisbon');
            $dataHoraAtual = date('Y-m-d H:i:s');

            $logStmt = $conn->prepare("INSERT INTO acessos (id_utilizador, data_hora) VALUES (:id_user, :data_hora)");
            $logStmt->bindValue(':id_user', $utilizador['id'], SQLITE3_INTEGER);
            $logStmt->bindValue(':data_hora', $dataHoraAtual, SQLITE3_TEXT);
            $logStmt->execute();

            // 5. Redireciona com sucesso para a Homepage
            header("Location: ../index.php");
            exit();
        } else {
            echo "<script>alert('Utilizador ou password incorretos.'); window.location.href='../login.php';</script>";
        }
    } catch (Exception $e) {
        die("Erro no processo de Login/Acesso: " . $e->getMessage());
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>