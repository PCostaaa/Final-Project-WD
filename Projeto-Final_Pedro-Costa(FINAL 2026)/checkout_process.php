<?php
declare(strict_types=1);
ob_start();
session_start();
require_once __DIR__ . '/dbconnection.php';

try {
    if (!isset($_SESSION['username'])) {
        header('Location: login_register.php?form=login&redirect=' . urlencode('view_cart.php')); exit;
    }
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        header('Location: view_cart.php?msg=invalid'); exit;
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId || !ctype_digit((string)$userId)) {
        header('Location: login_register.php?form=login&redirect=' . urlencode('view_cart.php')); exit;
    }

    // reverificação e correção automática
    $cart = $_SESSION['cart'];
    $ids = array_keys($cart);
    $ids = array_values(array_filter($ids, fn($v) => ctype_digit((string)$v)));
    $insufficient = [];

    if (!empty($ids)) {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, available_tickets, ticket_price FROM events WHERE id IN ($ph)");
        $stmt->execute($ids);
        $byId = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) $byId[(int)$r['id']] = $r;

        foreach ($cart as $eventId => $qty) {
            $eventId = (int)$eventId; $qty = (int)$qty;
            $ev = $byId[$eventId] ?? null;
            if (!$ev || (int)$ev['available_tickets'] <= 0) {
                unset($cart[$eventId]);           // removido/esgotado
                $insufficient[] = $eventId;
                continue;
            }
            $stock = (int)$ev['available_tickets'];
            if ($qty > $stock) {
                $cart[$eventId] = $stock;         // reduzir ao stock
                $insufficient[] = $eventId;
            }
        }
    }

    if (!empty($insufficient)) {
        $_SESSION['cart'] = $cart; // guardar correções
        header('Location: view_cart.php?msg=nostock_adjusted'); // mostra ao utilizador o que mudou
        exit;
    }

   
    $pdo->beginTransaction();

    foreach ($_SESSION['cart'] as $eventId => $quantity) {
        $stmt = $pdo->prepare('SELECT ticket_price, available_tickets FROM events WHERE id = ? FOR UPDATE');
        $stmt->execute([(int)$eventId]);
        $ev = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ev || (int)$quantity > (int)$ev['available_tickets']) {
            throw new Exception('Stock alterou entretanto.');
        }

        $price    = (float)$ev['ticket_price'];
        $subtotal = $price * (int)$quantity;

        $stmt = $pdo->prepare('UPDATE events SET available_tickets = available_tickets - ? WHERE id = ?');
        $stmt->execute([(int)$quantity, (int)$eventId]);

        $stmt = $pdo->prepare('INSERT INTO purchases (user_id, event_id, quantity, total_price, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([(int)$userId, (int)$eventId, (int)$quantity, $subtotal, 'paid']);
    }

    $pdo->commit();
    unset($_SESSION['cart']);
    header('Location: purchase_success.php?from=checkout'); exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['purchase_error'] = 'Erro na compra: ' . $e->getMessage();
    header('Location: view_cart.php?msg=error'); exit;
}

