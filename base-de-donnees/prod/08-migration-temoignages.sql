-- =============================================================
-- Migration : témoignages clients (VERSION PRODUCTION)
-- À exécuter APRÈS 07-migration-coordonnees.sql. Sélectionner la BD. Gestion dans l'admin :
-- menu « Témoignages ».
-- =============================================================

CREATE TABLE IF NOT EXISTS temoignage (
    id_temoignage INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    titre_fr      VARCHAR(120)  NOT NULL DEFAULT '',
    titre_en      VARCHAR(120)  NOT NULL DEFAULT '',
    texte_fr      TEXT          NOT NULL,
    texte_en      TEXT          NULL,
    auteur        VARCHAR(120)  NOT NULL,
    ville         VARCHAR(80)   NOT NULL DEFAULT '',
    province      CHAR(2)       NOT NULL DEFAULT '',
    visible       TINYINT(1)    NOT NULL DEFAULT 1,
    ordre         INT           NOT NULL DEFAULT 0,
    date_ajout    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_temoignage),
    KEY idx_temoignage_visible_ordre (visible, ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
