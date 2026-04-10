<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom      = $_POST['nom'];
    $prenom   = $_POST['prenom'];
    $email    = $_POST['email'];
    $phone    = $_POST['telephone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérifier si email déjà utilisé
    $check = $conn->prepare("SELECT id FROM clients WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        header("Location: registre.html?error=email_exists");
        exit();
    }
    $check->close();

    // Insérer le nouveau client
    $stmt = $conn->prepare("INSERT INTO clients (nom, prenom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nom, $prenom, $email, $phone, $password);

    if ($stmt->execute()) {
        // ✅ Connecter automatiquement après inscription
        $_SESSION['user_id']     = $conn->insert_id;
        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['success']     = "Inscription réussie ! Bienvenue " . $prenom . " 🎉";

        header("Location: profil.php");
        exit();
    } else {
        header("Location: registre.html?error=server");
        exit();
    }
    $stmt->close();
}
?>