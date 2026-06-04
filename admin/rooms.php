<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? 'list';

if ($action === 'list')              { listRooms(); }
elseif ($action === 'get')           { getRoom(); }
elseif ($action === 'availability')  { checkAvailability(); }
elseif ($action === 'types')         { listRoomTypes(); }
elseif ($action === 'create')        { createRoom(); }
elseif ($action === 'update')        { updateRoom(); }
elseif ($action === 'update_status') { updateRoomStatus(); }
elseif ($action === 'delete')        { deleteRoom(); }
elseif ($action === 'create_type')   { createRoomType(); }
elseif ($action === 'update_type')   { updateRoomType(); }
else                                 { respond(400, 'Ação inválida.'); }

function listRooms(): void {
    $db      = getDB();
    $hotelId = (int)($_GET['hotel_id'] ?? 1);
    $status  = trim($_GET['status'] ?? '');
    $where   = ['rm.hotel_id = ?'];
    $params  = [$hotelId];
    if ($status !== '') { $where[] = 'rm.status = ?'; $params[] = $status; }
    $whereSQL = implode(' AND ', $where);
    $stmt = $db->prepare("SELECT rm.id, rm.number, rm.floor, rm.status, rt.id AS type_id, rt.name AS type_name, rt.price, rt.capacity, rt.image_url, rt.description AS type_description FROM rooms rm JOIN room_types rt ON rt.id = rm.room_type_id WHERE {$whereSQL} ORDER BY rm.number");
    foreach ($params as $i => $val) { $stmt->bindValue($i + 1, $val); }
    $result = $stmt->execute();
    $rows   = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $rows[] = $row; }
    respond(200, 'OK', $rows);
}

function getRoom(): void {
    $id   = (int)($_GET['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare("SELECT rm.*, rt.name AS type_name, rt.price, rt.capacity, rt.description AS type_description, rt.image_url FROM rooms rm JOIN room_types rt ON rt.id = rm.room_type_id WHERE rm.id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$row) respond(404, 'Quarto não encontrado.');
    respond(200, 'OK', $row);
}

function checkAvailability(): void {
    $hotelId  = (int)($_GET['hotel_id']  ?? 1);
    $checkIn  = trim($_GET['check_in']   ?? '');
    $checkOut = trim($_GET['check_out']  ?? '');
    if (!$checkIn || !$checkOut) respond(422, 'Datas obrigatórias.');
    $db   = getDB();
    $stmt = $db->prepare("SELECT rm.id, rm.number, rm.floor, rt.name AS type_name, rt.price, rt.capacity, rt.image_url, rt.description FROM rooms rm JOIN room_types rt ON rt.id = rm.room_type_id WHERE rm.hotel_id=? AND rm.status='available' AND rm.id NOT IN (SELECT r.room_id FROM reservations r WHERE r.status!='cancelled' AND r.check_in<? AND r.check_out>?) ORDER BY rt.price");
    $stmt->bindValue(1, $hotelId,  SQLITE3_INTEGER);
    $stmt->bindValue(2, $checkOut, SQLITE3_TEXT);
    $stmt->bindValue(3, $checkIn,  SQLITE3_TEXT);
    $result = $stmt->execute();
    $rows   = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $rows[] = $row; }
    respond(200, 'OK', $rows);
}

function listRoomTypes(): void {
    $hotelId = (int)($_GET['hotel_id'] ?? 1);
    $db      = getDB();
    $stmt    = $db->prepare('SELECT * FROM room_types WHERE hotel_id=? ORDER BY price');
    $stmt->bindValue(1, $hotelId, SQLITE3_INTEGER);
    $result  = $stmt->execute();
    $rows    = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $rows[] = $row; }
    respond(200, 'OK', $rows);
}

function createRoom(): void {
    $hotelId = (int)($_POST['hotel_id']     ?? 0);
    $typeId  = (int)($_POST['room_type_id'] ?? 0);
    $number  = trim($_POST['number']        ?? '');
    $floor   = $_POST['floor'] !== '' ? (int)$_POST['floor'] : null;
    if (!$hotelId || !$typeId || !$number) respond(422, 'Campos obrigatórios em falta.');
    $db   = getDB();
    $stmt = $db->prepare("INSERT INTO rooms (hotel_id, room_type_id, number, floor, status) VALUES (?, ?, ?, ?, 'available')");
    $stmt->bindValue(1, $hotelId, SQLITE3_INTEGER);
    $stmt->bindValue(2, $typeId,  SQLITE3_INTEGER);
    $stmt->bindValue(3, $number,  SQLITE3_TEXT);
    $stmt->bindValue(4, $floor,   $floor !== null ? SQLITE3_INTEGER : SQLITE3_NULL);
    $stmt->execute();
    respond(201, 'Quarto criado.', ['id' => $db->lastInsertRowID()]);
}

function updateRoom(): void {
    $id     = (int)($_POST['id']           ?? 0);
    $typeId = (int)($_POST['room_type_id'] ?? 0);
    $number = trim($_POST['number']        ?? '');
    $floor  = $_POST['floor'] !== '' ? (int)$_POST['floor'] : null;
    if (!$id) respond(422, 'ID obrigatório.');
    $db   = getDB();
    $stmt = $db->prepare('UPDATE rooms SET room_type_id=?, number=?, floor=? WHERE id=?');
    $stmt->bindValue(1, $typeId, SQLITE3_INTEGER);
    $stmt->bindValue(2, $number, SQLITE3_TEXT);
    $stmt->bindValue(3, $floor,  $floor !== null ? SQLITE3_INTEGER : SQLITE3_NULL);
    $stmt->bindValue(4, $id,     SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Quarto atualizado.');
}

function updateRoomStatus(): void {
    $id      = (int)($_POST['id']    ?? 0);
    $status  = trim($_POST['status'] ?? '');
    $allowed = ['available', 'occupied', 'maintenance'];
    if (!in_array($status, $allowed, true)) respond(422, 'Status inválido.');
    $db   = getDB();
    $stmt = $db->prepare('UPDATE rooms SET status=? WHERE id=?');
    $stmt->bindValue(1, $status, SQLITE3_TEXT);
    $stmt->bindValue(2, $id,     SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Status atualizado.');
}

function deleteRoom(): void {
    if (!isAdmin()) respond(403, 'Sem permissão.');
    $id   = (int)($_POST['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare('DELETE FROM rooms WHERE id=?');
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Quarto eliminado.');
}

function createRoomType(): void {
    $hotelId = (int)($_POST['hotel_id'] ?? 0);
    $name    = trim($_POST['name']      ?? '');
    $price   = (float)($_POST['price']  ?? 0);
    $desc    = trim($_POST['description'] ?? '');
    $cap     = (int)($_POST['capacity'] ?? 2);
    $imgUrl  = trim($_POST['image_url'] ?? '');
    if (!$hotelId || !$name || !$price) respond(422, 'Campos obrigatórios em falta.');
    $db   = getDB();
    $stmt = $db->prepare("INSERT INTO room_types (hotel_id, name, description, price, capacity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $hotelId, SQLITE3_INTEGER);
    $stmt->bindValue(2, $name,    SQLITE3_TEXT);
    $stmt->bindValue(3, $desc,    SQLITE3_TEXT);
    $stmt->bindValue(4, $price,   SQLITE3_FLOAT);
    $stmt->bindValue(5, $cap,     SQLITE3_INTEGER);
    $stmt->bindValue(6, $imgUrl,  SQLITE3_TEXT);
    $stmt->execute();
    respond(201, 'Tipo de quarto criado.', ['id' => $db->lastInsertRowID()]);
}

function updateRoomType(): void {
    $id    = (int)($_POST['id']         ?? 0);
    $name  = trim($_POST['name']        ?? '');
    $price = (float)($_POST['price']    ?? 0);
    $desc  = trim($_POST['description'] ?? '');
    $cap   = (int)($_POST['capacity']   ?? 2);
    $img   = trim($_POST['image_url']   ?? '');
    if (!$id) respond(422, 'ID obrigatório.');
    $db   = getDB();
    $stmt = $db->prepare("UPDATE room_types SET name=?, description=?, price=?, capacity=?, image_url=? WHERE id=?");
    $stmt->bindValue(1, $name,  SQLITE3_TEXT);
    $stmt->bindValue(2, $desc,  SQLITE3_TEXT);
    $stmt->bindValue(3, $price, SQLITE3_FLOAT);
    $stmt->bindValue(4, $cap,   SQLITE3_INTEGER);
    $stmt->bindValue(5, $img,   SQLITE3_TEXT);
    $stmt->bindValue(6, $id,    SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Tipo de quarto atualizado.');
}

function respond(int $code, string $message, mixed $data = null): never {
    http_response_code($code);
    $payload = ['status' => $code, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}