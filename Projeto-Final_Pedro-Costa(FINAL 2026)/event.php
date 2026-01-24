<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . 'dbconnection.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  http_response_code(400); echo "Pedido inválido."; exit;
}
$eventId = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT id, title, description, event_date, event_time, location, ticket_price, available_tickets, image_url FROM events WHERE id = ? LIMIT 1');
$stmt->execute([$eventId]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$event) { http_response_code(404); echo "Evento não encontrado."; exit; }

$dt = $event['event_date'] ? date('d/m/Y', strtotime($event['event_date'])) : '—';
$tm = $event['event_time'] ? date('H:i', strtotime($event['event_time'])) : '—';
$price = number_format((float)$event['ticket_price'], 2, ',', '.');
$stock = (int)$event['available_tickets'];
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Evento EventHive">
  <meta name="keywords" content="Gestão de eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura">
  <meta name="author" content="Pedro Costa">
  <title><?= htmlspecialchars($event['title']) ?> — Detalhes</title>
  
  <link rel="stylesheet" href="./Styles/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">EventHive</a>
    <div class="ms-auto">
      <a href="events.php" class="btn btn-sm btn-outline-secondary">Voltar aos eventos</a>
      <a href="view_cart.php" class="btn btn-sm btn-outline-primary">Carrinho</a>
    </div>
  </div>
</nav>

<main class="container">
  <div class="row g-4">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <?php if (!empty($event['image_url'])): ?>
          <img src="<?= htmlspecialchars($event['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($event['title']) ?>">
        <?php endif; ?>
        <div class="card-body">
          <h1 class="h4 mb-3"><?= htmlspecialchars($event['title']) ?></h1>
          <ul class="list-unstyled small mb-3">
            <li><strong>Data:</strong> <?= $dt ?> &nbsp; <strong>Hora:</strong> <?= $tm ?></li>
            <li><strong>Local:</strong> <?= htmlspecialchars($event['location'] ?: '—') ?></li>
            <li><strong>Preço:</strong> € <?= $price ?></li>
            <li><strong>Bilhetes disponíveis:</strong> <?= $stock ?></li>
          </ul>

          <?php if ($stock > 0): ?>
            <form action="add_to_cart.php" method="post" class="d-flex gap-2">
              <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
              <input type="number" name="quantity" class="form-control" min="1" max="<?= $stock ?>" value="1" style="max-width:120px">
              <button type="submit" class="btn btn-primary">Adicionar ao carrinho</button>
            </form>
            <div class="mt-2">
              <a href="checkout_process.php" class="btn btn-success btn-sm">Comprar já</a>
              <a href="view_cart.php" class="btn btn-outline-secondary btn-sm">Ver carrinho</a>
            </div>
          <?php else: ?>
            <button class="btn btn-secondary" disabled>Esgotado</button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5">Descrição</h2>
          <p style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($event['description'] ?: 'Sem descrição.')) ?></p>
        </div>
      </div>
    </div>
  </div>
</main>


<!-- FOOTER -->
    <footer class="footer">
      <div class="container">
        <p>&copy; 2025 EventHive. Todos os direitos reservados.</p>
      </div>
    </footer>
    
<script src="Script/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
