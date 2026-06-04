<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? 'list';

if ($action === 'list')        { listGuests(); }
elseif ($action === 'get')     { getGuest(); }
elseif ($action === 'history') { guestHistory(); }
elseif ($action === 'create')  { createGuest(); }
elseif ($action === 'update')  { updateGuest(); }
elseif ($action === 'delete')  { deleteGuest(); }
else                           { respond(400, 'Ação inválida.'); }

function listGuests(): void {
    $db      = getDB();
    $search  = trim($_GET['search'] ?? '');
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;
    $offset  = ($page - 1) * $perPage;
    $where   = ['1=1'];
    $params  = [];
    if ($search !== '') {
        $where[] = "(first_name LIKE '%' || ? || '%' OR last_name LIKE '%' || ? || '%' OR email LIKE '%' || ? || '%')";
        array_push($params, $search, $search, $search);
    }
    $whereSQL  = implode(' AND ', $where);
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM guests WHERE {$whereSQL}");
    foreach ($params as $i => $val) { $countStmt->bindValue($i + 1, $val, SQLITE3_TEXT); }
    $total = (int)$countStmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];
    $stmt = $db->prepare("SELECT g.id, g.first_name, g.last_name, g.email, g.phone, g.nationality, g.created_at, COUNT(r.id) AS total_reservations, MAX(r.check_in) AS last_check_in FROM guests g LEFT JOIN reservations r ON r.guest_id = g.id WHERE {$whereSQL} GROUP BY g.id ORDER BY g.last_name, g.first_name LIMIT ? OFFSET ?");
    foreach ($params as $i => $val) { $stmt->bindValue($i + 1, $val, SQLITE3_TEXT); }
    $stmt->bindValue(count($params) + 1, $perPage, SQLITE3_INTEGER);
    $stmt->bindValue(count($params) + 2, $offset,  SQLITE3_INTEGER);
    $result = $stmt->execute();
    $rows   = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $rows[] = $row; }
    respond(200, 'OK', ['data' => $rows, 'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => (int)ceil($total / $perPage)]]);
}

function getGuest(): void {
    $id   = (int)($_GET['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM guests WHERE id=?');
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $row  = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$row) respond(404, 'Hóspede não encontrado.');
    respond(200, 'OK', $row);
}

function guestHistory(): void {
    $id   = (int)($_GET['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare("SELECT r.id AS res_id, r.check_in, r.check_out, r.status, r.total_price, rm.number AS room_number, rt.name AS room_type FROM reservations r JOIN rooms rm ON rm.id = r.room_id JOIN room_types rt ON rt.id = rm.room_type_id WHERE r.guest_id=? ORDER BY r.check_in DESC");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $rows   = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { $rows[] = $row; }
    respond(200, 'OK', $rows);
}

function createGuest(): void {
    $firstName   = trim($_POST['first_name']  ?? '');
    $lastName    = trim($_POST['last_name']   ?? '');
    $email       = trim($_POST['email']       ?? '');
    $phone       = trim($_POST['phone']       ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $idDoc       = trim($_POST['id_document'] ?? '');
    if (!$firstName || !$lastName || !$email) respond(422, 'Campos obrigatórios em falta.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(422, 'Email inválido.');
    $db   = getDB();
    $stmt = $db->prepare("INSERT INTO guests (first_name, last_name, email, phone, nationality, id_document) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $firstName,   SQLITE3_TEXT);
    $stmt->bindValue(2, $lastName,    SQLITE3_TEXT);
    $stmt->bindValue(3, $email,       SQLITE3_TEXT);
    $stmt->bindValue(4, $phone,       SQLITE3_TEXT);
    $stmt->bindValue(5, $nationality, SQLITE3_TEXT);
    $stmt->bindValue(6, $idDoc,       SQLITE3_TEXT);
    $stmt->execute();
    respond(201, 'Hóspede criado.', ['id' => $db->lastInsertRowID()]);
}

function updateGuest(): void {
    $id          = (int)($_POST['id']          ?? 0);
    $firstName   = trim($_POST['first_name']   ?? '');
    $lastName    = trim($_POST['last_name']    ?? '');
    $email       = trim($_POST['email']        ?? '');
    $phone       = trim($_POST['phone']        ?? '');
    $nationality = trim($_POST['nationality']  ?? '');
    $idDoc       = trim($_POST['id_document']  ?? '');
    if (!$id) respond(422, 'ID obrigatório.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(422, 'Email inválido.');
    $db   = getDB();
    $stmt = $db->prepare("UPDATE guests SET first_name=?, last_name=?, email=?, phone=?, nationality=?, id_document=? WHERE id=?");
    $stmt->bindValue(1, $firstName,   SQLITE3_TEXT);
    $stmt->bindValue(2, $lastName,    SQLITE3_TEXT);
    $stmt->bindValue(3, $email,       SQLITE3_TEXT);
    $stmt->bindValue(4, $phone,       SQLITE3_TEXT);
    $stmt->bindValue(5, $nationality, SQLITE3_TEXT);
    $stmt->bindValue(6, $idDoc,       SQLITE3_TEXT);
    $stmt->bindValue(7, $id,          SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Hóspede atualizado.');
}

function deleteGuest(): void {
    if (!isAdmin()) respond(403, 'Sem permissão.');
    $id    = (int)($_POST['id'] ?? 0);
    $db    = getDB();
    $check = $db->prepare("SELECT COUNT(*) as n FROM reservations WHERE guest_id=? AND status IN ('pending','confirmed')");
    $check->bindValue(1, $id, SQLITE3_INTEGER);
    if ((int)$check->execute()->fetchArray(SQLITE3_ASSOC)['n'] > 0) respond(409, 'Hóspede tem reservas ativas.');
    $stmt = $db->prepare('DELETE FROM guests WHERE id=?');
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->execute();
    respond(200, 'Hóspede eliminado.');
}

function respond(int $code, string $message, mixed $data = null): never {
    http_response_code($code);
    $payload = ['status' => $code, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}