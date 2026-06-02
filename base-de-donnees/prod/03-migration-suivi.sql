-- =============================================================
-- Migration : suivi de remise / expédition (VERSION PRODUCTION)
-- À importer APRÈS 02-migration-client.sql.
-- =============================================================

ALTER TABLE reservation
    ADD COLUMN IF NOT EXISTS statut_livraison ENUM('en_preparation','expedie','livre') NULL
        AFTER statut_reservation,
    ADD COLUMN IF NOT EXISTS numero_suivi    VARCHAR(100) NULL AFTER statut_livraison,
    ADD COLUMN IF NOT EXISTS transporteur    VARCHAR(80)  NULL AFTER numero_suivi,
    ADD COLUMN IF NOT EXISTS date_expedition DATE         NULL AFTER transporteur;
