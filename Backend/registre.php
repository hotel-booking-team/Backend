<?php
// Configuration de la session pour une meilleure persistance
ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60); // 30 jours
session_set_cookie_params(30 * 24 * 60 * 60); // 30 jours

session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prenom    = trim($_POST['prenom']);
    $nom       = trim($_POST['nom']);
    $email     = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    // Vérifications de base
    if (empty($prenom) || empty($nom) || empty($email) || empty($telephone) || empty($password)) {
        header("Location: ../frontend/registre.html?error=champs_vides");
        exit();
    }

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../frontend/registre.html?error=invalid_email");
        exit();
    }

    // Validation du mot de passe (min 8 caractères)
    if (strlen($password) < 8) {
        header("Location: ../frontend/registre.html?error=weak_password");
        exit();
    }

    if ($password !== $confirm) {
        header("Location: ../frontend/registre.html?error=passwords_mismatch");
        exit();
    }

    // Validation des longueurs de chaînes
    if (strlen($prenom) > 50 || strlen($nom) > 50) {
        header("Location: ../frontend/registre.html?error=field_too_long");
        exit();
    }

    // Vérifier si l'email existe déjà
    $stmt_check = $conn->prepare("SELECT id FROM clients WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: ../frontend/registre.html?error=email_exists");
        exit();
    }
    $stmt_check->close();

    // Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insérer le nouveau client
    $stmt = $conn->prepare("INSERT INTO clients (prenom, nom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $prenom, $nom, $email, $telephone, $hashed_password);

    if ($stmt->execute()) {
        // Récupérer l'ID du nouvel utilisateur et ouvrir la session
        $new_id = $stmt->insert_id;
        $_SESSION['user_id']     = $new_id;
        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['success']     = "Bienvenue, " . htmlspecialchars($prenom) . " ! Votre compte a été créé avec succès.";

        $stmt->close();

        // ✅ Redirection vers le profil après inscription
        header("Location: profil.php");
        exit();
    } else {
        $stmt->close();
        header("Location: ../frontend/registre.html?error=server_error");
        exit();
    }
}
?>