<?php
declare(strict_types=1);
session_start();
$redirectTo = 'events.php';
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Lista de eventos disponíveis na EventHive.">
  <meta name="keywords" content="Eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura, pesquisa de eventos">
  <meta name="author" content="Pedro Costa">
  <title>Compra Concluída</title>
  
  <meta http-equiv="refresh" content="3;url=<?= htmlspecialchars($redirectTo, ENT_QUOTES) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./Styles/style.css">
</head>

<body class="bg-light">
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h1 class="h4 mb-3">✅ Compra concluída!</h1>
            <p class="mb-2">O seu pagamento foi registado com sucesso.</p>
            <p class="text-muted">Vai ser redirecionado para os eventos em instantes…</p>
            <div class="mt-3">
              <a href="<?= htmlspecialchars($redirectTo, ENT_QUOTES) ?>" class="btn btn-primary">Ir para Eventos agora</a>
              <a href="user.php" class="btn btn-outline-secondary">Ver as minhas compras</a>
            </div>
            <?php if (!empty($_SESSION['purchase_error'])): ?>
              <div class="alert alert-warning mt-3">
                Informação: <?= htmlspecialchars($_SESSION['purchase_error']) ?>
              </div>
              <?php unset($_SESSION['purchase_error']); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
  <script src="./Script/script.js"></script>
</body>
</html>
