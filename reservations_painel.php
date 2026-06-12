<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// 1. Extração de Variáveis do Colega (Definição de Coordenadas de Segurança)
$tipo_utilizador = isset($_SESSION['tipo']) ? $_SESSION['tipo'] : 'cliente';
$nome_utilizador = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Reservations - Hotel Management</title>
  <link rel="stylesheet" href="hotel-details.css">
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
    

    <!-- ÁREA DE CONTEÚDO PRINCIPAL -->
    <main class="content">
      
      <h1>Manage Reservations</h1>

      <!-- BARRA DE PESQUISA -->
      <div class="filters" style="display: flex; gap: 20px; align-items: center; margin-bottom: 30px; margin-top: 10px;">
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
      <table class="reservations-table" style="width: 100%; text-align: left; border-collapse: collapse;">
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
          $db = new SQLite3(__DIR__ . '/../hotel.db');
          
          // 1. Lógica de Ramificação de Permissões
          if ($tipo_utilizador === 'admin') {
              $stmt = $db->prepare("SELECT * FROM reservas");
          } else {
              $stmt = $db->prepare("SELECT * FROM reservas WHERE nome_hospede = :nome");
              $stmt->bindValue(':nome', $nome_utilizador, SQLITE3_TEXT);
          }
          
          $result = $stmt->execute();
          
          // 2. Geração Dinâmica da Interface
          while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
              echo '<tr>';
              echo '<td>R00' . $row['id'] . '</td>';
              echo '<td>' . htmlspecialchars($row['nome_hospede']) . '</td>';
              echo '<td>' . htmlspecialchars($row['quarto']) . '</td>';
              echo '<td>' . $row['check_in'] . '</td>';
              echo '<td>' . $row['check_out'] . '</td>';
              
              // 3. Controlo Visual de Estado
              $cor_status = ($row['status'] === 'Cancelada') ? 'color: #e74c3c; font-weight: bold;' : 'color: #27ae60; font-weight: bold;';
              echo '<td style="' . $cor_status . '">' . htmlspecialchars($row['status']) . '</td>';
              echo '<td>€' . number_format($row['preco'], 2) . '</td>';
              
              // 4. Injeção do Motor de Cancelamento
              echo '<td>';
              if ($row['status'] !== 'Cancelada') {
                  echo '<form action="scripts/cancelar_reserva.php" method="POST" style="margin: 0;">';
                  echo '<input type="hidden" name="id_reserva" value="' . $row['id'] . '">';
                  echo '<button type="submit" class="btn-action" style="background-color: #e74c3c; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px;">Cancelar</button>';
                  echo '</form>';
              } else {
                  echo '<span style="color: grey; font-size: 0.9em;">Cancelada</span>';
              }
              echo '</td>';
              
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