<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? 'list';

if ($action === 'list')         { listReservations(); }
elseif ($action === 'get')      { getReservation(); }
elseif ($action === 'create')   { createReservation(); }
elseif ($action === 'update')   { updateReservation(); }
elseif ($action === 'cancel')   { cancelReservation(); }
elseif ($action === 'delete')   { deleteReservation(); }
else                            { respond(400, 'Invalid action.'); }

function listReservations(): void {
    $db       = getDB();
    $search   = trim($_GET['search']    ?? '');
    $status   = trim($_GET['status']    ?? '');
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo   = trim($_GET['date_to']   ?? '');
    $roomType = trim($_GET['room_type'] ?? '');
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $perPage  = 20;
    $offset   = ($page - 1) * $perPage;

    $where  = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[]  = "(g.first_name LIKE '%' || ? || '%' OR g.last_name LIKE '%' || ? || '%' OR r.id LIKE '%' || ? || '%' OR rm.number LIKE '%' || ? || '%')";
        array_push($params, $search, $search, $search, $search);
    }
    if ($status !== '') {
        $where[]  = 'r.status = ?';
        $params[] = $status;
    }
    if ($dateFrom !== '') {
        $where[]  = 'r.check_in >= ?';
        $params[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $where[]  = 'r.check_out <= ?';
        $params[] = $dateTo;
    }
    if ($roomType !== '') {
        $where[]  = 'rt.name = ?';
        $params[] = $roomType;
    }

    $whereSQL  = implode(' AND ', $where);
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM reservations r JOIN guests g ON g.id = r.guest_id JOIN rooms rm ON rm.id = r.room_id JOIN room_types rt ON rt.id = rm.room_type_id WHERE {$whereSQL}");
    foreach ($params as $i => $val) { $countStmt->bindValue($i + 1, $val, SQLITE3_TEXT); }
    $total = (int)$countStmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];

    $stmt = $db->prepare("SELECT r.id AS res_id, g.first_name || ' ' || g.last_name AS guest_name, g.email AS guest_email, rm.number AS room_number, rt.name AS room_type, r.check_in, r.check_out, r.status, r.total_price, r.notes, r.created_at FROM reservations r JOIN guests g ON g.id = r.guest_id JOIN rooms rm ON rm.id = r.room_id JOIN room_types rt ON rt.id = rm.room_type_id WHERE {$whereSQL} ORDER BY r.created_at DESC LIMIT ? OFFSET ?");
    foreach ($params as $i => $val) { $stmt->bindValue($i + 1, $val, SQLITE3_TEXT); }
    $stmt->bindValue(count($params) + 1, $perPage, SQLITE3_INTEGER);
    $stmt->bindValue(count($params) + 2, $offset,  SQLITE3_INTEGER);

    $result = $stmt->execute();
    $rows   = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $rows[] = $row; }

    respond(200, 'OK', [
        'data'       => $rows,
        'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => (int)ceil($total / $perPage)],
    ]);
}

function getReservation(): void {
    $id   = (int)($_GET['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare("SELECT r.*, g.first_name || ' ' || g.last_name AS guest_name, g.email, g.phone, rm.number AS room_number, rt.name AS room_type, rt.price AS room_price FROM reservations r JOIN guests g ON g.id = r.guest_id JOIN rooms rm ON rm.id = r.room_id JOIN room_types rt ON rt.id = rm.room_type_id WHERE r.id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$row) respond(404, 'Reservation not found.');
    respond(200, 'OK', $row);
}

function createReservation(): void {
    $guestId  = (int)($_POST['guest_id']  ?? 0);
    $roomId   = (int)($_POST['room_id']   ?? 0);
    $checkIn  = trim($_POST['check_in']   ?? '');
    $checkOut = trim($_POST['check_out']  ?? '');
    $notes    = trim($_POST['notes']      ?? '');

    if (!$guestId || !$roomId || !$checkIn || !$checkOut) respond(422, 'Campos obrigatórios em falta.');
    if (strtotime($checkOut) <= strtotime($checkIn)) respond(422, 'Check-out deve ser após o check-in.');

    $db = getDB();
    if (!isRoomAvailable($db, $roomId, $checkIn, $checkOut)) respond(409, 'Quarto não disponível.');

    $priceStmt = $db->prepare('SELECT rt.price FROM rooms rm JOIN room_types rt ON rt.id = rm.room_type_id WHERE rm.id = ?');
    $priceStmt->bindValue(1, $roomId, SQLITE3_INTEGER);
    $priceResult   = $priceStmt->execute()->fetchArray(SQLITE3_ASSOC);
    $pricePerNight = (float)($priceResult['price'] ?? 0);
    $nights        = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
    $totalPrice    = $pricePerNight * $nights;

    $stmt = $db->prepare("INSERT INTO reservations (guest_id, room_id, check_in, check_out, status, total_price, notes) VALUES (?, ?, ?, ?, 'pending', ?, ?)");
    $stmt->bindValue(1, $guestId,    SQLITE3_INTEGER);
    $stmt->bindValue(2, $roomId,     SQLITE3_INTEGER);
    $stmt->bindValue(3, $checkIn,    SQLITE3_TEXT);
    $stmt->bindValue(4, $checkOut,   SQLITE3_TEXT);
    $stmt->bindValue(5, $totalPrice, SQLITE3_FLOAT);
    $stmt->bindValue(6, $notes,      SQLITE3_TEXT);
    $stmt->execute();

    respond(201, 'Reserva criada.', ['id' => $db->lastInsertRowID(), 'total_price' => $totalPrice]);
}

function updateReservation(): void {
    $id       = (int)($_POST['id']       ?? 0);
    $roomId   = (int)($_POST['room_id']  ?? 0);
    $checkIn  = trim($_POST['check_in']  ?? '');
    $checkOut = trim($_POST['check_out'] ?? '');
    $status   = trim($_POST['status']    ?? 'pending');
    $notes    = trim($_POST['notes']     ?? '');

    if (!$id) respond(422, 'ID obrigatório.');
    if (strtotime($checkOut) <= strtotime($checkIn)) respond(422, 'Check-out deve ser após o check-in.');

    $db = getDB();
    if (!isRoomAvailable($db, $roomId, $checkIn, $checkOut, $id)) respond(409, 'Quarto não disponível.');

    $priceStmt = $db->prepare('SELECT rt.price FROM rooms rm JOIN room_types rt ON rt.id = rm.room_type_id WHERE rm.id = ?');
    $priceStmt->bindValue(1, $roomId, SQLITE3_INTEGER);
    $priceResult   = $priceStmt->execute()->fetchArray(SQLITE3_ASSOC);
    $pricePerNight = (float)($priceResult['price'] ?? 0);
    $nights        = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
    $totalPrice    = $pricePerNight * $nights;

    $stmt = $db->prepare("UPDATE reservations SET room_id=?, check_in=?, check_out=?, status=?, total_price=?, notes=?, updated_at=datetime('now') WHERE id=?");
    $stmt->bindValue(1, $roomId,     SQLITE3_INTEGER);
    $stmt->bindValue(2, $checkIn,    SQLITE3_TEXT);
    $stmt->bindValue(3, $checkOut,   SQLITE3_TEXT);
    $stmt->bindValue(4, $status,     SQLITE3_TEXT);
    $stmt->bindValue(5, $totalPrice, SQLITE3_FLOAT);
    $stmt->bindValue(6, $notes,      SQLITE3_TEXT);
    $stmt->bindValue(7, $id,         SQLITE3_INTEGER);
    $stmt->execute();

    respond(200, 'Reserva atualizada.', ['total_price' => $totalPrice]);
}

function cancelReservation(): void {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) respond(422, 'ID obrigatório.');
    $db   = getDB();
    $stmt = $db->prepare("UPDATE reservations SET status='cancelled', updated_at=datetime('now') WHERE id=?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Reserva cancelada.');
}

function deleteReservation(): void {
    if (!isAdmin()) respond(403, 'Sem permissão.');
    $id   = (int)($_POST['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare('DELETE FROM reservations WHERE id=?');
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Reserva eliminada.');
}

function isRoomAvailable(SQLite3 $db, int $roomId, string $checkIn, string $checkOut, int $excludeId = 0): bool {
    $stmt = $db->prepare("SELECT COUNT(*) as n FROM reservations WHERE room_id=? AND id!=? AND status!='cancelled' AND check_in<? AND check_out>?");
    $stmt->bindValue(1, $roomId,    SQLITE3_INTEGER);
    $stmt->bindValue(2, $excludeId, SQLITE3_INTEGER);
    $stmt->bindValue(3, $checkOut,  SQLITE3_TEXT);
    $stmt->bindValue(4, $checkIn,   SQLITE3_TEXT);
    return (int)$stmt->execute()->fetchArray(SQLITE3_ASSOC)['n'] === 0;
}

function respond(int $code, string $message, mixed $data = null): never {
    http_response_code($code);
    $payload = ['status' => $code, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}