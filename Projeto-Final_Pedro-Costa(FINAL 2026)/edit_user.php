<?php
session_start();
require_once "dbconnection.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) exit("Invalid user ID.");

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newUsername = trim($_POST['username']);
    $newEmail = trim($_POST['email']);
    $newRole = $_POST['role'];

    $update = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $update->execute([$newUsername, $newEmail, $newRole, $id]);
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Painel de edição de utilizadores">
    <meta name="keywords" content="Gestão de eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura">
    <meta name="author" content="Pedro Costa">

    <link rel="stylesheet" href="./Styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css" integrity="sha512-kJlvECunwXftkPwyvHbclArO8wszgBGisiLeuDFwNM8ws+wKIw0sv1os3ClWZOcrEB2eRXULYUsm8OVRGJKwGA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <div class="form-panel">
        <h2>Edit User</h2>
        <form method="POST">
            <label>Username: <input type="text" name="username" value="<?php echo $user['username']; ?>"></label><br>
            <label>Email: <input type="email" name="email" value="<?php echo $user['email']; ?>"></label><br>
            <label>Role: 
                <select name="role">
                <option value="user" <?php if($user['role']=='user') echo 'selected'; ?>>User</option>
                <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                </select>
            </label><br>
            <button type="submit">Update</button>
        </form>
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
