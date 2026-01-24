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

    if (!$id) {
        $response['error'] = 'ID do evento inválido.';
        echo json_encode($response);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT image_url FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event && $event['image_url'] && file_exists($event['image_url']) && strpos($event['image_url'], 'https://placehold.co/') === false) {
        }

        // Apaga o evento da Base de Dados
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Evento eliminado com sucesso!';
        } else {
            $response['error'] = 'Evento não encontrado ou já eliminado.';
        }
    } catch (PDOException $e) {
        error_log("Database error in delete_event.php: " . $e->getMessage());
        $response['error'] = 'Erro na base de dados ao eliminar evento. Por favor, tente novamente.';
    }
} else {
    $response['error'] = 'Método de requisição inválido.';
}

echo json_encode($response);
exit();
?>