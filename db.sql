-- 1. Créer la base
CREATE DATABASE hotel_elmahari;

-- 2. Utiliser la base
USE hotel_elmahari;

-- 3. Créer la table des clients
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100),
    telephone VARCHAR(20),
    mot_de_passe VARCHAR(100),
    date_inscription DATETIME
);

-- 4. Créer la table des réservations
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    chambre VARCHAR(50),
    date_debut DATE,
    date_fin DATE
);