<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
require_once "dbconnection.php";


$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$totalPurchases = $pdo->query("SELECT COUNT(*) FROM purchases")->fetchColumn();


$events = $pdo->query("SELECT *, image_url FROM events ORDER BY event_date DESC")->fetchAll();


$users = $pdo->query("SELECT id, username, email, role FROM users ORDER BY created_at DESC")->fetchAll();


$purchases = $pdo->query("
    SELECT p.*, u.username, e.title
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN events e ON p.event_id = e.id
    ORDER BY p.purchase_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Painel de administração da EventHive">
  <meta name="keywords" content="Gestão de eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura">
  <meta name="author" content="Pedro Costa">
  <title>Painel Admin - EventHive</title>
  <link rel="stylesheet" href="./Styles/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="header" id="header">
  <nav class="nav container">
    <a href="index.php" class="nav__logo">
      <img src="./Images/logo.png" alt="logo EventHive">
    </a>
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
        <li class="nav__item">
          <a href="admin.php" class="nav__link active">
            <i class="ri-user-line me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
          </a>
        </li>
        <li class="nav__item">
          <a href="logout.php" class="nav__link logout-link">
            <i class="ri-logout-box-line me-2"></i>Logout
          </a>
        </li>
      </ul>
      <div class="nav__close" id="nav-close">
        <i class="ri-close-line"></i>
      </div>
    </div>
    <div class="nav__toggle" id="nav-toggle">
      <i class="ri-menu-line"></i>
    </div>
  </nav>
</header>

<main class="main admin-dashboard">
  <div class="container-fluid">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <div class="welcome-content">
        <h1 class="dashboard-title">
          <i class="ri-dashboard-line me-3"></i>
          Painel de Administração
        </h1>
        <p class="dashboard-subtitle">Bem-vindo de volta, <?= htmlspecialchars($_SESSION['username']); ?>! Gerencie sua plataforma EventHive.</p>
      </div>
      <div class="welcome-decoration">
        <div class="floating-shapes">
          <div class="shape shape-1"></div>
          <div class="shape shape-2"></div>
          <div class="shape shape-3"></div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card users-card">
        <div class="stat-icon">
          <i class="ri-user-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number" data-target="<?= $totalUsers ?>"><?= $totalUsers ?></h3>
          <p class="stat-label">Utilizadores</p>
          <div class="stat-trend">
            <i class="ri-arrow-up-line"></i>
            <span>+12%</span>
          </div>
        </div>
        <div class="stat-bg-pattern"></div>
      </div>

      <div class="stat-card events-card">
        <div class="stat-icon">
          <i class="ri-calendar-event-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number" data-target="<?= $totalEvents ?>"><?= $totalEvents ?></h3>
          <p class="stat-label">Eventos</p>
          <div class="stat-trend">
            <i class="ri-arrow-up-line"></i>
            <span>+8%</span>
          </div>
        </div>
        <div class="stat-bg-pattern"></div>
      </div>

      <div class="stat-card purchases-card">
        <div class="stat-icon">
          <i class="ri-shopping-cart-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number" data-target="<?= $totalPurchases ?>"><?= $totalPurchases ?></h3>
          <p class="stat-label">Compras</p>
          <div class="stat-trend">
            <i class="ri-arrow-up-line"></i>
            <span>+15%</span>
          </div>
        </div>
        <div class="stat-bg-pattern"></div>
      </div>

      <div class="stat-card revenue-card">
        <div class="stat-icon">
          <i class="ri-money-euro-circle-line"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number" data-target="15420">15.4K€</h3>
          <p class="stat-label">Receita Total</p>
          <div class="stat-trend">
            <i class="ri-arrow-up-line"></i>
            <span>+22%</span>
          </div>
        </div>
        <div class="stat-bg-pattern"></div>
      </div>
    </div>

    <!-- Management Sections -->
    <div class="management-grid">
      <!-- Events Management -->
      <div class="management-card events-management">
        <div class="card-header">
          <div class="card-title">
            <i class="ri-calendar-event-line me-2"></i>
            Gestão de Eventos
          </div>
          <button class="btn btn-primary btn-create" onclick="openCreateEventModal()">
            <i class="ri-add-line me-2"></i>
            Criar Evento
          </button>
        </div>
        <div class="card-content">
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Imagem</th>
                  <th>Título</th>
                  <th>Data</th>
                  <th>Local</th>
                  <th>Preço</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice($events, 0, 5) as $e): ?>
                <tr class="table-row">
                  <td>
                    <div class="event-image">
                      <?php if (!empty($e['image_url'])): ?>
                        <img src="<?= htmlspecialchars($e['image_url']); ?>" alt="Evento">
                      <?php else: ?>
                        <div class="no-image">
                          <i class="ri-image-line"></i>
                        </div>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="event-title"><?= htmlspecialchars($e['title']); ?></td>
                  <td>
                    <div class="date-info">
                      <i class="ri-calendar-line"></i>
                      <?= date('d/m/Y', strtotime($e['event_date'])); ?>
                    </div>
                  </td>
                  <td>
                    <div class="location-info">
                      <i class="ri-map-pin-line"></i>
                      <?= htmlspecialchars($e['location']); ?>
                    </div>
                  </td>
                  <td class="price">€<?= number_format($e['ticket_price'], 2, ',', '.'); ?></td>
                  <td>
                    <div class="action-buttons">
                      <button class="btn-action btn-edit" onclick="openEditEventModal(
                        <?= $e['id']; ?>,
                        '<?= addslashes($e['title']); ?>',
                        '<?= addslashes($e['description']); ?>',
                        '<?= $e['event_date']; ?>',
                        '<?= $e['event_time']; ?>',
                        '<?= addslashes($e['location']); ?>',
                        <?= $e['ticket_price']; ?>,
                        <?= $e['available_tickets']; ?>,
                        '<?= addslashes($e['image_url'] ?? ''); ?>'
                      )">
                        <i class="ri-edit-line"></i>
                      </button>
                      <button class="btn-action btn-delete" onclick="openDeleteEventModal(<?= $e['id']; ?>)">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if (count($events) > 5): ?>
          <div class="view-more">
            <button class="btn btn-outline-primary" onclick="showAllEvents()">
              <i class="ri-arrow-down-line me-2"></i>
              Ver Todos os Eventos (<?= count($events) ?>)
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Users Management -->
      <div class="management-card users-management">
        <div class="card-header">
          <div class="card-title">
            <i class="ri-user-line me-2"></i>
            Gestão de Utilizadores
          </div>
        </div>
        <div class="card-content">
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Avatar</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Função</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice($users, 0, 5) as $u): ?>
                <tr class="table-row">
                  <td>
                    <div class="user-avatar">
                      <div class="avatar-circle">
                        <?= strtoupper(substr($u['username'], 0, 1)); ?>
                      </div>
                    </div>
                  </td>
                  <td class="username"><?= htmlspecialchars($u['username']); ?></td>
                  <td class="email">
                    <div class="email-info">
                      <i class="ri-mail-line"></i>
                      <?= htmlspecialchars($u['email']); ?>
                    </div>
                  </td>
                  <td>
                    <span class="role-badge role-<?= $u['role']; ?>">
                      <i class="ri-<?= $u['role'] === 'admin' ? 'shield-user' : 'user'; ?>-line"></i>
                      <?= ucfirst($u['role']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button class="btn-action btn-edit" onclick="openEditModal(<?= $u['id']; ?>,'<?= addslashes($u['username']); ?>','<?= addslashes($u['email']); ?>','<?= $u['role']; ?>')">
                        <i class="ri-edit-line"></i>
                      </button>
                      <button class="btn-action btn-delete" onclick="openDeleteModal(<?= $u['id']; ?>)">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Purchases Management -->
      <div class="management-card purchases-management">
        <div class="card-header">
          <div class="card-title">
            <i class="ri-shopping-cart-line me-2"></i>
            Gestão de Compras
          </div>
        </div>
        <div class="card-content">
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Utilizador</th>
                  <th>Evento</th>
                  <th>Quantidade</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Data</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice($purchases, 0, 5) as $p): ?>
                <tr class="table-row" data-purchase-id="<?= $p['id']; ?>">
                  <td>
                    <div class="user-info">
                      <div class="user-avatar-sm">
                        <div class="avatar-circle-sm">
                          <?= strtoupper(substr($p['username'], 0, 1)); ?>
                        </div>
                      </div>
                      <span class="user-name"><?= htmlspecialchars($p['username']); ?></span>
                    </div>
                  </td>
                  <td class="event-name">
                    <div class="event-info">
                      <i class="ri-calendar-event-line"></i>
                      <?= htmlspecialchars($p['title']); ?>
                    </div>
                  </td>
                  <td class="quantity">
                    <span class="quantity-badge purchase-quantity">
                      <i class="ri-ticket-line"></i>
                      <?= $p['quantity']; ?>
                    </span>
                  </td>
                  <td class="total-price">
                    <span class="price-amount">€<?= number_format($p['total_price'], 2, ',', '.'); ?></span>
                  </td>
                  <td>
                    <span class="status-badge status-<?= $p['status']; ?> purchase-status">
                      <i class="ri-<?= $p['status'] === 'completed' ? 'check' : ($p['status'] === 'pending' ? 'time' : 'close'); ?>-line"></i>
                      <?= ucfirst($p['status']); ?>
                    </span>
                  </td>
                  <td class="purchase-date">
                    <div class="date-info">
                      <i class="ri-calendar-line"></i>
                      <?= date('d/m/Y', strtotime($p['purchase_date'])); ?>
                      <small class="time-info"><?= date('H:i', strtotime($p['purchase_date'])); ?></small>
                    </div>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button class="btn-action btn-edit" onclick="openEditPurchaseModal(<?= $p['id']; ?>, <?= $p['user_id']; ?>, <?= $p['event_id']; ?>, <?= $p['quantity']; ?>, '<?= $p['status']; ?>')">
                        <i class="ri-edit-line"></i>
                      </button>
                      <button class="btn-action btn-delete" onclick="openDeletePurchaseModal(<?= $p['id']; ?>)">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modals -->
<?php include "admin_modals.php"; ?>

<footer class="footer admin-footer">
  <div class="container">
    <div class="footer-content">
      <p>&copy; 2025 EventHive. Todos os direitos reservados.</p>
      <div class="footer-links">
        <a href="index.php">Home</a>
        <a href="events.php">Eventos</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="./Script/script.js"></script>
<script>
// Number counter animation
function animateNumbers() {
  const counters = document.querySelectorAll('.stat-number[data-target]');

  counters.forEach(counter => {
    const target = parseInt(counter.getAttribute('data-target'));
    const text = counter.textContent;
    const isCurrency = text.includes('€');
    const isK = text.includes('K');

    let startValue = 0;
    const duration = 2000;
    const increment = target / (duration / 16);

    const timer = setInterval(() => {
      startValue += increment;
      if (startValue >= target) {
        startValue = target;
        clearInterval(timer);
      }

      if (isCurrency && isK) {
        counter.textContent = (startValue / 1000).toFixed(1) + 'K€';
      } else if (isCurrency) {
        counter.textContent = startValue.toFixed(0) + '€';
      } else {
        counter.textContent = Math.floor(startValue);
      }
    }, 16);
  });
}

// Trigger animations on page load
document.addEventListener('DOMContentLoaded', () => {
  animateNumbers();

  // Add loading states to buttons
  document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('click', function() {
      this.innerHTML = '<i class="ri-loader-4-line rotating"></i>';
      this.disabled = true;
    });
  });
});

// Function to show all events (placeholder)
function showAllEvents() {
  // This would typically show a modal or redirect to a full events page
  alert('Funcionalidade completa em desenvolvimento!');
}
</script>
</body>
</html>
