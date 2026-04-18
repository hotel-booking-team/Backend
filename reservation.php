<?php
session_start();
require 'db.php';

// ── 0. L'utilisateur doit être connecté ─────────────────────────────────────
if (!isset($_SESSION['client_id'])) {
    header("Location: ../frontend/login.html?redirect=reservation");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../frontend/reservation.html");
    exit;
}

$client_id = intval($_SESSION['client_id']);

// ── 1. Récupération des champs ───────────────────────────────────────────────
$room_id    = intval($_POST['room_id']   ?? 0);
$date_debut = trim($_POST['checkin']     ?? '');
$date_fin   = trim($_POST['checkout']    ?? '');
$adultes    = intval($_POST['adultes']   ?? 1);
$enfants    = intval($_POST['enfants']   ?? 0);
$notes      = trim($_POST['demandes']    ?? '');

// ── 2. Validation des champs ─────────────────────────────────────────────────
if ($room_id <= 0 || empty($date_debut) || empty($date_fin)) {
    header("Location: ../frontend/reservation.html?status=error&message=Champs_invalides");
    exit;
}

// ── 3. Validation des dates ──────────────────────────────────────────────────
$dtDebut = DateTime::createFromFormat('Y-m-d', $date_debut);
$dtFin   = DateTime::createFromFormat('Y-m-d', $date_fin);
$today   = new DateTime('today');

if (!$dtDebut || !$dtFin) {
    header("Location: ../frontend/reservation.html?status=error&message=Dates_invalides");
    exit;
}
if ($dtDebut < $today) {
    header("Location: ../frontend/reservation.html?status=error&message=Date_arrivee_passee");
    exit;
}
if ($dtFin <= $dtDebut) {
    header("Location: ../frontend/reservation.html?status=error&message=Date_depart_invalide");
    exit;
}

// Convertir au format datetime attendu par la BDD (check-in 14h / check-out 12h)
$datetime_debut = $date_debut . ' 14:00:00';
$datetime_fin   = $date_fin   . ' 12:00:00';

try {
    // ── 4. Vérifier que la chambre existe et n'est pas en maintenance ────────
    $stmtRoom = $conn->prepare("SELECT id, prix, statut FROM rooms WHERE id = ?");
    $stmtRoom->bind_param("i", $room_id);
    $stmtRoom->execute();
    $room = $stmtRoom->get_result()->fetch_assoc();

    if (!$room) {
        header("Location: ../frontend/reservation.html?status=error&message=Chambre_introuvable");
        exit;
    }
    if ($room['statut'] === 'maintenance') {
        header("Location: ../frontend/reservation.html?status=error&message=Chambre_indisponible&room_id=" . $room_id);
        exit;
    }

    // ── 5. Vérifier les chevauchements de dates ──────────────────────────────
    // Deux séjours se chevauchent si : debut_existant < fin_nouveau ET fin_existant > debut_nouveau
    $stmtCheck = $conn->prepare("
        SELECT id FROM reservations
        WHERE room_id = ?
          AND statut NOT IN ('annulee', 'terminee')
          AND date_debut < ?
          AND date_fin   > ?
    ");
    $stmtCheck->bind_param("iss", $room_id, $datetime_fin, $datetime_debut);
    $stmtCheck->execute();
    $conflict = $stmtCheck->get_result();

    if ($conflict->num_rows > 0) {
        header("Location: ../frontend/reservation.html?status=error&message=Chambre_indisponible&room_id=" . $room_id);
        exit;
    }

    // ── 6. Calcul du montant total ───────────────────────────────────────────
    $nights        = $dtDebut->diff($dtFin)->days;
    $montant_total = $nights * floatval($room['prix']);

    // ── 7. Insertion ─────────────────────────────────────────────────────────
    $stmtInsert = $conn->prepare("
        INSERT INTO reservations
            (client_id, type_reservation, room_id, date_debut, date_fin, statut, montant_total, notes)
        VALUES
            (?, 'chambre', ?, ?, ?, 'en_attente', ?, ?)
    ");
    $stmtInsert->bind_param(
        "iissds",
        $client_id, $room_id,
        $datetime_debut, $datetime_fin,
        $montant_total, $notes
    );

    if ($stmtInsert->execute()) {
        $reservation_id = $conn->insert_id;
        header("Location: ../frontend/reservation.html?status=success&reservation_id=" . $reservation_id);
        exit;
    } else {
        throw new Exception("Erreur lors de l'insertion");
    }

} catch (Exception $e) {
    error_log("Erreur réservation : " . $e->getMessage());
    header("Location: ../frontend/reservation.html?status=error&message=Erreur_serveur");
    exit;
}