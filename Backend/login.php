<?php
// Configuration de la session pour une meilleure persistance
ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60); // 30 jours
session_set_cookie_params(30 * 24 * 60 * 60); // 30 jours

session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation basique
    if (empty($email) || empty($password)) {
        header("Location: ../frontend/login.html?error=missing_fields");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, nom, prenom, mot_de_passe FROM clients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_nom']    = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['last_activity'] = time();

            // Gestion du "Remember Me"
            if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                $token = bin2hex(random_bytes(32)); // Générer un token sécurisé
                $expires = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60); // 30 jours
                
                // Créer la table remember_tokens si elle n'existe pas
                $conn->query("CREATE TABLE IF NOT EXISTS remember_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(255) UNIQUE NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES clients(id) ON DELETE CASCADE
                )");
                
                // Insérer le token en base de données
                $stmt_remember = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                if ($stmt_remember) {
                    $stmt_remember->bind_param("iss", $user['id'], $token, $expires);
                    $stmt_remember->execute();
                    $stmt_remember->close();
                    
                    // Définir le cookie
                    setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/', '', false, true);
                }
            }

            // ✅ Login → page services
            header("Location: ../frontend/services.php");
            exit();
        } else {
            header("Location: ../frontend/login.html?error=wrong_password");
            exit();
        }
    } else {
        header("Location: ../frontend/login.html?error=not_found");
        exit();
    }
    $stmt->close();
}
?>