<!DOCTYPE html>
<?php
$db_path = __DIR__ . '/hotel.db';
$db = new SQLite3($db_path);
$db->enableExceptions(true);

// Obter todos os hotéis para o formulário (agora trazemos TUDO: nome, preco, localizacao, etc)
$query = $db->query("SELECT * FROM hoteis");
$dados_hoteis = [];
while ($linha = $query->fetchArray(SQLITE3_ASSOC)) {
    $dados_hoteis[] = $linha;
}

// Descobrir em qual hotel o utilizador clicou no index
$id_recebido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$hotel_selecionado = null;

foreach ($dados_hoteis as $hotel) {
    if ($hotel['id'] === $id_recebido) {
        $hotel_selecionado = $hotel;
        break;
    }
}

// Se não vier nenhum ID no link por acidente, assumimos o 1º hotel para a página não "partir"
if (!$hotel_selecionado && count($dados_hoteis) > 0) {
    $hotel_selecionado = $dados_hoteis[0];
}

?>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Details - Hotel Management</title>
  <link rel="stylesheet" href="hotel-details.css">
</head>
<body>
  
  <header>
    <div class="logo">
      <a href="index.php" style="text-decoration: none; color: inherit;">LOGO</a>
    </div>
    <nav class="header-nav">
      <a href="#" class="icon-link" title="Language">🌐</a>
      <a href="#" class="icon-link" title="Help">❓</a>
      <a href="#" class="icon-link" title="Profile">👤</a>
    </nav>
  </header>

  <div class="main-container">
    
    <section class="hotel-header">
  <div class="hotel-info">
    <h1><?php echo htmlspecialchars($hotel_selecionado['nome']); ?></h1>
    <div class="hotel-meta">
      <span class="meta-item">⭐ <?php echo number_format($hotel_selecionado['avaliacao'], 1); ?> Rating</span>
      <span class="meta-item">📍 <?php echo htmlspecialchars($hotel_selecionado['localizacao']); ?></span>
      <span class="meta-item verified">✓ Verified Information</span>
    </div>
  </div>
</section>

    <section class="gallery-booking">
      
      <div class="gallery-container">
        <div class="gallery-grid">
          <div class="gallery-main">
            <img src="https://via.placeholder.com/600x400/E3F2FD/4A90E2?text=Main+Image" alt="Hotel Main">
          </div>
          <div class="gallery-secondary">
            <img src="https://via.placeholder.com/290x195/E3F2FD/4A90E2?text=Image+2" alt="Hotel 2">
            <img src="https://via.placeholder.com/290x195/E3F2FD/4A90E2?text=Image+3" alt="Hotel 3">
          </div>
          <div class="gallery-tertiary">
            <img src="https://via.placeholder.com/190x195/E3F2FD/4A90E2?text=Image+4" alt="Hotel 4">
            <img src="https://via.placeholder.com/190x195/E3F2FD/4A90E2?text=Image+5" alt="Hotel 5">
            <img src="https://via.placeholder.com/190x195/E3F2FD/4A90E2?text=Image+6" alt="Hotel 6">
          </div>
        </div>
        
        <div class="image-tags">
          <span class="tag">Lorem ipsum</span>
          <span class="tag">Lorem ipsum</span>
          <span class="tag">Lorem ipsum</span>
          <span class="tag">Lorem ipsum</span>
          <span class="tag">Lorem ipsum</span>
        </div>
      </div>

      <aside class="booking-sidebar">
        <div class="price-tag">Price: <?php echo number_format($hotel_selecionado['preco'], 2); ?>€ / night</div>
        
        <div class="rating-section">
          <div class="rating-number">4.7 ⭐</div>
          <div class="rating-label">Lorem ipsum</div>
        </div>

        <div class="quote-section">
          <div class="quote-icon">❝❞</div>
          <p class="quote-text">Lorem ipsum dolor sit amet, consetetur adipiscing elit, sed diam nonumy eirmod.</p>
          <a href="#" class="read-more">See more guests reviews</a>
        </div>

        <div class="action-icons">
          <button class="icon-btn" title="Share">🔗</button>
          <button class="icon-btn" title="Favorite">❤️</button>
          <button class="icon-btn" title="More">⋯</button>
        </div>

        <form action="scripts/verificar_disponibilidade.php" method="GET" class="booking-form" id="formTriagem">
  
  <input type="hidden" name="hotel_id" value="<?php echo htmlspecialchars($hotel_selecionado['id']); ?>">

  <select name="tipo_quarto" class="date-input" style="width:100%; margin-bottom:10px;" id="quartoSelect" required>
     <option value="" disabled selected>Escolha o tipo de quarto</option>
     <option value="Single">Quarto Single</option>
     <option value="Double">Quarto Duplo</option>
     <option value="Suite">Suite</option>
  </select>
  
  <div class="date-inputs">
    <input type="date" name="checkin" class="date-input" id="checkInInput" required>
    <input type="date" name="checkout" class="date-input" id="checkOutInput" required>
  </div>
  
  <button type="submit" class="btn-availability">See availability</button>
</form>

      </aside>

    </section>

    <section class="content-section">
      <h2>About</h2>
      <p>Lorem ipsum dolor sit amet, consetetur adipiscing elit.</p>
    </section>

  </div>

  <footer>
    <div class="footer-content">
      <div class="footer-left">
        <p>Subscribe to AdobeXD via Email</p>
        <p class="footer-subtitle">Excepteur sint occaecat cupidatat non proident sunt...</p>
        
        <form action="processa_newsletter.php" method="POST" class="newsletter-form">
          <input type="email" name="email_inserido" placeholder="Email Address" class="email-input" required>
          <button type="submit" class="btn-submit">Subscribe</button>
        </form>

      </div>
    </div>
  </footer>

<script src="scripts/hotel_validation.js"></script>

</body>
</html>