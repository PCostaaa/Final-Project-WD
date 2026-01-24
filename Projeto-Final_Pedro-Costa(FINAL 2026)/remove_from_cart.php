<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$eventId = null;
if (isset($_POST['id']) && ctype_digit($_POST['id'])) {
  $eventId = (int)$_POST['id'];
} elseif (isset($_GET['id']) && ctype_digit($_GET['id'])) {
  $eventId = (int)$_GET['id'];
}

$return = $_POST['return'] ?? $_GET['return'] ?? 'view_cart.php';

if ($eventId === null) {
  header('Location: ' . $return . '?msg=invalid'); exit;
}

if (isset($_SESSION['cart'][$eventId])) {
  unset($_SESSION['cart'][$eventId]);
  if (empty($_SESSION['cart'])) unset($_SESSION['cart']);
  header('Location: ' . $return . '?msg=removed'); exit;
}

header('Location: ' . $return . '?msg=notfound'); exit;
