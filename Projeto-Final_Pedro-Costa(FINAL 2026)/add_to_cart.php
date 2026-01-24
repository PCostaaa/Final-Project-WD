<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'error' => 'Método inválido']); exit;
}

$eventId  = isset($_POST['event_id']) && ctype_digit($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$quantity = isset($_POST['quantity']) && ctype_digit($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
if ($eventId <= 0 || $quantity <= 0) {
  echo json_encode(['success' => false, 'error' => 'Dados inválidos']); exit;
}

// obter stock
$stmt = $pdo->prepare('SELECT available_tickets FROM events WHERE id = ?');
$stmt->execute([$eventId]);
$ev = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ev) {
  echo json_encode(['success' => false, 'error' => 'Evento inexistente']); exit;
}
$stock = (int)$ev['available_tickets'];
if ($stock <= 0) {
  echo json_encode(['success' => false, 'error' => 'Esgotado']); exit;
}

// Atualiza sessão sem ultrapassar stock
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$current = (int)($_SESSION['cart'][$eventId] ?? 0);
$newQty  = min($current + $quantity, $stock);
$_SESSION['cart'][$eventId] = $newQty;

$remaining = max($stock - $newQty, 0);
$cart_count = array_sum(array_map('intval', $_SESSION['cart']));
echo json_encode([
  'success'    => true,
  'cart_count' => $cart_count,
  'quantity'   => $newQty,
  'remaining'  => $remaining
]);
