<?php
session_start();
header('Content-Type: application/json');

// Certifica se o utilizador está logado e se é ADMIN
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

require_once "dbconnection.php";

$response = ['success' => false, 'message' => '', 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $ticket_price = filter_var($_POST['ticket_price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $available_tickets = filter_var($_POST['available_tickets'] ?? 0, FILTER_VALIDATE_INT);

    
    if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location) || $ticket_price === false || $available_tickets === false || $ticket_price < 0 || $available_tickets < 0) {
        $response['error'] = 'Por favor, preencha todos os campos obrigatórios corretamente.';
        echo json_encode($response);
        exit();
    }

    $image_url = null;

    // Trata do Upload de Imagem
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['event_image']['tmp_name'];
        $fileName = $_FILES['event_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFileDir = './uploads/events/';

            // Cria diretório se não existir
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_url = $dest_path; // Guarda o caminhor relativo na Base de Dados
            } else {
                $response['error'] = 'Erro ao fazer upload da imagem. Verifique as permissões da pasta "uploads/events".';
                echo json_encode($response);
                exit();
            }
        } else {
            $response['error'] = 'Tipo de arquivo de imagem não suportado. Apenas JPG, JPEG, PNG, GIF são aceites.';
            echo json_encode($response);
            exit();
        }
    }

    try {
        // Prepara e executa SQL INSERT
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, ticket_price, available_tickets, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $event_date, $event_time, $location, $ticket_price, $available_tickets, $image_url]);

        $response['success'] = true;
        $response['message'] = 'Evento criado com sucesso!';
    } catch (PDOException $e) {
        
        error_log("Database error in create_event.php: " . $e->getMessage());
        $response['error'] = 'Erro na base de dados ao criar evento. Por favor, tente novamente.';
    }
} else {
    $response['error'] = 'Método de requisição inválido.';
}

echo json_encode($response);
exit();
?>