<?php
session_start();
require 'db.php'; // Assure-toi que db.php utilise mysqli_connect()

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Requête sécurisée avec prepared statement
    $stmt = $conn->prepare("SELECT id, nom, mot_de_passe FROM clients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Vérifie le mot de passe hashé
        if (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            
            header("Location: profil.php");
            exit;
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Email introuvable.";
    }

    $stmt->close();
}
?>