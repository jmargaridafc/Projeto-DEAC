<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?next=details.html');
    exit;
}

try {
    $db = new SQLite3(__DIR__ . '/hotel.db');
    $db->enableExceptions(true);
    $db->exec('PRAGMA foreign_keys = ON;');
} catch (Exception $e) {
    die('Error connecting to the database.');
}

function clean($v): string {
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$guestName = $_SESSION['username'];
$room      = clean($_POST['hotel']    ?? '');
$checkIn   = clean($_POST['checkin']  ?? '');
$checkOut  = clean($_POST['checkout'] ?? '');
$totalRaw  = $_POST['total'] ?? '0';
$price     = is_numeric($totalRaw) ? (float)$totalRaw : 0.0;

$name    = clean($_POST['name']    ?? '');
$surname = clean($_POST['surname'] ?? '');
$email   = clean($_POST['email']   ?? '');
$phone   = clean($_POST['phone']   ?? '');

$errors = [];

if ($name === '')    $errors[] = 'Name is required.';
if ($surname === '') $errors[] = 'Surname is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if ($phone === '') $errors[] = 'Phone number is required.';
if ($room === '')  $errors[] = 'Hotel could not be identified.';
if ($checkIn === '' || $checkOut === '') $errors[] = 'Missing dates.';

if ($checkIn !== '' && $checkOut !== '') {
    if (strtotime($checkOut) <= strtotime($checkIn)) {
        $errors[] = 'Checkout date must be after check-in.';
    }
    if (strtotime($checkIn) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Check-in date cannot be in the past.';
    }
}

$expire = clean($_POST['expire'] ?? '');
if (preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expire, $m)) {
    $expireTimestamp = mktime(0, 0, 0, (int)$m[1] + 1, 0, 2000 + (int)$m[2]);
    if ($checkOut !== '' && $expireTimestamp < strtotime($checkOut)) {
        $errors[] = 'The card expires before the checkout date.';
    }
} else {
    $errors[] = 'Invalid card expiry date.';
}

if (!empty($errors)) {
    $hotelId = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
    $msg = urlencode(implode(' | ', $errors));
    header('Location: details.html?id=' . $hotelId . '&server_error=' . $msg);
    exit;
}

$checkStmt = $db->prepare(
    "SELECT COUNT(*) as taken FROM reservas
     WHERE quarto = :room
     AND status != 'Cancelada'
     AND check_in  < :checkout
     AND check_out > :checkin"
);
$checkStmt->bindValue(':room',     $room,     SQLITE3_TEXT);
$checkStmt->bindValue(':checkin',  $checkIn,  SQLITE3_TEXT);
$checkStmt->bindValue(':checkout', $checkOut, SQLITE3_TEXT);
$checkResult = $checkStmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($checkResult['taken'] > 0) {
    $hotelId = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : '';
    $msg = urlencode('This hotel is no longer available for the selected dates.');
    header('Location: details.html?id=' . $hotelId . '&server_error=' . $msg);
    exit;
}

try {
    $stmt = $db->prepare(
        "INSERT INTO reservas (nome_hospede, quarto, check_in, check_out, status, preco)
         VALUES (:guest, :room, :in, :out, 'Confirmada', :price)"
    );
    $stmt->bindValue(':guest', $guestName, SQLITE3_TEXT);
    $stmt->bindValue(':room',  $room,      SQLITE3_TEXT);
    $stmt->bindValue(':in',    $checkIn,   SQLITE3_TEXT);
    $stmt->bindValue(':out',   $checkOut,  SQLITE3_TEXT);
    $stmt->bindValue(':price', $price,     SQLITE3_FLOAT);
    $stmt->execute();

    $reservationId = $db->lastInsertRowID();

} catch (Exception $e) {
    die('Error saving the reservation. Please try again.');
}

header('Location: confirmation.html'
    . '?id='       . urlencode($reservationId)
    . '&nome='     . urlencode($name . ' ' . $surname)
    . '&hotel='    . urlencode($room)
    . '&checkin='  . urlencode($checkIn)
    . '&checkout=' . urlencode($checkOut)
    . '&total='    . urlencode(number_format($price, 2))
);
exit;
