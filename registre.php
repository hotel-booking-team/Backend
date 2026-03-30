<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $phone = $_POST['telephone'];
    $password = $_POST['password'];

    // Hasher le mot de passe pour la sécurité
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Requête sécurisée avec prepared statement
    $sql = "INSERT INTO clients (nom, prenom, email, telephone, mot_de_passe) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $nom, $prenom, $email, $phone, $password_hash);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: login.php?msg=success");
        exit();
    } else {
        echo "Erreur : " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
}
?>