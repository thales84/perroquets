-- =============================================================
-- Migration : comptes clients (VERSION PRODUCTION)
-- À importer APRÈS 01-schema.sql. Sélectionner la BD dans phpMyAdmin.
-- =============================================================

-- Table des clients
CREATE TABLE IF NOT EXISTS client (
    id_client         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prenom            VARCHAR(80)  NOT NULL,
    nom               VARCHAR(80)  NOT NULL,
    email             VARCHAR(160) NOT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    province          VARCHAR(60)  NULL,
    telephone         VARCHAR(30)  NULL,
    date_inscription  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_client_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lier les réservations aux clients (nullable = réservation invité possible)
ALTER TABLE reservation
    ADD COLUMN IF NOT EXISTS client_id INT UNSIGNED NULL AFTER id_reservation,
    ADD CONSTRAINT fk_reservation_client
        FOREIGN KEY (client_id) REFERENCES client(id_client) ON DELETE SET NULL;
