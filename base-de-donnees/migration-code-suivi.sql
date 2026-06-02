-- =============================================================
-- Migration : code de suivi aléatoire (anti-énumération)
-- À exécuter APRÈS migration-suivi.sql
-- =============================================================

USE perroquets;

-- Code de suivi public, aléatoire et non devinable (ex: MP-X7K2-9QF4)
ALTER TABLE reservation
    ADD COLUMN IF NOT EXISTS code_suivi VARCHAR(20) NULL AFTER statut_livraison,
    ADD UNIQUE KEY IF NOT EXISTS uq_reservation_code_suivi (code_suivi);
