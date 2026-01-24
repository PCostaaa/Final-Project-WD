<?php
session_start();
require_once "dbconnection.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("
  SELECT p.*, e.title 
  FROM purchases p
  JOIN events e ON p.event_id = e.id
  WHERE p.user_id = ?
  ORDER BY p.purchase_date DESC
");
$stmt->execute([$id]);
$purchases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Histórico de Compras EventHive.">
    <meta name="keywords" content="Eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura, pesquisa de eventos">
    <meta name="author" content="Pedro Costa">
    <title>Histórico de Compras</title>

    <link rel="stylesheet" href="./Styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css" integrity="sha512-kJlvECunwXftkPwyvHbclArO8wszgBGisiLeuDFwNM8ws+wKIw0sv1os3ClWZOcrEB2eRXULYUsm8OVRGJKwGA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <div class="history-panel">
        <h2>Histórico de Compras para o User com o ID  <?php echo $id; ?></h2>
        <h2>Histórico do User com o ID: <?php echo $id; ?></h2>
            <table>
              <tr>
                <th>Evento</th>
                <th>Qtde</th>
                <th>Total</th>
                <th>Status</th>
                <th>Data</th>
              </tr>
              <?php foreach ($purchases as $p): ?>
              <tr>
                  <td><?php echo $p['title']; ?></td>
                  <td><?php echo $p['quantity']; ?></td>
                  <td>€<?php echo $p['total_price']; ?></td>
                  <td><?php echo ucfirst($p['status']); ?></td>
                  <td><?php echo $p['purchase_date']; ?></td>
              </tr>
              <?php endforeach; ?>
            </table>
            <a href="admin.php">← Voltar</a>
    </div>
    
    <!-- FOOTER -->
    <footer class="footer">
      <div class="container">
        <p>&copy; 2025 EventHive. Todos os direitos reservados.</p>
      </div>
    </footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script src="./Script/script.js"></script>
</body>
</html>
