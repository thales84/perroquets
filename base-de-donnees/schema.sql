-- =============================================================
-- Projet : mapleperroquets.com
-- Fichier : schema.sql — Création du schéma complet
-- Encodage : utf8mb4 / InnoDB
-- =============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -------------------------------------------------------------
-- Création de la base (exécuter en tant que root si nécessaire)
-- -------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS perroquets
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE perroquets;

-- -------------------------------------------------------------
-- Table : espece
-- Référentiel des espèces de perroquets proposées sur la vitrine.
-- Colonnes _en laissées NULL (bilingue par conception, FR d'abord).
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS espece (
    id_espece       INT            NOT NULL AUTO_INCREMENT,
    nom_commun_fr   VARCHAR(120)   NOT NULL,
    nom_commun_en   VARCHAR(120)   NULL,
    nom_scientifique VARCHAR(120)  NULL,
    famille_fr      VARCHAR(80)    NULL,
    famille_en      VARCHAR(80)    NULL,
    slug_fr         VARCHAR(140)   NOT NULL,
    slug_en         VARCHAR(140)   NULL,
    description_fr  TEXT           NULL,
    description_en  TEXT           NULL,

    PRIMARY KEY (id_espece),
    UNIQUE KEY uq_espece_slug_fr (slug_fr),
    UNIQUE KEY uq_espece_slug_en (slug_en)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table : oiseau
-- Chaque ligne représente un individu unique mis en vente.
-- Disparaît de la vitrine dès son statut = 'vendu'.
-- Index sur statut et id_espece pour les requêtes fréquentes.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS oiseau (
    id_oiseau       INT                              NOT NULL AUTO_INCREMENT,
    id_espece       INT                              NOT NULL,
    slug_fr         VARCHAR(180)                     NOT NULL,
    slug_en         VARCHAR(180)                     NULL,
    sexe            ENUM('male','femelle','inconnu') NOT NULL DEFAULT 'inconnu',
    date_naissance  DATE                             NULL,
    num_bague       VARCHAR(60)                      NULL,
    prix_cad        DECIMAL(10,2)                    NULL,
    sevre_main      TINYINT(1)                       NOT NULL DEFAULT 0,
    statut          ENUM('disponible','reserve','vendu') NOT NULL DEFAULT 'disponible',
    description_fr  TEXT                             NULL,
    description_en  TEXT                             NULL,
    date_ajout      DATETIME                         NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_oiseau),
    UNIQUE KEY uq_oiseau_slug_fr (slug_fr),
    UNIQUE KEY uq_oiseau_slug_en (slug_en),
    UNIQUE KEY uq_oiseau_num_bague (num_bague),
    INDEX idx_oiseau_statut (statut),
    INDEX idx_oiseau_espece (id_espece),

    CONSTRAINT fk_oiseau_espece
        FOREIGN KEY (id_espece) REFERENCES espece (id_espece)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table : photo
-- Photos liées à un oiseau. Cascade à la suppression de l'oiseau.
-- est_principale = 1 pour la photo affichée sur la carte vitrine.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS photo (
    id_photo          INT          NOT NULL AUTO_INCREMENT,
    id_oiseau         INT          NOT NULL,
    chemin_fichier    VARCHAR(255) NOT NULL,
    texte_alt_fr      VARCHAR(200) NULL,
    texte_alt_en      VARCHAR(200) NULL,
    est_principale    TINYINT(1)   NOT NULL DEFAULT 0,
    ordre_affichage   INT          NOT NULL DEFAULT 0,

    PRIMARY KEY (id_photo),
    INDEX idx_photo_oiseau (id_oiseau),

    CONSTRAINT fk_photo_oiseau
        FOREIGN KEY (id_oiseau) REFERENCES oiseau (id_oiseau)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table : reservation
-- Formulaire de demande de réservation envoyé par un visiteur.
-- Index sur statut_reservation pour le tableau de bord admin.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservation (
    id_reservation      INT                                   NOT NULL AUTO_INCREMENT,
    id_oiseau           INT                                   NOT NULL,
    nom_client          VARCHAR(120)                          NOT NULL,
    email_client        VARCHAR(160)                          NOT NULL,
    telephone           VARCHAR(30)                           NULL,
    province            VARCHAR(60)                           NULL,
    message             TEXT                                  NULL,
    langue_demande      ENUM('fr','en')                       NOT NULL DEFAULT 'fr',
    statut_reservation  ENUM('nouvelle','traitee','annulee')  NOT NULL DEFAULT 'nouvelle',
    date_demande        DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_reservation),
    INDEX idx_reservation_statut (statut_reservation),

    CONSTRAINT fk_reservation_oiseau
        FOREIGN KEY (id_oiseau) REFERENCES oiseau (id_oiseau)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- Table : admin
-- Comptes administrateurs du back-office.
-- mot_de_passe_hash stocké via password_hash() PHP (bcrypt).
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id_admin            INT          NOT NULL AUTO_INCREMENT,
    identifiant         VARCHAR(60)  NOT NULL,
    mot_de_passe_hash   VARCHAR(255) NOT NULL,

    PRIMARY KEY (id_admin),
    UNIQUE KEY uq_admin_identifiant (identifiant)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
