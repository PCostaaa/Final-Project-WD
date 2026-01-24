<?php

require_once "dbconnection.php";

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $eventId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            $response['success'] = true;
            // Format date and time for display
            $event['event_date'] = date('d/m/Y', strtotime($event['event_date']));
            $event['event_time'] = date('H:i', strtotime($event['event_time']));
            $response = array_merge($response, $event);
        } else {
            $response['error'] = 'Evento não encontrado.';
        }
    } catch (PDOException $e) {
        $response['error'] = 'Erro de base de dados: ' . $e->getMessage();
        error_log("DB Error em fetch_event_details.php: " . $e->getMessage());
    }
} else {
    $response['error'] = 'ID do evento inválido.';
}

echo json_encode($response);
exit();
?>
