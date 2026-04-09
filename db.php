<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "hotel_mahari";
$conn = mysqli_connect($host, $user, $pass, $db);
// Vérification de la connexion
if (!$conn) {
    die("Connexion impossible : " . mysqli_connect_error());
}
?>