<?php
session_start();
require_once "dbconnection.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = $_POST['id'] ?? null;
$message = ""; // Variável para armazenar a mensagem para exibir

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute(params: [$id]);
        $message = "O utilizador foi removido com sucesso!";
    } catch (PDOException $e) {
        $message = "Erro ao remover o utilizador: " . $e->getMessage();
    }
} else {
    $message = "ID do utilizador não fornecido para exclusão.";
}

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Painel para administração">
    <meta name="keywords" content="Gestão de eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura">
    <meta name="author" content="Pedro Costa">        
    <title>Eliminar Utilizador</title>

    <link rel="stylesheet" href="./Styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css" integrity="sha512-kJlvECunwXftkPwyvHbclArO8wszgBGisiLeuDFwNM8ws+wKIw0sv1os3ClWZOcrEB2eRXULYUsm8OVRGJKwGA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <div class="form-panel">
        <?php if ($message): ?>
            <h2>Status da Exclusão</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php else: ?>
            <h2>Aguardando Exclusão</h2>
            <p>Por favor, forneça um ID de utilizador para eliminar.</p>
        <?php endif; ?>
        <a href="admin.php" class="back-link">Concluído</a>
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
