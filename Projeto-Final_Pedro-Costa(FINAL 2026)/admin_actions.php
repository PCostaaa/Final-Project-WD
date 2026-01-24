<?php 
session_start();
require_once "dbconnection.php";

header('Content-Type: application/json');

// Verifica se o utilizador é admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}


$action = $_POST['action'] ?? '';

// Verifica a ação e executa o código correspondente
switch ($action) {
    case 'update_status':
        $purchaseId = intval($_POST['purchase_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($purchaseId > 0 && in_array($status, ['pending', 'completed', 'cancelled'])) {
            try {
                $stmt = $pdo->prepare("UPDATE purchases SET status = ? WHERE id = ?");
                $stmt->execute([$status, $purchaseId]);
                echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso.']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Erro na base de dados ao atualizar status: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos para atualização de status.']);
        }
        break;

    case 'delete_user':
        $userId = intval($_POST['id'] ?? 0);

        if ($userId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Utilizador apagado com sucesso.']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Utilizador não encontrado ou já apagado.']);
                }
            } catch (PDOException $e) {
                error_log("Erro de DB ao apagar utilizador: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Erro na base de dados ao apagar o utilizador.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'ID de utilizador inválido.']);
        }
        break;

    case 'edit_user':
        $userId = intval($_POST['id'] ?? 0);
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';

        if ($userId > 0 && !empty($username) && filter_var($email, FILTER_VALIDATE_EMAIL) && in_array($role, ['admin', 'user'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role, $userId]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Utilizador atualizado com sucesso.']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Nenhuma alteração feita ou utilizador não encontrado.']);
                }
            } catch (PDOException $e) {
                error_log("Erro de DB ao atualizar utilizador: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Erro na base de dados ao atualizar o utilizador.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos para edição do utilizador.']);
        }
        break;

    case 'edit_purchase':
        $purchaseId = intval($_POST['id'] ?? 0);
        $userId = intval($_POST['user_id'] ?? 0);
        $eventId = intval($_POST['event_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($purchaseId > 0 && $userId > 0 && $eventId > 0 && $quantity > 0 && in_array($status, ['pending', 'completed', 'cancelled'])) {
            try {
                $stmt = $pdo->prepare("UPDATE purchases SET user_id = ?, event_id = ?, quantity = ?, status = ? WHERE id = ?");
                $stmt->execute([$userId, $eventId, $quantity, $status, $purchaseId]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Compra atualizada com sucesso.']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Nenhuma alteração feita ou compra não encontrada.']);
                }
            } catch (PDOException $e) {
                error_log("Erro de DB ao atualizar compra: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Erro na base de dados ao atualizar compra.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Dados inválidos para edição da compra.']);
        }
        break;

    case 'delete_purchase':
        $purchaseId = intval($_POST['id'] ?? 0);

        if ($purchaseId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM purchases WHERE id = ?");
                $stmt->execute([$purchaseId]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Compra apagada com sucesso.']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Compra não encontrada.']);
                }
            } catch (PDOException $e) {
                error_log("Erro de DB ao apagar compra: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Erro na base de dados ao apagar compra.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'ID de compra inválido.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Ação desconhecida.']);
        break;
}
exit;
?>
