<?php
// 1. Estabelecer ligação à base de dados na raiz
$db = new SQLite3(__DIR__ . '/hotel.db');

// 2. Capturar os dados do formulário (Método POST)
$quarto = $_POST['quarto'];
$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];

// 3. Validação lógica das datas
if (strtotime($check_out) <= strtotime($check_in)) {
    echo "<h3>Erro: A data de saída tem de ser posterior à data de entrada.</h3>";
    echo "<a href='hotelpage.php'>Voltar</a>";
    exit;
}

// 4. Query para verificar colisões na base de dados
$query = "SELECT COUNT(*) as ocupados FROM reservas 
          WHERE quarto = '$quarto' 
          AND status != 'Cancelled' 
          AND check_in < '$check_out' 
          AND check_out > '$check_in'";

$resultado = $db->query($query);
$row = $resultado->fetchArray(SQLITE3_ASSOC);

// 5. Decisão baseada na disponibilidade
if ($row['ocupados'] > 0) {
    // 5.1. OCUPADO: Fica nesta página e avisa o cliente
    echo "<h3>Lamentamos, mas o quarto $quarto não está disponível para as datas selecionadas.</h3>";
    echo "<a href='hotelpage.php'>Voltar e escolher outras datas</a>";
} else {
    // 5.2. LIVRE: Redireciona para o colega, enviando os dados pelo URL (Método GET)
    $url_colega = "scripts/confirmar_reserva.php";
    $parametros = "?quarto=$quarto&check_in=$check_in&check_out=$check_out";
    
    header("Location: " . $url_colega . $parametros);
    exit;
}
?>
