<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.html');
        exit;
    }
}

function login(string $username, string $password): array|false {
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, username, password_hash, name, role FROM users WHERE username = ? LIMIT 1');
    $stmt->bindValue(1, $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user   = $result->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        $now = date('Y-m-d H:i:s');

        $upd = $db->prepare('UPDATE users SET last_access = ? WHERE id = ?');
        $upd->bindValue(1, $now, SQLITE3_TEXT);
        $upd->bindValue(2, $user['id'], SQLITE3_INTEGER);
        $upd->execute();

        $log = $db->prepare('INSERT INTO access_log (user_id, username, accessed_at) VALUES (?, ?, ?)');
        $log->bindValue(1, $user['id'], SQLITE3_INTEGER);
        $log->bindValue(2, $user['username'], SQLITE3_TEXT);
        $log->bindValue(3, $now, SQLITE3_TEXT);
        $log->execute();

        unset($user['password_hash']);
        return $user;
    }

    return false;
}

function logout(): void {
    $_SESSION = [];
    session_destroy();
    header('Location: /login.html');
    exit;
}

function currentUser(): ?array {
    if (!empty($_SESSION['user_id'])) {
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name'     => $_SESSION['user_name'],
            'role'     => $_SESSION['user_role'],
        ];
    }
    return null;
}

function isAdmin(): bool {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

function getAccessLog(int $userId): array {
    $db   = getDB();
    $stmt = $db->prepare('SELECT accessed_at FROM access_log WHERE user_id = ? ORDER BY accessed_at DESC');
    $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $rows   = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}
