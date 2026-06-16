<?php
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

try {
    $db = new SQLite3(__DIR__ . '/../hotel.db');
    $db->enableExceptions(true);

    $stmt = $db->prepare("SELECT id, nome AS name, localizacao AS location, preco AS price FROM hoteis WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $hotel = $result->fetchArray(SQLITE3_ASSOC);

    if ($hotel) {
        echo json_encode($hotel);
    } else {
        echo json_encode(['error' => 'Hotel not found']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
