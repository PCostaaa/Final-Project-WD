<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/dbconnection.php';

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;
if ($id === null) {
  echo json_encode(['success' => false, 'error' => 'ID inválido']);
  exit;
}

try {
  $stmt = $pdo->prepare(
    'SELECT id, title, description, event_date, event_time, location, ticket_price, available_tickets, image_url
     FROM events
     WHERE id = ?
     LIMIT 1'
  );
  $stmt->execute([$id]);
  $event = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$event) {
    echo json_encode(['success' => false, 'error' => 'Evento não encontrado']);
    exit;
  }

  echo json_encode(['success' => true] + $event);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'error' => 'Erro no servidor']);
}
