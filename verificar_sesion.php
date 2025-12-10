<?php
session_save_path(__DIR__ . '/sessions');
session_start();
header('Content-Type: application/json');

echo json_encode([
    'loggedIn' => isset($_SESSION['user_id']),
    'id'       => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['username'] ?? null
]);
