<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $prenom = trim($_POST['prenom'] ?? '');
    $nom    = trim($_POST['nom'] ?? '');
    $email  = trim($_POST['email'] ?? '');


    
    if (empty($prenom) || empty($nom) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        header("Location: ../frontend/reservation.html?status=error&message=Champs_invalides");
        exit;
    }

    
    try {
        $stmt = $conn->prepare("INSERT INTO reservations (prenom, nom, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $prenom, $nom, $email);
        
        if ($stmt->execute()) {
            
            header("Location: ../frontend/reservation.html?status=success");
            exit;
        } else {
            throw new Exception("Erreur d'exécution");
        }
    } catch (Exception $e) {
        header("Location: ../frontend/reservation.html?status=error");
        exit;
    }
} else {
    header("Location: ../frontend/reservation.html");
    exit;
}