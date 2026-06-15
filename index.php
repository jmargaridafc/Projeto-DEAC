<?php 
// 1. Inicia a sessão para podermos saber se o administrador está logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Primeiro liga à base de dados para ativar a variável $conn
require_once 'db.php'; 

// 3. Só depois carrega o script que vai ler os hotéis
include_once 'scripts/disponibilidade.php'; 

// 4. Verifica se o utilizad~   or atual é administrador
$isAdmin = (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - Reserva de Hotéis</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/style_filtros.css">
</head>
<body>

    <div id="filterSidebar" class="filter-sidebar">
        <div class="sidebar-header">
            <h3>Filter by:</h3>
            <button id="closeFiltersBtn" class="close-btn">&times;</button>
        </div>
        
        <div class="sidebar-content">
            <div class="filter-group">
                <h4>Price range</h4>
                <input type="range" min="0" max="500" value="250" class="slider">
            </div>

            <hr>

            <div class="filter-group">
                <h4>Property Type</h4>
                <label class="check-container"><input type="checkbox"> Lorem ola dolor sit amet</label>
                <label class="check-container"><input type="checkbox"> Lorem ipsum dolor sit amet</label>
                <label class="check-container"><input type="checkbox"> Lorem ipsum dolor sit amet</label>
            </div>

            <hr>

            <div class="filter-group">
                <h4>Amenities</h4>
                <label class="check-container"><input type="checkbox"> Lorem ipsum dolor sit amet</label>
                <label class="check-container"><input type="checkbox"> Lorem ipsum dolor sit amet</label>
            </div>
        </div>
    </div>

    <div id="sidebarOverlay" class="sidebar-overlay"></div>


    <header class="main-header">
        <div class="header-container">
            <div class="logo">LOGO</div>
            <nav class="header-nav">
                <a href="reservations_painel.php" class="nav-icon" title="Reservas" aria-label="Reservas">🛏️</a>
                <a href="#" class="nav-icon" title="Idioma" aria-label="Idioma">🌐</a>
                <a href="#" class="nav-icon" title="Ajuda" aria-label="Ajuda">❓</a>
                <a href="login.php" class="nav-icon profile-icon" title="Ir para o Login" aria-label="Login">👤</a>
            </nav>
        </div>
        
        <div class="hero-section">
            <div class="search-bar">
                <input type="text" placeholder="Location" class="search-input">
                <input type="text" placeholder="Date" class="search-input">
                <input type="text" placeholder="Guests" class="search-input">
                <button class="search-btn">🔍</button>
            </div>
        </div>
    </header>


    <main class="content-container">
        
        <section class="filters-section">
            <div class="filters-scroll">
                <button id="openFiltersBtn" class="filter-btn active">Filters</button>
                <button class="filter-btn">Lorem ipsum</button>
                <button class="filter-btn">Lorem ipsum</button>
                <button class="filter-btn">Lorem ipsum</button>
                <button class="filter-btn">Lorem ipsum</button>
                <button class="filter-btn">Lorem ipsum</button>
                <button class="filter-btn">Lorem ipsum</button>
            </div>
            <div class="sort-actions">
                <span>↕️ Sort by</span>
                <span>🗺️ See on map</span>
            </div>
        </section>

        <section class="hotel-grid">
            <?php if (!empty($lista_hoteis)): ?>
                <?php foreach ($lista_hoteis as $hotel): 
                    $indisponivel = ($hotel['vagas'] <= 0); 
                ?>
                    
                    <article class="hotel-card <?php echo $indisponivel ? 'card-indisponivel' : ''; ?>" style="position: relative;">
                        
                        <a href="<?php echo $indisponivel ? '#' : 'details.html?id=' . $hotel['id']; ?>" class="hotel-card-link" style="text-decoration: none; color: inherit; display: block;">
                            
                            <div class="card-image-placeholder">
                                <span class="favorite-icon" onclick="event.stopPropagation(); event.preventDefault();">🤍</span>
                                
                                <?php if ($indisponivel): ?>
                                    <div class="badge-indisponivel">Indisponível</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-info">
                                <div class="card-header">
                                    <h3><?php echo htmlspecialchars($hotel['nome']); ?></h3>
                                    <span class="rating"><?php echo htmlspecialchars($hotel['avaliacao']); ?> ★</span>
                                </div>
                                <p class="location"><?php echo htmlspecialchars($hotel['localizacao']); ?></p>
                                
                                <div class="price-action-container" style="display: flex; justify-content: space-between; align-items: center;">
                                    <?php if ($indisponivel): ?>
                                        <p class="price" style="color: #db4455; font-weight: bold;">Esgotado</p>
                                    <?php else: ?>
                                        <p class="price">Price <span><?php echo htmlspecialchars($hotel['preco']); ?></span> €</p>
                                    <?php endif; ?>

                                    <?php if ($isAdmin): ?>
                                        <div class="admin-actions" style="display: flex; gap: 12px; font-size: 1.2rem;" onclick="event.stopPropagation(); event.preventDefault();">
                                            <a href="#" onclick="editarPreco(<?php echo $hotel['id']; ?>, <?php echo $hotel['preco']; ?>); return false;" title="Editar Preço" style="text-decoration: none;">✏️</a>
                                            <a href="#" onclick="if(confirm('Tem a certeza que deseja apagar este hotel?')) { window.location.href='scripts/eliminar_hotel.php?id=<?php echo $hotel['id']; ?>'; } return false;" title="Eliminar Hotel" style="text-decoration: none;">🗑️</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </a>
                    </article>

                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum hotel disponível de momento.</p>
            <?php endif; ?>
        </section>
    </main>


    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-column newsletter">
                <h4>Subscribe to AdobeXD via Email</h4>
                <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia.</p>
                <div class="newsletter-form">
                    <input type="email" placeholder="Email Address">
                    <button>Subscribe</button>
                </div>
            </div>
            <div class="footer-column">
                <p>+44 245 873 993</p>
                <p>adobe@email.com</p>
                <p>Find a store</p>
            </div>
            <div class="footer-column">
                <a href="#">Contact Us</a>
                <a href="#">Ordering & Payment</a>
                <a href="#">Shipping</a>
                <a href="#">Returns</a>
                <a href="#">FAQ</a>
                <a href="#">Sizing Guide</a>
            </div>
            <div class="footer-column">
                <a href="#">About Adobe XD-RAY</a>
                <a href="#">Work With Us</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
                <a href="#">Press Enquiries</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© MARS-CD 2017</p>
            <div class="social-icons">
                <span>🌐</span> <span>🐦</span> <span>📘</span>
            </div>
        </div>
    </footer>

    <script>
    function editarPreco(id, precoAtual) {
        let novoPreco = prompt("Introduza o novo preço do hotel:", precoAtual);
        if (novoPreco !== null && !isNaN(novoPreco) && novoPreco.trim() !== "") {
            window.location.href = "scripts/atualizar_preco.php?id=" + id + "&preco=" + encodeURIComponent(novoPreco);
        }
    }
    </script>
    <script src="scripts/filtros.js"></script>
</body>
</html>