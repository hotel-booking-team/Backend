<?php
session_start();
$host = "localhost";
$db   = "hotel_mahari";
$user = "root";
$pass = "";
$conn = new mysqli($host, $user, $pass, $db);
// vérifier connexion
if ($conn->connect_error) {
    die("Erreur connexion");
}
$email = $_POST['email'];
$password = $_POST['password'];
// vérifier utilisateur
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION["user"] = $email;
        header("Location: dashboard.php");
    } else {
        echo "Mot de passe incorrect";
    }
} else {
    echo "Utilisateur non trouvé";
}
?>