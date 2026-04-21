<?php
session_start();
require 'db.php';

// Nettoyer le token de "remember me" de la base de données
if (isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt_delete = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("s", $token);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
}

// Supprimer le cookie
setcookie('remember_token', '', time() - 3600, '/', '', false, true);

// Détruire la session
session_destroy();
header("Location: ../frontend/login.html");
exit();
?>