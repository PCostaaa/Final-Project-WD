<?php 
include 'dbconnection.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);


$errorMessage = '';

//  Determina que formulário irá mostrar
$formType = $_GET['form'] ?? 'register';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determina que formulário foi "aplicado"
    $formType = $_POST['form_type'] ?? 'register';

    if ($formType === 'register') {
        // Verifica campos obrigatórios
        if (
            !isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])
        ) {
            $errorMessage = 'Todos os campos são obrigatórios.';
        } else {
            // Recebe e limpa os dados
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);
            $userType = $_POST['role'];

            // Validação
            if (strlen($username) < 5) {
                $errorMessage = 'O nome de utilizador deve ter pelo menos 5 caracteres.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'Por favor, insira um endereço de email válido.';
            } elseif (strlen($password) < 8) {
                $errorMessage = 'A palavra-passe tem que ter pelo menos 8 caracteres.';
            } elseif ($password !== $confirmPassword) {
                $errorMessage = 'As palavras-passe não coincidem.';
            } else {
                // Verifica se o nome de utilizador ou email já existem
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
                $stmt->execute([$username, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $errorMessage = 'O nome de utilizador ou email já existe.';
                } else {
                    // Hash da palavra-passe
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    // Insere o utilizador na base de dados
                    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
                    $success = $stmt->execute([$username, $email, $hashedPassword, $userType]);

                    if ($success) {
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $userType;
                        //  Apanha o ID do utilizador
                        $userId = $pdo->lastInsertId();
                        $_SESSION['user_id'] = $userId; //  Guarda o ID do utilizador na sessão
                        header('Location: user.php'); // Redireciona em caso de registo com sucesso
                        exit;
                    } else {
                        $errorMessage = 'Erro ao registar. Por favor, tente novamente.';
                    }
                }
            }
        }
    }
    // Verifica se o formulário é de login
    elseif ($formType === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validação LOGIN
        if (empty($username) || empty($password)) {
            $errorMessage = 'Por favor, insira as credenciais.';
        } else {
            // Verifica se o utilizador existe
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['id']; //  Guarda o ID do utilizador na sessão

                //  Redireeciona consoante o papel do Utilizador
                if($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: events.php"); //  Redireciona utilziadores normais para a página de eventos
                }
                exit;
            } else {
                $errorMessage = 'Nome de utilizador ou palavra-passe incorretos.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login e registo para a plataforma EventHive, onde podes gerir e comprar bilhetes para os teus eventos favoritos.">
    <meta name="keywords" content="Gestão de eventos, bilhetes, concertos, espetáculos, festivais, artistas, bandas, música, cultura">
    <meta name="author" content="Pedro Costa">
    <title>EventHive - Login/Registar</title>

    <link rel="stylesheet" href="./Styles/style.css">
    <!-- ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css" xintegrity="sha512-kJlvECunwXftkPwyvHbclArO8wszgBGisiLeuDFwNM8ws+wKIw0sv1os3ClWZOcrEB2eRXULYUsm8OVRGJKwGA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <div class="form-container">
        <!-- Formulário de Registo -->
        <div class="form-box <?php echo $formType === 'register' ? 'show' : ''; ?>" id="register-form">
            <form method="post" action="login_register.php" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="register">
                <h2>Registar</h2>
                <hr>
                <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirma a Password" required>
                <select name="role">
                    <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] == 'user') ? 'selected' : ''; ?>>Utilizador</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admnistrador</option>
                </select>
                <button type="submit" name="registar">Registar</button>
                <p>Já tens conta? <a href="login_register.php?form=login">Login</a></p>
                <?php if ($formType === 'register' && !empty($errorMessage)): ?>
                    <p class="error-message" style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
            </form>
        </div>

        <!-- Formulário de Login -->
        <div class="form-box <?php echo $formType === 'login' ? 'show' : ''; ?>" id="login-form">
            <form method="post" action="login_register.php" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="login">
                <h2>Login</h2>
                <hr>
                <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Não tens conta? <a href="login_register.php?form=register">Registar</a></p>
                <?php if ($formType === 'login' && !empty($errorMessage)): ?>
                    <p class="error-message" style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
            </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script src="./Script/script.js"></script>
</body>
</html>
