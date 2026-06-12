<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $perfil_escolhido = isset($_POST['perfil']) ? trim($_POST['perfil']) : 'cliente'; 

    // 1. Vetor de Ligação (A correção estrutural)
    try {
        $db_path = __DIR__ . '/../hotel.db';
        $db = new SQLite3($db_path);
        $db->enableExceptions(true);
    } catch (Exception $e) {
        die("FALHA MATEMÁTICA NOVA: " . $e->getMessage());
    }

    // 2. Extração de Identidade
    $stmt = $db->prepare("SELECT * FROM utilizadores WHERE nome_utilizador = :user AND tipo = :tipo");
    $stmt->bindValue(':user', $username, SQLITE3_TEXT);
    $stmt->bindValue(':tipo', $perfil_escolhido, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // 3. Validação Criptográfica e Geração de Sessão
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nome_utilizador'];
        $_SESSION['tipo'] = $user['tipo'];

        // Registo de acessos exigido pelo Guião W2
        $agora = date('Y-m-d H:i:s');
        $stmt_log = $db->prepare("INSERT INTO acessos (id_utilizador, data_hora) VALUES (:id, :hora)");
        $stmt_log->bindValue(':id', $user['id'], SQLITE3_INTEGER);
        $stmt_log->bindValue(':hora', $agora, SQLITE3_TEXT);
        $stmt_log->execute();

        header("Location: ../reservations_painel.php");
        exit;
    } else {
        header("Location: ../login.php");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>