<?php
// scripts/verificar_disponibilidade.php

$db = new SQLite3(__DIR__ . '/../hotel.db');

$hotel_id = trim($_GET['hotel_id'] ?? '');
$check_in = trim($_GET['checkin'] ?? '');
$check_out = trim($_GET['checkout'] ?? '');
// NOVA LINHA: Capturar o tipo de quarto
$tipo_quarto = trim($_GET['tipo_quarto'] ?? '');

if (!$hotel_id || !$check_in || !$check_out) {
    die("<h3>Erro: Faltam dados (Hotel ou Datas).</h3><a href='../hotelpage.php'>Voltar</a>");
}

if (strtotime($check_out) <= strtotime($check_in)) {
    echo "<h3>Erro: A data de saída tem de ser posterior à data de entrada.</h3><a href='../hotelpage.php'>Voltar</a>";
    exit;
}
if (strtotime($check_in) < strtotime(date('Y-m-d'))) {
    echo "<h3>Erro: A data de entrada não pode ser no passado.</h3><a href='../hotelpage.php'>Voltar</a>";
    exit;
}

$stmtHotel = $db->prepare("SELECT nome FROM hoteis WHERE id = :id");
$stmtHotel->bindValue(':id', $hotel_id, SQLITE3_INTEGER);
$resHotel = $stmtHotel->execute()->fetchArray(SQLITE3_ASSOC);

if (!$resHotel) {
    die("<h3>Erro: Hotel não encontrado.</h3><a href='../hotelpage.php'>Voltar</a>");
}

// NOVA LÓGICA: Junta o nome do hotel com o tipo de quarto para gravar e verificar
$quarto_completo = $resHotel['nome'] . " - " . $tipo_quarto;

$stmt = $db->prepare("SELECT COUNT(*) as ocupados FROM reservas 
                      WHERE quarto = :quarto 
                      AND status != 'Cancelada' 
                      AND check_in < :checkout 
                      AND check_out > :checkin");
$stmt->bindValue(':quarto', $quarto_completo, SQLITE3_TEXT);
$stmt->bindValue(':checkin', $check_in, SQLITE3_TEXT);
$stmt->bindValue(':checkout', $check_out, SQLITE3_TEXT);
$row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($row['ocupados'] > 0) {
    echo "<h3>Lamentamos, mas o quarto \"$tipo_quarto\" no hotel \"{$resHotel['nome']}\" não está disponível para as datas selecionadas.</h3>";
    echo "<a href='../hotelpage.php'>Voltar e escolher outras datas</a>";
} else {
    // Redireciona enviando também o tipo de quarto no URL
    $url = "../details.html";
    $parametros = "?id=" . urlencode($hotel_id) . "&checkin=" . urlencode($check_in) . "&checkout=" . urlencode($check_out) . "&tipo_quarto=" . urlencode($tipo_quarto);
    
    header("Location: " . $url . $parametros);
    exit;
}
?>
