<?php
    session_start();
    include 'dbconnection.php';

    // Inicializa o termo de pesquisa
    $searchTerm = $_GET['search'] ?? '';
    $events = [];

    $q    = isset($_GET['q'])    ? trim($_GET['q'])    : '';
    $from = isset($_GET['from']) ? trim($_GET['from']) : '';
    $to   = isset($_GET['to'])   ? trim($_GET['to'])   : '';

    $where = [];
    $params = [];

    if ($q !== '') { $where[] = 'title LIKE ?'; $params[] = '%'.$q.'%'; }
    if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $where[] = 'DATE(event_date) >= ?'; $params[] = $from; }
    if ($to   !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $where[] = 'DATE(event_date) <= ?'; $params[] = $to; }

    $sql = 'SELECT id, title, description, event_date, event_time, location, ticket_price, available_tickets, image_url FROM events';
    if ($where) { $sql .= ' WHERE '.implode(' AND ', $where); }
    $sql .= ' ORDER BY event_date ASC, event_time ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    try {
        // Prepara a consulta SQL
        $sql = "SELECT * FROM events";
        $params = [];

        if (!empty($searchTerm)) {
            $sql .= " WHERE title LIKE ?";
            $params[] = '%' . $searchTerm . '%';
        }

        $sql .= " ORDER BY event_date DESC, event_time DESC"; // Ordena por data e hora

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Database error fetching events: " . $e->getMessage());
    }

    $cartItemCount = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $k => $v) {
            $cartItemCount += is_array($v) ? (int)($v['quantity'] ?? 0) : (int)$v;
        }
    }

    $openModalScript = '';
    if (isset($_GET['open_modal']) && $_GET['open_modal'] === 'true' && isset($_GET['event_id'])) {
        $eventIdToOpen = htmlspecialchars($_GET['event_id']);
        $openModalScript = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const modalButton = document.querySelector('[data-bs-target=\"#eventDetailsModal\"][data-event-id=\"$eventIdToOpen\"]');
                    if (modalButton) {
                        modalButton.click(); // Trigger the click to show the modal
                    } else {
                        console.error('Modal trigger button not found for event ID: $eventIdToOpen');
                    }
                });
            </script>
        ";
    }
?>

<!doctype html>
<html lang="pt-PT">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Lista de eventos disponíveis na EventHive.">
    <meta name="keywords" content="Eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura, pesquisa de eventos">
    <meta name="author" content="Pedro Costa">
    <title>EventHive - Eventos</title>

    <link rel="stylesheet" href="./Styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css" xintegrity="sha512-kJlvECunwXftkPwyvHbclArO8wszgBGisiLeuDFwNM8ws+wKIw0sv1os3ClWZOcrEB2eRXULYUsm8OVRGJKwGA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  </head>
  <body>
    <header class="header" id="header">
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
              <a href="events.php" class="nav__link active">
                <i class="ri-calendar-event-line me-2"></i>Eventos
              </a>
            </li>

            <?php if (isset($_SESSION['username'])): ?>
            <li class="nav__item">
              <a href="<?php echo ($_SESSION['role'] === 'admin') ? 'admin.php' : 'user.php'; ?>" class="nav__link">
                <i class="ri-user-line me-2"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
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

        <div class="nav__actions">
          <a href="view_cart.php" class="nav__cart">
            <i class="ri-shopping-cart-line"></i>
            <span class="cart-badge" id="cart-item-count"><?php echo $cartItemCount; ?></span>
          </a>
          <div class="nav__toggle" id="nav-toggle">
            <i class="ri-menu-line"></i>
          </div>
        </div>
      </nav>
    </header>

    <main class="main container">
      <h1 class="text-center my-4">Eventos Disponíveis</h1>
      
      <div class="search-bar my-4">
        <form action="events.php" method="GET" class="d-flex justify-content-center">
          <input type="text" name="search" placeholder="Pesquisar eventos por título..." 
                 class="form-control me-2" value="<?php echo htmlspecialchars($searchTerm); ?>" style="max-width: 500px;">
          <button type="submit" class="btn btn-primary">Pesquisar</button>
        </form>
      </div>

      <div class="row">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
            <div class="col-md-4 mb-4">
                <div class="card event-card h-100"> <div class="card-body">
                        <?php if (!empty($event['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" class="card-img-top mb-3" alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <?php else: ?>
                            <img src="https://placehold.co/400x250/E0E0E0/333333?text=Sem+Imagem" class="card-img-top mb-3" alt="Sem Imagem">
                        <?php endif; ?>
                        
                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="card-text">
                            <?php 
                                $description = htmlspecialchars($event['description']);
                                echo (strlen($description) > 100) ? substr($description, 0, 97) . '...' : $description;
                            ?>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                Data: <?php echo date('d/m/Y', strtotime($event['event_date'])); ?> 
                                Hora: <?php echo date('H:i', strtotime($event['event_time'])); ?>
                            </small>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                Local: <?php echo htmlspecialchars($event['location']); ?>
                            </small>
                        </p>
                        <p class="card-text">
                            <strong>Preço: €<?php echo htmlspecialchars(number_format($event['ticket_price'], 2, ',', '.')); ?></strong>
                        </p>
                        
                        <?php if (isset($_SESSION['username']) && $_SESSION['role'] === 'user'): // Only show buy option for logged-in users ?>
                        <div class="d-flex align-items-center mt-3">
                            <input type="number" class="form-control form-control-sm me-2 quantity-input" value="1" min="1" max="<?php echo htmlspecialchars($event['available_tickets']); ?>" style="width: 80px;">
                            <button type="button" class="btn btn-success add-to-cart-btn" data-event-id="<?php echo htmlspecialchars($event['id']); ?>">
                                <i class="ri-shopping-cart-line"></i> Adicionar ao Carrinho
                            </button>
                        </div>
                        <?php elseif (!isset($_SESSION['username'])): // Sugere iniciar sessão ?>
                            <p class="text-center mt-3"><a href="login_register.php?form=login">Faça login para comprar bilhetes.</a></p>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-secondary open-details" data-bs-toggle="modal" data-bs-target="#eventDetailsModal" data-event-id="<?= (int)$event['id']?>">Detalhes</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p>Nenhum evento encontrado. <?php echo !empty($searchTerm) ? 'Tente uma pesquisa diferente.' : ''; ?></p>
                <?php if (empty($searchTerm)): ?>
                    <p>Certifique-se de que os eventos estão adicionados à sua base de dados.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
      </div>
    </main>

    <!-- Modal de Detalhes -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailsModalLabel">Detalhes do Evento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body" id="eventDetailsContent">
        <p>A carregar detalhes do evento...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        <!-- Este botão é ativado pelo script.js quando os dados carregarem -->
        <button type="button" id="modalBuyTicketsBtn" class="btn btn-primary d-none">Adicionar ao carrinho</button>
      </div>
    </div>
  </div>
</div>

    <footer class="footer">
      <div class="container">
        <p>&copy; 2025 EventHive. Todos os direitos reservados.</p>
      </div>
    </footer>
    
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080">
       <div id="cartToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="cartToastBody">Item adicionado ao carrinho</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div> 
       </div> 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="./Script/script.js"></script>
    <?php echo $openModalScript; ?>
  </body>
</html>