<?php
session_start();
include 'dbconnection.php';

// Redireciona se o utilziador não tiver iniciado sessão
if (!isset($_SESSION['username'])) {
    header('Location: login_register.php?form=login&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Encontra o ID do evento através do URL
$eventId = $_GET['event_id'] ?? null;

if (!$eventId) {
    header('Location: events.php'); // Redireciona se o ID do evento não for fornecido
    exit();
}

try {
    // Procura os detalhes do evento
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo "Evento não encontrado.";
        exit();
    }
} catch (PDOException $e) {
    echo "Erro ao carregar os detalhes do evento.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lista de eventos disponíveis na EventHive.">
    <meta name="keywords" content="Eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura, pesquisa de eventos">
    <meta name="author" content="Pedro Costa">
    <title>Comprar Bilhetes - <?php echo htmlspecialchars($event['title']); ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css" xintegrity="sha512-kJlvECunwXftkPwyvHbclArO8wszgBGisiLeuDFwNM8ws+wKIw0sv1os3ClWZOcrEB2eRXULYUsm8OVRGJKwGA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <nav class="nav container">
            </nav>
    </header>

    <main class="main">
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card p-4">
                        <h2 class="card-title text-center mb-4">Comprar Bilhetes para: <?php echo htmlspecialchars($event['title']); ?></h2>
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($event['title']); ?>" style="max-height: 300px;">
                        </div>
                        <div class="text-muted text-center mb-3">
                            <i class="ri-calendar-line me-1"></i><?php echo date("d/m/Y", strtotime($event['event_date'])) . ' - ' . date("H:i", strtotime($event['event_time'])); ?>
                            <i class="ri-map-pin-line ms-4 me-1"></i><?php echo htmlspecialchars($event['location']); ?>
                        </div>
                        <p class="text-center lead">Preço por bilhete: €<?php echo htmlspecialchars(number_format($event['ticket_price'], 2, ',', '.')); ?></p>
                        <p class="text-center mb-4">Bilhetes Disponíveis: <?php echo htmlspecialchars($event['available_tickets']); ?></p>

                        <form id="purchaseForm" action="checkout_process.php" method="POST">
                            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($event['available_tickets']); ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Finalizar Compra</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>