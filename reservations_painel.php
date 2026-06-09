<?php
// 1. Iniciar o motor de sessões do PHP
session_start();

// 2. Verificação Restrita de Acesso
if (!isset($_SESSION['user_id'])) {
    // Se o colega que fez o login não gerou a sessão, o utilizador é expulso
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Reservations - Hotel Management</title>

  
</head>
<body>
  
  <!-- CABEÇALHO -->
  <header>
    <div class="logo">LOGO</div>
    <nav class="header-nav">
      <a href="#" class="icon-link" title="Language">🌐</a>
      <a href="#" class="icon-link" title="Help">❓</a>
      <a href="#" class="icon-link" title="Profile">👤</a>
    </nav>
  </header>

  <!-- CONTENTOR PRINCIPAL -->
  <div class="main-container">
    
    <!-- MENU LATERAL -->
    <aside class="sidebar">
      <nav>
        <ul>
          <li><a href="reservations.html" class="active">Reservations</a></li>
          <li><a href="rooms.html">Rooms</a></li>
          <li><a href="guests.html">Guests</a></li>
        </ul>
      </nav>
    </aside>

    <!-- ÁREA DE CONTEÚDO PRINCIPAL -->
    <main class="content">
      
      <h1>Manage Reservations</h1>

      <!-- BARRA DE PESQUISA -->
      <div class="search-bar">
        <input type="text" placeholder="Search by guest name, reservation ID or room">
      </div>

      <!-- FILTROS -->
      <div class="filters">
        <div class="filter-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="">All</option>
            <option value="confirmed">Confirmed</option>
            <option value="pending">Pending</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="daterange">Date Range</label>
          <input type="text" id="daterange" name="daterange" placeholder="Select dates">
        </div>

        <div class="filter-group">
          <label for="roomtype">Room type</label>
          <select id="roomtype" name="roomtype">
            <option value="">All</option>
            <option value="single">Single</option>
            <option value="double">Double</option>
            <option value="suite">Suite</option>
          </select>
        </div>
      </div>

      <!-- TABELA DE RESERVAS -->
      <table class="reservations-table">
        <thead>
          <tr>
            <th>Res. ID</th>
            <th>Guest name</th>
            <th>Room</th>
            <th>Check-IN</th>
            <th>Check-OUT</th>
            <th>Status</th>
            <th>Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // 1. Estabelecer ligação apontando duas pastas acima (raiz)
          $db = new SQLite3(__DIR__ . '/../hotel.db');
          
          // 2. Extrair todas as reservas existentes
          $result = $db->query("SELECT * FROM reservas");
          
          // 3. Iterar e imprimir cada reserva numa linha dinâmica
          while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
              echo '<tr>';
              echo '<td>R00' . $row['id'] . '</td>';
              echo '<td>' . $row['nome_hospede'] . '</td>';
              echo '<td>' . $row['quarto'] . '</td>';
              echo '<td>' . $row['check_in'] . '</td>';
              echo '<td>' . $row['check_out'] . '</td>';
              echo '<td>' . $row['status'] . '</td>';
              echo '<td>€' . $row['preco'] . '</td>';
              echo '<td><button class="btn-action">View</button></td>';
              echo '</tr>';
          }
          ?>
        </tbody>
      </table>

    </main>

  </div>

  <!-- RODAPÉ -->
  <footer>
    <div class="newsletter">
      <p>Subscribe to AdobeXD via Email</p>
      <p>Excepteur sint occaecat cupidatat non proident...</p>
      <form>
        <input type="email" placeholder="Email Address">
        <button type="submit">contact us</button>
      </form>
    </div>
  </footer>

</body>
</html>