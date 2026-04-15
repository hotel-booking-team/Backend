<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "hotel_el_mahari";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connexion impossible : " . mysqli_connect_error());
}
?>