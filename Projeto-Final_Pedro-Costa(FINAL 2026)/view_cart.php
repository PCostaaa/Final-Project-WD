<?php
session_start();
require_once __DIR__ . '/dbconnection.php';

$cart = $_SESSION['cart'] ?? [];        // formato: [event_id => quantity]
$totalPrice = 0.0;
$eventsById = [];
$adjusted = false;
$totalItems = 0;

if (!empty($cart)) {
    $ids = array_keys($cart);
    $ids = array_values(array_filter($ids, fn($v) => ctype_digit((string)$v)));
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, title, ticket_price, available_tickets FROM events WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $eventsById[(int)$row['id']] = $row;
        }
    }

    // Ajustar carrinho ao stock atual
    foreach ($cart as $eventId => $qty) {
        $eventId = (int)$eventId;
        $qty = (int)$qty;
        $ev = $eventsById[$eventId] ?? null;
        if (!$ev) { // evento removido → retirar do carrinho
            unset($cart[$eventId]);
            $adjusted = true;
            continue;
        }
        $stock = (int)$ev['available_tickets'];
        if ($stock <= 0) {
            unset($cart[$eventId]); // esgotado → retirar
            $adjusted = true;
            continue;
        }
        if ($qty > $stock) {
            $cart[$eventId] = $stock; // reduzir à disponibilidade
            $adjusted = true;
        }
        $totalItems += $qty;
        $totalPrice += $qty * (float)$ev['ticket_price'];
    }
    if ($adjusted) {
        $_SESSION['cart'] = $cart; // persistir ajustes
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Carrinho de compras da EventHive">
  <meta name="keywords" content="Gestão de eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura">
  <meta name="author" content="Pedro Costa">
  <title>Carrinho de Compras - EventHive</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css">
  <link rel="stylesheet" href="./Styles/style.css">
</head>
<body>
<header class="header">
  <nav class="nav container">
    <a href="index.php" class="nav__logo"><img src="./Images/logo.png" alt="logo EventHive"></a>
    <div class="nav__menu" id="nav-menu">
      <ul class="nav__list">
        <li class="nav__item">
          <a href="index.php" class="nav__link">
            <i class="ri-home-line me-2"></i>Home
          </a>
        </li>
        <li class="nav__item">
          <a href="events.php" class="nav__link">
            <i class="ri-calendar-event-line me-2"></i>Eventos
          </a>
        </li>
        <?php if (isset($_SESSION['username'])): ?>
        <li class="nav__item">
          <a href="user.php" class="nav__link">
            <i class="ri-user-line me-2"></i>Perfil
          </a>
        </li>
        <li class="nav__item">
          <a href="logout.php" class="nav__link logout-link">
            <i class="ri-logout-box-line me-2"></i>Logout
          </a>
        </li>
        <?php else: ?>
        <li class="nav__item">
          <a href="login_register.php" class="nav__link">
            <i class="ri-login-box-line me-2"></i>Login
          </a>
        </li>
        <?php endif; ?>
      </ul>
      <div class="nav__close" id="nav-close">
        <i class="ri-close-line"></i>
      </div>
    </div>
    <div class="nav__toggle" id="nav-toggle">
      <i class="ri-menu-line"></i>
    </div>
    <div class="nav__actions">
      <a href="view_cart.php" class="nav__cart">
        <i class="ri-shopping-cart-line"></i>
        <span class="cart-badge" id="cart-item-count"><?= $totalItems ?></span>
      </a>
    </div>
  </nav>
</header>

<main class="main cart-dashboard">
  <div class="container-fluid">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <div class="welcome-content">
        <h1 class="dashboard-title">
          <i class="ri-shopping-cart-line me-3"></i>
          O seu Carrinho de Compras
        </h1>
        <p class="dashboard-subtitle">Reveja os seus bilhetes selecionados e finalize a sua compra.</p>
      </div>
      <div class="welcome-decoration">
        <div class="floating-shapes">
          <div class="shape shape-1"></div>
          <div class="shape shape-2"></div>
          <div class="shape shape-3"></div>
        </div>
      </div>
    </div>

    <?php if (!empty($cart)): ?>
    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card cart-items-card">
        <div class="stat-icon">
          <i class="ri-shopping-bag-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number" data-target="<?= $totalItems ?>"><?= $totalItems ?></h3>
          <p class="stat-label">Itens no Carrinho</p>
        </div>
        <div class="stat-bg-pattern"></div>
      </div>

      <div class="stat-card cart-total-card">
        <div class="stat-icon">
          <i class="ri-money-euro-circle-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number" data-target="<?= number_format($totalPrice, 0, ',', '') ?>"><?= number_format($totalPrice, 0, ',', '') ?>€</h3>
          <p class="stat-label">Total a Pagar</p>
        </div>
        <div class="stat-bg-pattern"></div>
      </div>
    </div>

    <!-- Cart Management -->
    <div class="management-card cart-management">
      <div class="card-header">
        <div class="card-title">
          <i class="ri-shopping-cart-line me-2"></i>
          Itens do Carrinho
        </div>
      </div>
      <div class="card-content">
        <div class="table-responsive">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Evento</th>
                <th>Preço Unitário</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cart as $eventId => $quantity): ?>
                <?php
                $eventId = (int)$eventId;
                $quantity = (int)$quantity;
                $event = $eventsById[$eventId] ?? null;

                $title = $event ? $event['title'] : 'Evento indisponível';
                $price = $event ? (float)$event['ticket_price'] : 0.0;
                $subtotal = $price * $quantity;
                ?>
                <tr class="table-row">
                  <td class="event-title"><?= htmlspecialchars($title) ?></td>
                  <td>€ <?= number_format($price, 2, ',', '.') ?></td>
                  <td><?= $quantity ?></td>
                  <td>€ <?= number_format($subtotal, 2, ',', '.') ?></td>
                  <td>
                    <form action="remove_from_cart.php" method="post" class="d-inline">
                      <input type="hidden" name="id" value="<?= (int)$eventId ?>">
                      <input type="hidden" name="return" value="view_cart.php">
                      <button type="submit" class="action-button" onclick="return confirm('Remover este item?');" title="Remover">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                <td><strong>€ <?= number_format($totalPrice, 2, ',', '.') ?></strong></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-center align-items-center gap-3 mt-4">
      <a href="events.php" class="btn btn-secondary">Continuar a Comprar</a>
      <form id="checkoutForm" action="checkout_process.php" method="post" class="d-inline">
        <button type="submit" id="finalizeBtn" class="btn btn-primary btn-finalize">Finalizar Compra</button>
      </form>
    </div>

    <?php else: ?>
    <div class="alert alert-info text-center">
      <i class="ri-shopping-cart-line me-2"></i>O seu carrinho está vazio.
    </div>
    <div class="text-center">
      <a href="events.php" class="btn btn-primary">Voltar para Eventos</a>
    </div>
    <?php endif; ?>
  </div>
</main>

<footer class="footer">
  <div class="container">
    <p>&copy; 2025 EventHive. Todos os direitos reservados.</p>
  </div>
</footer>

<script>
  document.getElementById('finalizeBtn')?.addEventListener('click', function () {
    document.getElementById('checkoutForm')?.submit();
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="./Script/script.js"></script>
</body>
</html>
