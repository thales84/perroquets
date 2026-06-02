-- =============================================================
-- Migration : table des paramètres (VERSION PRODUCTION)
-- À importer APRÈS 04-migration-code-suivi.sql. Sélectionner la BD.
-- =============================================================

CREATE TABLE IF NOT EXISTS parametre (
    cle    VARCHAR(60) NOT NULL,
    valeur TEXT        NULL,
    PRIMARY KEY (cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO parametre (cle, valeur) VALUES
    ('site_nom',            'Maple Perroquets'),
    ('site_slogan',         'Perroquets élevés à la main au Canada'),
    ('meta_titre_gabarit',  '%page% — %site%'),
    ('meta_description',     'Perroquets élevés à la main, disponibles à la vente au Canada. Réservation simple, sans paiement en ligne.'),
    ('partage_image',       ''),
    ('og_locale',           'fr_CA'),
    ('twitter_compte',      ''),
    ('social_facebook',     ''),
    ('social_instagram',    ''),
    ('index_autoriser',     '1'),
    ('verif_google',        ''),
    ('verif_bing',          ''),
    ('ga4_id',              ''),
    ('gtm_id',              ''),
    ('pixel_id',            '');
