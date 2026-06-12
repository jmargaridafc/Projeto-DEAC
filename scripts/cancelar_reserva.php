<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Erro: O utilizador tem de ter sessão iniciada para gravar uma reserva.");
}

$nome_hospede = $_SESSION['username'];
$quarto = isset($_GET['quarto']) ? trim($_GET['quarto']) : '';
$check_in = isset($_GET['check_in']) ? trim($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? trim($_GET['check_out']) : '';

if ($quarto === '' || $check_in === '' || $check_out === '') {
    die("Erro: Falta de parâmetros algébricos no URL.");
}

$db = new SQLite3(__DIR__ . '/../hotel.db');
$db->enableExceptions(true);

try {
    $stmt_preco = $db->prepare("SELECT preco FROM hoteis WHERE nome = :nome_quarto");
    $stmt_preco->bindValue(':nome_quarto', $quarto, SQLITE3_TEXT);
    $resultado_preco = $stmt_preco->execute();
    $row_preco = $resultado_preco->fetchArray(SQLITE3_ASSOC);
    
    $preco_final = $row_preco ? $row_preco['preco'] : 0.0;

    $stmt_inserir = $db->prepare("INSERT INTO reservas (nome_hospede, quarto, check_in, check_out, status, preco) 
                                  VALUES (:hospede, :quarto, :in, :out, 'Confirmada', :preco)");
    
    $stmt_inserir->bindValue(':hospede', $nome_hospede, SQLITE3_TEXT);
    $stmt_inserir->bindValue(':quarto', $quarto, SQLITE3_TEXT);
    $stmt_inserir->bindValue(':in', $check_in, SQLITE3_TEXT);
    $stmt_inserir->bindValue(':out', $check_out, SQLITE3_TEXT);
    $stmt_inserir->bindValue(':preco', $preco_final, SQLITE3_FLOAT);
    
    $stmt_inserir->execute();

} catch (Exception $e) {
    die("Falha na gravação algébrica: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation - Hotel Management</title>
  <link rel="stylesheet" href="../hotel-details.css">
</head>
<body>
  
  <header>
    <div class="logo"><a href="../index.php" style="text-decoration: none; color: inherit;">LOGO</a></div>
    <nav class="header-nav">
      <a href="#" class="icon-link" title="Language">🌐</a>
      <a href="#" class="icon-link" title="Help">❓</a>
      <a href="#" class="icon-link" title="Profile">👤</a>
    </nav>
  </header>

  <div class="main-container" style="text-align: center; padding: 100px 20px;">
    <h1 style="color: #27ae60;">Reserva Efetuada com Sucesso!</h1>
    <p><strong>Hóspede:</strong> <?php echo htmlspecialchars($nome_hospede); ?></p>
    <p><strong>Hotel/Quarto:</strong> <?php echo htmlspecialchars($quarto); ?></p>
    <p><strong>Valor:</strong> <?php echo number_format($preco_final, 2); ?>€</p>
    <br><br>
    <a href="../reservations_painel.php" style="display: inline-block; padding: 12px 24px; background-color: #4A90E2; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Ir para o meu Painel</a>
  </div>

  <footer>
    <div class="newsletter">
      <p>Subscribe to AdobeXD via Email</p>
      <form><input type="email" placeholder="Email Address"><button type="submit">Subscribe</button></form>
    </div>
  </footer>

</body>
</html>