<?php
// Configuration de la session
ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
session_set_cookie_params(30 * 24 * 60 * 60);

session_start();
require 'db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontend/login.html?error=not_logged_in");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $client_id    = (int)$_SESSION['user_id'];
    $chambre_type = trim($_POST['chambre_type'] ?? '');
    $checkin      = trim($_POST['checkin'] ?? '');
    $checkout     = trim($_POST['checkout'] ?? '');

    // Validation des champs obligatoires
    if (empty($chambre_type) || empty($checkin) || empty($checkout)) {
        header("Location: ../frontend/reservation.html?status=error&message=Champs_invalides");
        exit;
    }

    // Validation des dates
    $date_debut = DateTime::createFromFormat('Y-m-d', $checkin);
    $date_fin   = DateTime::createFromFormat('Y-m-d', $checkout);
    $today      = new DateTime('today');

    if (!$date_debut || !$date_fin) {
        header("Location: ../frontend/reservation.html?status=error&message=Dates_invalides");
        exit;
    }

    if ($date_debut < $today) {
        header("Location: ../frontend/reservation.html?status=error&message=Date_arrivee_passee");
        exit;
    }

    if ($date_fin <= $date_debut) {
        header("Location: ../frontend/reservation.html?status=error&message=Date_depart_invalide");
        exit;
    }

    // Déterminer le libellé de la chambre
    $chambres = [
        '1' => 'Chambre Standard',
        '3' => 'Chambre Supérieure',
        '5' => 'Chambre Deluxe',
        '7' => 'Suite Présidentielle',
    ];
    $chambre_label = $chambres[$chambre_type] ?? 'Chambre Standard';

    // Insérer la réservation
    try {
        $stmt = $conn->prepare("INSERT INTO reservations (client_id, chambre, date_debut, date_fin) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $client_id, $chambre_label, $checkin, $checkout);

        if ($stmt->execute()) {
            header("Location: ../frontend/reservation.html?status=success");
            exit;
        } else {
            throw new Exception("Erreur d'exécution");
        }
    } catch (Exception $e) {
        header("Location: ../frontend/reservation.html?status=error&message=Erreur_serveur");
        exit;
    }
} else {
    header("Location: ../frontend/reservation.html");
    exit;
}
