<?php
    session_start();
    include 'dbconnection.php';

    $featuredEvents = [];
    try {
        //  Mostra alguns eventos
        $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC, event_time DESC LIMIT 3");
        $featuredEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error fetching featured events for index.php: " . $e->getMessage());
    }

    $openModalScript = '';
    if (isset($_GET['open_modal']) && $_GET['open_modal'] === 'true' && isset($_GET['event_id'])) {
      $openModalScript = "
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementByID('eventDetailsModal'));
            const modalButton = document.querySelector('[data-bs-target=\"#eventDetailsModal\"][data-event-id=\"$eventIdOpen\"]');
            if (modalButton) {
              modalButton.click();
              }
          });
        </script>
      ";
    }
?>

<!doctype html>
<html lang="pt-pt">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Página principal da EventHive">
    <meta name="keywords" content="Gestão de eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura">
    <meta name="author" content="Pedro Costa">
    <title>EventHive</title>

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
              <a href="index.php" class="nav__link active">
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
            <span class="cart-badge" id="cart-item-count">0</span>
          </a>
          <div class="nav__toggle" id="nav-toggle">
            <i class="ri-menu-line"></i>
          </div>
        </div>
      </nav>
    </header>


    <section class="intro">
      <div class="container">
        <h1 class="intro__title">Descobre experiências únicas</h1>
        <p class="intro__subtitle">Eventos selecionados com curadoria para apaixonados por cultura, música e arte.</p>
        <div class="intro__tagline">
          <span class="tagline__text">“A arte une, a música inspira, os momentos ficam.”</span>
        </div>
      </div>
    </section>

    <main class="main" id="main">
      <div class="container mt-3" id="cards_destaques">      
        <div class="row">
          <?php if (!empty($featuredEvents)): ?>
            <?php foreach ($featuredEvents as $event): ?>
            <div class="col-md-4 mb-4">
                <div class="card event-card h-100">
                    <div class="card-body">
                        <?php if (!empty($event['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" class="card-img-top mb-3 rounded" alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <?php else: ?>
                            <img src="https://placehold.co/400x250/E0E0E0/333333?text=Sem+Imagem" class="card-img-top mb-3 rounded" alt="Sem Imagem">
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
                        <?php if (!isset($_SESSION['username'])): // Sugere iniciar sessão se não o tiver feito ?>
                            <p class="text-center mt-3"><a href="login_register.php?form=login">Faça login para comprar bilhetes.</a></p>
                        <?php endif; ?>
                        <button type="button" class="card-link btn btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#eventDetailsModal" data-event-id="<?php echo htmlspecialchars($event['id']); ?>">Detalhes</button>
                        <a href="events.php?open_modal=true&event_id=<?php echo htmlspecialchars($event['id']); ?>" class="btn btn-outline-primary mt-2">Comprar Bilhetes</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 text-center">
                <p>Nenhum evento em destaque encontrado.</p>
                <p>Certifique-se de que os eventos estão adicionados à sua base de dados.</p>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
          <a href="events.php"><button type="button" class="btn btn-success">Todos os Eventos</button></a>
        </div>
      </div>
    </main>

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
        
        <button type="button" id="modalBuyTicketsBtn" class="btn btn-primary d-none">Comprar</button>
      </div>
    </div>
  </div>
</div>

    <footer class="footer">
      <div class="container">
        <p>&copy; 2025 EventHive. Todos os direitos reservados.</p>
      </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script src="./Script/script.js"></script>
</body>
</html>