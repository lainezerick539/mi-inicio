<?php
session_save_path(__DIR__ . '/sessions');
session_start();
session_destroy();
echo json_encode(['logout' => true]);
?>
