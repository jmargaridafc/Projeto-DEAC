<?php
// reservar.php — Recebe o POST do formulário e guarda em SQLite3

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// ── Base de dados SQLite3 ──────────────────────────────────────────────────
define('DB_PATH', __DIR__ . '/db/reservas.db');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // Cria a pasta db/ se não existir
        if (!is_dir(__DIR__ . '/db')) {
            mkdir(__DIR__ . '/db', 0755, true);
        }
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // Cria a tabela se ainda não existir
        $pdo->exec("CREATE TABLE IF NOT EXISTS reservas (
            id           TEXT PRIMARY KEY,
            data_criacao TEXT NOT NULL,
            nome         TEXT NOT NULL,
            apelido      TEXT NOT NULL,
            email        TEXT NOT NULL,
            telefone     TEXT NOT NULL,
            pay_nome     TEXT NOT NULL,
            pay_apelido  TEXT NOT NULL,
            cartao       TEXT NOT NULL,
            validade     TEXT NOT NULL,
            pedidos      TEXT,
            hotel        TEXT,
            quarto       TEXT,
            checkin      TEXT,
            checkout     TEXT,
            total        TEXT
        )");
    }
    return $pdo;
}

function clean($v): string {
    return trim(filter_var($v ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
}

// ── Recolha e limpeza dos dados ────────────────────────────────────────────
$reserva = [
    'id'          => uniqid('res_'),
    'data_criacao'=> date('Y-m-d H:i:s'),
    'nome'        => clean($_POST['name']        ?? ''),
    'apelido'     => clean($_POST['surname']     ?? ''),
    'email'       => clean($_POST['email']       ?? ''),
    'telefone'    => clean($_POST['phone']       ?? ''),
    'pay_nome'    => clean($_POST['pay_name']    ?? ''),
    'pay_apelido' => clean($_POST['pay_surname'] ?? ''),
    // Guarda apenas os últimos 4 dígitos do cartão
    'cartao'      => '**** **** **** ' . substr(preg_replace('/\D/', '', $_POST['card'] ?? ''), -4),
    'validade'    => clean($_POST['expire']      ?? ''),
    'pedidos'     => clean($_POST['requests']    ?? ''),
    // Dados do hotel vindos do formulário (campos hidden preenchidos pelo JS)
    'hotel'       => clean($_POST['hotel']       ?? ''),
    'quarto'      => clean($_POST['quarto']      ?? ''),
    'checkin'     => clean($_POST['checkin']     ?? ''),
    'checkout'    => clean($_POST['checkout']    ?? ''),
    'total'       => clean($_POST['total']       ?? ''),
];

// ── Validação ──────────────────────────────────────────────────────────────
$erros = [];
foreach (['nome', 'apelido', 'email', 'telefone', 'pay_nome', 'pay_apelido', 'cartao', 'validade'] as $campo) {
    if (empty($reserva[$campo])) {
        $erros[] = "Campo obrigatório em falta: $campo";
    }
}
if (!filter_var($reserva['email'], FILTER_VALIDATE_EMAIL)) {
    $erros[] = "Email inválido.";
}
if (!empty($erros)) {
    http_response_code(400);
    echo implode('<br>', $erros);
    exit;
}

// ── Inserção na BD ─────────────────────────────────────────────────────────
try {
    $sql = "INSERT INTO reservas
                (id, data_criacao, nome, apelido, email, telefone,
                 pay_nome, pay_apelido, cartao, validade, pedidos,
                 hotel, quarto, checkin, checkout, total)
            VALUES
                (:id, :data_criacao, :nome, :apelido, :email, :telefone,
                 :pay_nome, :pay_apelido, :cartao, :validade, :pedidos,
                 :hotel, :quarto, :checkin, :checkout, :total)";

    db()->prepare($sql)->execute($reserva);

} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/omeuphp.log',
        "[" . date('c') . "] ERRO BD: " . $e->getMessage() . "\n",
        FILE_APPEND);
    http_response_code(500);
    echo "Erro ao guardar a reserva. Tente novamente.";
    exit;
}

// ── Log e redireccionamento ────────────────────────────────────────────────
file_put_contents(__DIR__ . '/omeuphp.log',
    "[" . date('c') . "] Reserva {$reserva['id']} criada para {$reserva['email']}\n",
    FILE_APPEND);

header('Location: confirmation.html?id=' . urlencode($reserva['id'])
    . '&nome=' . urlencode($reserva['nome'])
    . '&hotel=' . urlencode($reserva['hotel'])
    . '&checkin=' . urlencode($reserva['checkin'])
    . '&checkout=' . urlencode($reserva['checkout'])
    . '&total=' . urlencode($reserva['total']));
exit;