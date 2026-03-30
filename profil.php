<?php
session_start();
require 'db.php'; // Assure-toi que db.php utilise mysqli_connect()

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int)$_SESSION['user_id']; // sécurité : forcer un entier

// 1. Récupérer les infos du client
$query_user = "SELECT * FROM clients WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $query_user);
mysqli_stmt_bind_param($stmt_user, "i", $id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);
mysqli_stmt_close($stmt_user);

// 2. Action : Modifier le profil
if (isset($_POST['modifier'])) {
    $n_nom = $_POST['nom'];
    $n_tel = $_POST['telephone'];

    $update_query = "UPDATE clients SET nom = ?, telephone = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt_update, "ssi", $n_nom, $n_tel, $id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    header("Location: profil.php?m=1");
    exit();
}

// 3. Récupérer l'historique
$query_hist = "SELECT * FROM reservations WHERE client_id = ?";
$stmt_hist = mysqli_prepare($conn, $query_hist);
mysqli_stmt_bind_param($stmt_hist, "i", $id);
mysqli_stmt_execute($stmt_hist);
$history = mysqli_stmt_get_result($stmt_hist);
mysqli_stmt_close($stmt_hist);
?>
<!DOCTYPE html>
<html>
<head><title>Mon Profil</title></head>
<body>
    <h2>Profil de <?php echo htmlspecialchars($user['prenom']); ?></h2>
    
    <form method="POST">
        Nom : <input type="text" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>"><br>
        Tél : <input type="text" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>"><br>
        <input type="submit" name="modifier" value="Sauvegarder">
    </form>

    <h3>Mes Réservations</h3>
    <table border="1">
        <tr><th>Chambre</th><th>Date Arrivée</th></tr>
        <?php while($row = mysqli_fetch_assoc($history)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['chambre']); ?></td>
            <td><?php echo htmlspecialchars($row['date_debut']); ?></td>
        </tr>
        <?php } ?>
    </table>

    <a href="logout.php">Déconnexion</a>
</body>
</html>