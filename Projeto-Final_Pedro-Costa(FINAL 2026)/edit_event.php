<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit();
}

require_once "dbconnection.php";

$response = ['success' => false, 'message' => '', 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $ticket_price = filter_var($_POST['ticket_price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $available_tickets = filter_var($_POST['available_tickets'] ?? 0, FILTER_VALIDATE_INT);

    // Valida Id e outros parâmetros
    if (!$id || empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location) || $ticket_price === false || $available_tickets === false || $ticket_price < 0 || $available_tickets < 0) {
        $response['error'] = 'Dados de evento incompletos ou inválidos para edição.';
        echo json_encode($response);
        exit();
    }

    $currentImageUrl = null;
    try {
        $stmt = $pdo->prepare("SELECT image_url FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($event) {
            $currentImageUrl = $event['image_url'];
        }
    } catch (PDOException $e) {
        error_log("Database error fetching image_url in edit_event.php: " . $e->getMessage());
        $response['error'] = 'Erro ao procurar dados do evento para edição.';
        echo json_encode($response);
        exit();
    }

    $image_url_to_save = $currentImageUrl;

    // Upload de Imagem
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['event_image']['tmp_name'];
        $fileName = $_FILES['event_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFileDir = './uploads/events/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_url_to_save = $dest_path;
                if ($currentImageUrl && file_exists($currentImageUrl) && strpos($currentImageUrl, 'https://placehold.co/') === false) {
                }
            } else {
                $response['error'] = 'Erro ao fazer upload da nova imagem.';
                echo json_encode($response);
                exit();
            }
        } else {
            $response['error'] = 'Tipo de arquivo de nova imagem não permitido.';
            echo json_encode($response);
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, ticket_price = ?, available_tickets = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$title, $description, $event_date, $event_time, $location, $ticket_price, $available_tickets, $image_url_to_save, $id]);

        $response['success'] = true;
        $response['message'] = 'Evento atualizado com sucesso!';
    } catch (PDOException $e) {
        error_log("Database error in edit_event.php: " . $e->getMessage());
        $response['error'] = 'Erro na base de dados ao atualizar evento. Por favor, tente novamente.';
    }
} else {
    $response['error'] = 'Método de requisição inválido.';
}

echo json_encode($response);
exit();
?>