<?php
session_start();

header('Content-Type: application/json');

$response = [
    'isLoggedIn' => isset($_SESSION['username'])
];

echo json_encode($response);
?>