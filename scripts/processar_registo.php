<?php
session_start();

// Configurações da Base de Dados (Altere com os seus dados reais)
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "o_seu_banco_de_dados";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }
    
    // Captura os dados do formulário
    $tipo_utilizador = isset($_POST['perfil']) ? $_POST['perfil'] : 'cliente';
    $nome_utilizador = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password        = isset($_POST['password']) ? $_POST['password'] : '';
    $password_conf   = isset($_POST['password_conf']) ? $_POST['password_conf'] : '';
    
    $_SESSION['old_nome_utilizador'] = $nome_utilizador;

    // Validações
    if (empty($nome_utilizador) || empty($password) || empty($password_conf)) {
        $_SESSION['erro'] = "Por favor, preencha todos os campos obrigatórios.";
        header("Location: novoregisto.php");
        exit();
    } 
    
    if ($password !== $password_conf) {
        $_SESSION['erro'] = "As palavras-passe não coincidem.";
        header("Location: novoregisto.php");
        exit();
    } 
    
    if (strlen($password) < 6) {
        $_SESSION['erro'] = "A palavra-passe deve ter pelo menos 6 caracteres.";
        header("Location: novoregisto.php");
        exit();
    }

    // Verificar duplicação de utilizador
    $stmt = $conn->prepare("SELECT id FROM utilizadores WHERE nome_utilizador = ?");
    $stmt->bind_param("s", $nome_utilizador);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['erro'] = "Este nome de utilizador já está registado.";
        $stmt->close();
        $conn->close();
        header("Location: novoregisto.php");
        exit();
    }
    $stmt->close();

    // Encriptação e Inserção
    $password_encriptada = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt_insert = $conn->prepare("INSERT INTO utilizadores (tipo, nome_utilizador, password) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $tipo_utilizador, $nome_utilizador, $password_encriptada);
    
    if ($stmt_insert->execute()) {
        unset($_SESSION['old_nome_utilizador']);
        $_SESSION['sucesso'] = "Conta criada com sucesso! Já pode iniciar sessão.";
        header("Location: ../login.php"); 
        exit();
    } else {
        $_SESSION['erro'] = "Ocorreu um erro ao criar a conta. Tente novamente.";
        header("Location: novoregisto.php");
        exit();
    }

    $stmt_insert->close();
    $conn->close();
} else {
    header("Location: ../login.php");
    exit();
}
?>