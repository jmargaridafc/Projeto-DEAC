<?php
// scripts/processar_login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $db_path = __DIR__ . '/../hotel.db';
        $db = new SQLite3($db_path);
        $db->enableExceptions(true);
    } catch (Exception $e) {
        die("FALHA NA LIGAÇÃO À BD: " . $e->getMessage());
    }

    // 1. Procura o utilizador apenas pelo username
    $stmt = $db->prepare("SELECT * FROM utilizadores WHERE nome_utilizador = :user");
    $stmt->bindValue(':user', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    // 2. Validação Criptográfica e Sessão
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['nome_utilizador'];
        $_SESSION['tipo'] = $user['tipo']; // Lê 'admin' ou 'cliente' da BD

        // Registo de acessos exigido pelo Guião W2
        $agora = date('Y-m-d H:i:s');
        $stmt_log = $db->prepare("INSERT INTO acessos (id_utilizador, data_hora) VALUES (:id, :hora)");
        $stmt_log->bindValue(':id', $user['id'], SQLITE3_INTEGER);
        $stmt_log->bindValue(':hora', $agora, SQLITE3_TEXT);
        $stmt_log->execute();

        // 3. Tratamento seguro do destino de redirecionamento
        $destino = 'index.php';
        if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
            // basename remove caminhos relativos perigosos mas preserva o nome do ficheiro (ex: "reservations_painel.php")
            $destino = basename($_POST['redirect_to']); 
        }
        
        // Redireciona subindo um nível (saindo da pasta scripts/)
        header("Location: ../" . $destino);
        exit;

    } else {
        $_SESSION['erro'] = "Nome de utilizador ou palavra-passe incorretos.";
        
        // Se o login falhar, voltamos para o formulário mantendo o destino na URL
        $origem = (isset($_POST['redirect_to']) && $_POST['redirect_to'] !== 'index.php') ? basename($_POST['redirect_to']) : '';
        
        if (!empty($origem)) {
            header("Location: ../login.php?next=" . urlencode($origem));
        } else {
            header("Location: ../login.php");
        }
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>