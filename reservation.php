<?php
session_start();
require __DIR__ . '/db.php';

function redirect_to(string $path): void
{
    header('Location: ' . $path);
    exit();
}

function has_column(mysqli $conn, string $table, string $column): bool
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);

    $stmt = $conn->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?
         LIMIT 1'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

if (!isset($_SESSION['user_id'])) {
    redirect_to('../frontend/login.html?next=' . urlencode('reservation.html'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('../frontend/reservation.html');
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? 'create';

if ($action === 'cancel') {
    $reservationId = (int)($_POST['reservation_id'] ?? 0);

    if ($reservationId <= 0) {
        redirect_to('profil.php?status=error&message=reservation_invalide');
    }

    $check = $conn->prepare('SELECT id FROM reservations WHERE id = ? AND client_id = ? LIMIT 1');
    if (!$check) {
        redirect_to('profil.php?status=error&message=server');
    }

    $check->bind_param('ii', $reservationId, $userId);
    $check->execute();
    $res = $check->get_result();

    if (!$res || $res->num_rows === 0) {
        $check->close();
        redirect_to('profil.php?status=error&message=non_autorise');
    }
    $check->close();

    if (has_column($conn, 'reservations', 'statut')) {
        $cancel = $conn->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ? AND client_id = ?");
        if ($cancel) {
            $cancel->bind_param('ii', $reservationId, $userId);
            $cancel->execute();
            $cancel->close();
        }
    } else {
        // Compatibilité ancien schéma: suppression si colonne statut absente
        $cancel = $conn->prepare('DELETE FROM reservations WHERE id = ? AND client_id = ?');
        if ($cancel) {
            $cancel->bind_param('ii', $reservationId, $userId);
            $cancel->execute();
            $cancel->close();
        }
    }

    redirect_to('profil.php?status=cancelled');
}

// Action par défaut: création de réservation
$prenom   = trim($_POST['prenom'] ?? '');
$nom      = trim($_POST['nom'] ?? '');
$email    = trim($_POST['email'] ?? '');
$chambre  = trim($_POST['chambre'] ?? '');
$checkin  = trim($_POST['checkin'] ?? '');
$checkout = trim($_POST['checkout'] ?? '');
$adultes  = max(1, (int)($_POST['adultes'] ?? 2));
$enfants  = max(0, (int)($_POST['enfants'] ?? 0));
$demandes = trim($_POST['demandes'] ?? '');

if ($prenom === '' || $nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $chambre === '' || $checkin === '' || $checkout === '') {
    redirect_to('../frontend/reservation.html?status=error&message=champs_invalides');
}

$startTs = strtotime($checkin);
$endTs = strtotime($checkout);
if ($startTs === false || $endTs === false || $endTs <= $startTs) {
    redirect_to('../frontend/reservation.html?status=error&message=dates_invalides');
}

$hasChambreId = has_column($conn, 'reservations', 'chambre_id');
$hasServiceId = has_column($conn, 'reservations', 'service_id');
$hasAdultes = has_column($conn, 'reservations', 'adultes');
$hasEnfants = has_column($conn, 'reservations', 'enfants');
$hasDemandes = has_column($conn, 'reservations', 'demandes');
$hasStatut = has_column($conn, 'reservations', 'statut');

$chambreId = null;
if ($hasChambreId && has_column($conn, 'chambres', 'id')) {
    $findRoom = $conn->prepare('SELECT id FROM chambres WHERE code = ? LIMIT 1');
    if ($findRoom) {
        $findRoom->bind_param('s', $chambre);
        $findRoom->execute();
        $roomRes = $findRoom->get_result();
        if ($roomRes && $roomRes->num_rows > 0) {
            $room = $roomRes->fetch_assoc();
            $chambreId = (int)$room['id'];
        }
        $findRoom->close();
    }

    if ($chambreId === null) {
        $nomChambre = ucfirst($chambre);
        $insertRoom = $conn->prepare('INSERT INTO chambres (code, nom) VALUES (?, ?)');
        if ($insertRoom) {
            $insertRoom->bind_param('ss', $chambre, $nomChambre);
            if ($insertRoom->execute()) {
                $chambreId = (int)$conn->insert_id;
            }
            $insertRoom->close();
        }
    }
}

$columns = ['client_id', 'chambre', 'date_debut', 'date_fin'];
$values = [$userId, $chambre, $checkin, $checkout];
$types = 'isss';

if ($hasChambreId) {
    $columns[] = 'chambre_id';
    $values[] = $chambreId;
    $types .= 'i';
}
if ($hasServiceId) {
    $columns[] = 'service_id';
    $values[] = null;
    $types .= 'i';
}
if ($hasAdultes) {
    $columns[] = 'adultes';
    $values[] = $adultes;
    $types .= 'i';
}
if ($hasEnfants) {
    $columns[] = 'enfants';
    $values[] = $enfants;
    $types .= 'i';
}
if ($hasDemandes) {
    $columns[] = 'demandes';
    $values[] = $demandes;
    $types .= 's';
}
if ($hasStatut) {
    $columns[] = 'statut';
    $values[] = 'confirmée';
    $types .= 's';
}

$placeholders = implode(', ', array_fill(0, count($columns), '?'));
$sql = 'INSERT INTO reservations (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
$stmt = $conn->prepare($sql);

if (!$stmt) {
    redirect_to('../frontend/reservation.html?status=error&message=server');
}

// bind_param exige des références
$bindParams = [];
$bindParams[] = &$types;
foreach ($values as $k => $v) {
    $bindParams[] = &$values[$k];
}
call_user_func_array([$stmt, 'bind_param'], $bindParams);

if (!$stmt->execute()) {
    $stmt->close();
    redirect_to('../frontend/reservation.html?status=error&message=insert_failed');
}

$stmt->close();
redirect_to('../frontend/reservation.html?status=success');
