<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Details - Hotel Management</title>
  <link rel="stylesheet" href="hotel-details.css">
</head>
<body>
  
  <header>
    <div class="logo">LOGO</div>
    <nav class="header-nav">
      <a href="#" class="icon-link" title="Language">🌐</a>
      <a href="#" class="icon-link" title="Help">❓</a>
      <a href="#" class="icon-link" title="Profile">👤</a>
    </nav>
  </header>

  <div class="main-container">
    
    <section class="hotel-header">
      <div class="hotel-info">
        <h1>Lorem ipsum dolor sit amet, consetetur</h1>
        <div class="hotel-meta">
          <span class="meta-item">⭐ 101 guest reviews</span>
          <span class="meta-item">📍 Location</span>
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
        <div class="price-tag">Starting price €</div>
        
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

        <form action="verificar_disponibilidade.php" method="POST" class="booking-form">
          <select name="quarto" required class="date-input" style="width:100%; margin-bottom:10px;">
             <option value="" disabled selected>Escolha o Quarto</option>
             <option value="Single">Single</option>
             <option value="Double">Double</option>
             <option value="Suite">Suite</option>
          </select>
          
          <div class="date-inputs">
            <input type="date" name="check_in" class="date-input" required>
            <input type="date" name="check_out" class="date-input" required>
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