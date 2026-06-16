<?php
// scripts/verificar_disponibilidade.php

// 1. Ligar à base de dados (apontando um nível acima para a raiz)
$db = new SQLite3(__DIR__ . '/../hotel.db');

// 2. Capturar os dados do formulário (agora via GET)
$hotel_id = trim($_GET['hotel_id'] ?? '');
$check_in = trim($_GET['checkin'] ?? '');
$check_out = trim($_GET['checkout'] ?? '');

// Verificar se não vieram vazios
if (!$hotel_id || !$check_in || !$check_out) {
    die("<h3>Erro: Faltam dados (Hotel ou Datas).</h3><a href='../hotelpage.php'>Voltar</a>");
}

// 3. Validação lógica das datas
if (strtotime($check_out) <= strtotime($check_in)) {
    echo "<h3>Erro: A data de saída tem de ser posterior à data de entrada.</h3>";
    echo "<a href='../hotelpage.php'>Voltar</a>";
    exit;
}
if (strtotime($check_in) < strtotime(date('Y-m-d'))) {
    echo "<h3>Erro: A data de entrada não pode ser no passado.</h3>";
    echo "<a href='../hotelpage.php'>Voltar</a>";
    exit;
}

// 4. Obter o nome do hotel pelo ID (A tabela 'reservas' guarda o nome no campo 'quarto')
$stmtHotel = $db->prepare("SELECT nome FROM hoteis WHERE id = :id");
$stmtHotel->bindValue(':id', $hotel_id, SQLITE3_INTEGER);
$resHotel = $stmtHotel->execute()->fetchArray(SQLITE3_ASSOC);

if (!$resHotel) {
    die("<h3>Erro: Hotel não encontrado.</h3><a href='../hotelpage.php'>Voltar</a>");
}
$quarto_nome = $resHotel['nome'];

// 5. Query para verificar colisões na base de dados
$stmt = $db->prepare("SELECT COUNT(*) as ocupados FROM reservas 
                      WHERE quarto = :quarto 
                      AND status != 'Cancelada' 
                      AND check_in < :checkout 
                      AND check_out > :checkin");
$stmt->bindValue(':quarto', $quarto_nome, SQLITE3_TEXT);
$stmt->bindValue(':checkin', $check_in, SQLITE3_TEXT);
$stmt->bindValue(':checkout', $check_out, SQLITE3_TEXT);
$row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

// 6. Decisão baseada na disponibilidade
if ($row['ocupados'] > 0) {
    // 6.1. OCUPADO: Fica nesta página e avisa o cliente
    echo "<h3>Lamentamos, mas o hotel/quarto \"$quarto_nome\" não está disponível para as datas selecionadas.</h3>";
    echo "<a href='../hotelpage.php'>Voltar e escolher outras datas</a>";
} else {
    // 6.2. LIVRE: Redireciona para o details.html levando os dados vitais no URL
    $url = "../details.html";
    $parametros = "?id=" . urlencode($hotel_id) . "&checkin=" . urlencode($check_in) . "&checkout=" . urlencode($check_out);
    
    header("Location: " . $url . $parametros);
    exit;
}
?>