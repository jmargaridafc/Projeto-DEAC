<?php
session_start();

// 1. Verificar se o utilizador tem sessão iniciada
if (!isset($_SESSION['user_id'])) {
    die("Erro: Acesso não autorizado. Por favor, faça login.");
}

// 2. Verificar se o ID da reserva foi enviado via POST (vindo do form do teu painel)
if (!isset($_POST['id_reserva']) || empty($_POST['id_reserva'])) {
    die("Erro: Falta o ID da reserva (parâmetro obrigatório).");
}

// 3. Capturar o ID de forma segura
$id_reserva = (int)$_POST['id_reserva'];

try {
    // 4. Ligar à base de dados (apontando um nível acima para a raiz)
    $db = new SQLite3(__DIR__ . '/../hotel.db');
    $db->enableExceptions(true);

    // 5. Atualizar o estado da reserva para 'Cancelada'
    $stmt = $db->prepare("UPDATE reservas SET status = 'Cancelada' WHERE id = :id");
    $stmt->bindValue(':id', $id_reserva, SQLITE3_INTEGER);
    $stmt->execute();

    // 6. Redirecionar de volta para o painel de reservas após o sucesso
    header("Location: ../reservations_painel.php?status=cancel_success");
    exit;

} catch (Exception $e) {
    die("Erro ao cancelar a reserva: " . $e->getMessage());
}
?>