<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

require_once "dbconnection.php";

// Encontra o utilizador através do username
$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    // Se o utilizador não é encontrado, é efetuado o logout
    header("Location: logout.php");
    exit();
}

$user_id = $user['id'];

// Trata de atualizar o perfil
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);

    // Validate input
    if (empty($new_username) || empty($new_email)) {
        $error = "Nome de utilizador e email são obrigatórios.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email inválido.";
    } elseif (strlen($new_username) < 3) {
        $error = "Nome de utilizador deve ter pelo menos 3 caracteres.";
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $updateStmt->execute([$new_username, $new_email, $user_id]);

            if ($updateStmt->rowCount() > 0) {
                // Atualiza o nome da sessão
                $_SESSION['username'] = $new_username;

                // Check if this is an AJAX request
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    // Return JSON response for AJAX
                    echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso!']);
                    exit();
                } else {
                    // Redirect for normal form submission
                    header("Location: user.php?success=1");
                    exit();
                }
            } else {
                $error = "Nenhuma alteração foi feita.";
            }
        } catch (PDOException $e) {
            error_log("Database error in user.php: " . $e->getMessage());
            $error = "Erro na base de dados. Tente novamente.";
        }
    }

    // Handle AJAX error response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'error' => $error]);
        exit();
    }
}

// Check for success message from URL parameter
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Perfil atualizado com sucesso!';
}

// Procura o histórico de compras
$pstmt = $pdo->prepare("
    SELECT p.id, e.title, e.event_date, e.event_time, p.quantity, p.total_price, p.status, p.purchase_date
    FROM purchases p
    JOIN events e ON p.event_id = e.id
    WHERE p.user_id = ?
    ORDER BY p.purchase_date DESC
");
$pstmt->execute([$user_id]);
$purchase_results = $pstmt->fetchAll();

// Calculate user statistics
$totalPurchases = count($purchase_results);
$totalTickets = array_sum(array_column($purchase_results, 'quantity'));
$totalSpent = array_sum(array_column($purchase_results, 'total_price'));
$completedPurchases = count(array_filter($purchase_results, function($p) { return $p['status'] === 'completed'; }));
?>


<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Painel do utilizador da EventHive">
  <meta name="keywords" content="Perfil, histórico de compras, bilhetes, eventos, cultura">
  <meta name="author" content="Pedro Costa">
  <title>Painel do Utilizador - EventHive</title>

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
          <a href="index.php" class="nav__link">Home</a>
        </li>
        <li class="nav__item">
          <a href="events.php" class="nav__link">Eventos</a>
        </li>
        <li class="nav__item">
          <a href="user.php" class="nav__link active">Perfil</a>
        </li>
        <li class="nav__item">
          <a href="logout.php" class="nav__link">Logout</a>
        </li>
      </ul>
    </div>
    <div class="nav__actions">
      <a href="view_cart.php" class="nav__cart">
        <i class="ri-shopping-cart-line"></i>
        <span class="cart-badge" id="cart-item-count">0</span>
      </a>
      <div class="nav__toggle" id="nav-toggle">
        <i class="ri-menu-line"></i>
      </div>
    </div>
  </nav>
</header>

<main class="user-dashboard">
  <!-- Welcome Section -->
  <section class="welcome-section">
    <div class="welcome-content">
      <h1 class="dashboard-title">
        <i class="ri-user-smile-line"></i>
        Bem-vindo de volta, <?php echo htmlspecialchars($_SESSION['username']); ?>!
      </h1>
      <p class="dashboard-subtitle">
        Faça a gestão do seu perfil e acompanhe o seu histórico de compras na EventHive.
      </p>
    </div>
    <div class="welcome-decoration">
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
      </div>
    </div>
  </section>

  <?php if (!empty($success_message)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-check-circle-line me-2"></i>
    <?php echo htmlspecialchars($success_message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <!-- Statistics Cards -->
  <section class="stats-grid">
    <div class="stat-card purchases-card">
      <div class="stat-bg-pattern"></div>
      <div class="stat-icon">
        <i class="ri-shopping-bag-line"></i>
      </div>
      <div class="stat-content">
        <div class="stat-number"><?php echo $totalPurchases; ?></div>
        <div class="stat-label">Compras Totais</div>
        <div class="stat-trend">
          <i class="ri-arrow-up-line"></i>
          <?php echo $completedPurchases; ?> Concluídas
        </div>
      </div>
    </div>

    <div class="stat-card tickets-card">
      <div class="stat-bg-pattern"></div>
      <div class="stat-icon">
        <i class="ri-ticket-line"></i>
      </div>
      <div class="stat-content">
        <div class="stat-number"><?php echo $totalTickets; ?></div>
        <div class="stat-label">Bilhetes Comprados</div>
        <div class="stat-trend">
          <i class="ri-star-line"></i>
          Eventos únicos
        </div>
      </div>
    </div>

    <div class="stat-card spending-card">
      <div class="stat-bg-pattern"></div>
      <div class="stat-icon">
        <i class="ri-money-euro-circle-line"></i>
      </div>
      <div class="stat-content">
        <div class="stat-number">€<?php echo number_format($totalSpent, 2); ?></div>
        <div class="stat-label">Total Gasto</div>
        <div class="stat-trend">
          <i class="ri-trending-up-line"></i>
          Em eventos
        </div>
      </div>
    </div>

    <div class="stat-card events-card">
      <div class="stat-bg-pattern"></div>
      <div class="stat-icon">
        <i class="ri-calendar-event-line"></i>
      </div>
      <div class="stat-content">
        <div class="stat-number"><?php echo count(array_unique(array_column($purchase_results, 'title'))); ?></div>
        <div class="stat-label">Eventos Participados</div>
        <div class="stat-trend">
          <i class="ri-heart-line"></i>
          Experiências únicas
        </div>
      </div>
    </div>
  </section>

  <!-- Management Cards -->
  <section class="management-grid">
    <!-- Profile Management Card -->
    <div class="management-card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="ri-user-settings-line"></i>
          Gestão de  Perfil
        </h3>
      </div>
      <div class="card-content">
        <form method="POST" class="profile-form-modern">
          <div class="form-row">
            <div class="form-group-modern">
              <label for="username" class="form-label">
                <i class="ri-user-line"></i>
                Nome de Utilizador
              </label>
              <input type="text" id="username" name="username"
                     value="<?php echo htmlspecialchars($user['username']); ?>"
                     class="form-input" required>
            </div>
            <div class="form-group-modern">
              <label for="email" class="form-label">
                <i class="ri-mail-line"></i>
                Email
              </label>
              <input type="email" id="email" name="email"
                     value="<?php echo htmlspecialchars($user['email']); ?>"
                     class="form-input" required>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" name="update_profile" class="btn-update-profile">
              <i class="ri-save-line"></i>
              Atualizar Perfil
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Purchase History Card -->
    <div class="management-card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="ri-history-line"></i>
          Histórico de Compras
        </h3>
        <div class="table-controls">
          <div class="control-group">
            <label for="tableFilter" class="control-label">
              <i class="ri-search-line"></i>
              Procurar
            </label>
            <input type="text" id="tableFilter" placeholder="Buscar compras..." class="table-search">
          </div>

          <div class="control-group">
            <label class="control-label">
              <i class="ri-calendar-line"></i>
              Período
            </label>
            <div class="date-range">
              <input type="date" id="startDate" placeholder="De">
              <input type="date" id="endDate" placeholder="Até">
            </div>
          </div>

          <div class="control-group">
            <label for="sortSelector" class="control-label">
              <i class="ri-sort-line"></i>
              Ordenar
            </label>
            <select id="sortSelector" class="sort-select">
              <option value="none">Selecionar...</option>
              <option value="date_event">Data do Evento</option>
              <option value="date_purchase">Data de Compra</option>
              <option value="price">Preço</option>
              <option value="quantity">Quantidade</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-content">
        <?php if (empty($purchase_results)): ?>
          <div class="empty-state">
            <i class="ri-shopping-bag-line"></i>
            <h4>Nenhuma compra encontrada.</h4>
            <p>Você ainda não fez nenhuma compra. Explore os nossos eventos!</p>
            <a href="events.php" class="btn-explore">
              Explorar Eventos
            </a>
          </div>
        <?php else: ?>
          <div class="table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Evento</th>
                  <th>Data & Hora</th>
                  <th>Quantidade</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Data da Compra</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($purchase_results as $row): ?>
                <tr class="table-row">
                  <td>
                    <div class="event-info">
                      <i class="ri-calendar-event-line"></i>
                      <span class="event-title"><?php echo htmlspecialchars($row['title']); ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="date-info">
                      <i class="ri-calendar-line"></i>
                      <?php echo date('d/m/Y', strtotime($row['event_date'])); ?>
                      <div class="time-info"><?php echo date('H:i', strtotime($row['event_time'])); ?></div>
                    </div>
                  </td>
                  <td>
                    <span class="quantity-badge">
                      <i class="ri-ticket-line"></i>
                      <?php echo $row['quantity']; ?>
                    </span>
                  </td>
                  <td>
                    <span class="price">€<?php echo number_format($row['total_price'], 2); ?></span>
                  </td>
                  <td>
                    <span class="status-badge status-<?php echo $row['status']; ?>">
                      <?php echo ucfirst($row['status']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="purchase-date">
                      <?php echo date('d/m/Y', strtotime($row['purchase_date'])); ?>
                      <div class="time-info"><?php echo date('H:i', strtotime($row['purchase_date'])); ?></div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="admin-footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-info">
          <p>&copy; 2025 EventHive. Todos os direitos reservados.</p>
        </div>
        <div class="footer-links">
          <a href="index.php">Home</a>
          <a href="events.php">Eventos</a>
          <a href="user.php">Perfil</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="./Script/script.js"></script>
<script>
// Initialize cart badge
document.addEventListener('DOMContentLoaded', function() {
  updateCartBadge();
});
</script>
</body>
</html>
