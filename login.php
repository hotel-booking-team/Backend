<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nom, prenom, mot_de_passe FROM clients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_nom']    = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];

            // ✅ Login → page profil (page existante et protégée)
            header("Location: profil.php");
            exit();
        } else {
            header("Location: ../frontend/login.html?error=wrong_password");
            exit();
        }
    } else {
        header("Location: ../frontend/login.html?error=not_found");
        exit();
    }
    $stmt->close();
}
?>