-- =============================================================
-- Migration : coordonnées de l'entreprise (NAP) dans `parametre`
-- À exécuter APRÈS migration-parametres.sql. Modifiables ensuite
-- dans l'admin : Paramètres > Contact & WhatsApp.
-- =============================================================

USE perroquets;

-- INSERT IGNORE : n'écrase pas une valeur déjà saisie dans l'admin.
INSERT IGNORE INTO parametre (cle, valeur) VALUES
    ('entreprise_rue',            '61 Woodstone Cres'),
    ('entreprise_ville',          'Moncton'),
    ('entreprise_province',       'NB'),
    ('entreprise_code_postal',    'E1G 1W3'),
    ('entreprise_telephone',      '+1 437-837-6858'),
    ('entreprise_courriel',       'perroquetsmaple@gmail.com'),
    ('entreprise_annee_creation', '2003');
