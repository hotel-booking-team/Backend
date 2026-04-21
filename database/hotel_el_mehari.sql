SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `note` int(1) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_avis` datetime DEFAULT current_timestamp(),
  `statut` enum('en_attente','publie','supprime') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_inscription` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `clients` (`id`, `nom`, `prenom`, `email`, `telephone`, `mot_de_passe`, `date_inscription`) VALUES
(1, 'Dupont', 'Jean', 'jean.dupont@email.com', '0612345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-04-06 19:24:05'),
(2, 'Martin', 'Sophie', 'sophie.martin@email.com', '0687654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-04-06 19:24:05');

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `mode_paiement` enum('carte','especes','virement','autre') DEFAULT 'carte',
  `statut` enum('en_attente','valide','echoue','rembourse') DEFAULT 'en_attente',
  `date_paiement` datetime DEFAULT current_timestamp(),
  `reference_transaction` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `type_reservation` enum('chambre','service','combo') NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `statut` enum('en_attente','confirmee','annulee','terminee') DEFAULT 'en_attente',
  `montant_total` decimal(10,2) DEFAULT NULL,
  `date_reservation` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ;

INSERT INTO `reservations` (`id`, `client_id`, `type_reservation`, `room_id`, `service_id`, `date_debut`, `date_fin`, `statut`, `montant_total`, `date_reservation`, `notes`) VALUES
(1, 1, 'chambre', 1, NULL, '2025-12-20 14:00:00', '2025-12-25 12:00:00', 'confirmee', 600.00, '2026-04-06 19:24:05', NULL),
(2, 1, 'service', NULL, 3, '2025-12-21 20:00:00', '2025-12-21 21:30:00', 'confirmee', 55.00, '2026-04-06 19:24:05', NULL),
(3, 2, 'chambre', 3, NULL, '2025-12-15 14:00:00', '2025-12-18 12:00:00', 'terminee', 540.00, '2026-04-06 19:24:05', NULL),
(4, 2, 'service', NULL, 5, '2025-12-16 10:00:00', '2025-12-16 14:00:00', 'terminee', 25.00, '2026-04-06 19:24:05', NULL);

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `type` varchar(50) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `statut` enum('disponible','occupee','reservee','maintenance') DEFAULT 'disponible',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `capacite` int(11) DEFAULT 2,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rooms` (`id`, `numero`, `type`, `prix`, `statut`, `description`, `image`, `capacite`, `created_at`) VALUES
(1, '101', 'Standard', 120.00, 'disponible', 'Chambre standard avec vue sur la ville, lit double, salle de bain privée', 'chambre101.jpg', 2, '2026-04-06 19:24:05'),
(2, '102', 'Standard', 120.00, 'disponible', 'Chambre standard confortable', 'chambre102.jpg', 2, '2026-04-06 19:24:05'),
(3, '201', 'Supérieure', 180.00, 'disponible', 'Chambre supérieure avec balcon, vue panoramique', 'chambre201.jpg', 3, '2026-04-06 19:24:05'),
(4, '202', 'Supérieure', 180.00, 'reservee', 'Chambre supérieure avec espace salon', 'chambre202.jpg', 3, '2026-04-06 19:24:05'),
(5, '301', 'Deluxe', 250.00, 'disponible', 'Chambre deluxe avec jacuzzi et vue mer', 'chambre301.jpg', 4, '2026-04-06 19:24:05'),
(6, '302', 'Deluxe', 250.00, 'occupee', 'Suite deluxe avec terrasse privée', 'chambre302.jpg', 4, '2026-04-06 19:24:05'),
(7, '401', 'Suite Présidentielle', 500.00, 'disponible', 'Suite présidentielle avec salon, cuisine et service personnalisé', 'suite401.jpg', 6, '2026-04-06 19:24:05');

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `nom_service` varchar(100) NOT NULL,
  `type_service` enum('spa','restaurant','piscine','excursion','autre') DEFAULT 'autre',
  `prix` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `duree` int(11) DEFAULT 60 COMMENT 'Durée en minutes',
  `disponibilite` enum('disponible','indisponible') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `services` (`id`, `nom_service`, `type_service`, `prix`, `description`, `image`, `duree`, `disponibilite`) VALUES
(1, 'Massage Relaxant', 'spa', 80.00, 'Massage aux huiles essentielles pour une relaxation profonde', 'massage.jpg', 60, 'disponible'),
(2, 'Soin du Visage', 'spa', 65.00, 'Soin hydratant et revitalisant pour le visage', 'soinvisage.jpg', 45, 'disponible'),
(3, 'Dîner Gastronomique', 'restaurant', 55.00, 'Menu 3 plats avec accord mets et vins', 'diner.jpg', 90, 'disponible'),
(4, 'Déjeuner Buffet', 'restaurant', 30.00, 'Buffet à volonté avec spécialités locales et internationales', 'buffet.jpg', 60, 'disponible'),
(5, 'Accès Piscine', 'piscine', 25.00, 'Accès à la piscine chauffée avec transats et serviettes', 'piscine.jpg', 240, 'disponible'),
(6, 'Excursion en Mer', 'excursion', 120.00, 'Sortie en bateau avec snorkeling et déjeuner à bord', 'excursion.jpg', 240, 'disponible'),
(7, 'Cours de Yoga', 'spa', 35.00, 'Session de yoga matinale au bord de la piscine', 'yoga.jpg', 60, 'disponible');

ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `reservation_id` (`reservation_id`);

ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `client_id` (`client_id`);

ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `service_id` (`service_id`);

ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`);
  
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paiements_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;
COMMIT;
