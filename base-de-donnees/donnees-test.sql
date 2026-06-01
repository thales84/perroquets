-- =============================================================
-- Projet : mapleperroquets.com
-- Fichier : donnees-test.sql — Jeu de données d'exemple
-- À exécuter APRÈS schema.sql sur la base "perroquets"
-- IMPORTANT : Remplacer le hash admin par le résultat de
--             generer-hash-admin.php avant d'insérer.
-- =============================================================

USE perroquets;

-- -------------------------------------------------------------
-- Nettoyage préalable — ordre inverse des FK pour éviter les erreurs
-- Permet de relancer ce script plusieurs fois sans conflit UNIQUE
-- -------------------------------------------------------------
DELETE FROM admin;
DELETE FROM reservation;
DELETE FROM photo;
DELETE FROM oiseau;
DELETE FROM espece;

-- -------------------------------------------------------------
-- Espèces (3) — colonnes _en laissées NULL (règle bilingue)
-- -------------------------------------------------------------
INSERT INTO espece
    (nom_commun_fr, nom_scientifique, famille_fr, slug_fr, description_fr)
VALUES
(
    'Gris du Gabon',
    'Psittacus erithacus',
    'Psittacidés',
    'gris-du-gabon',
    'Le Gris du Gabon est réputé pour ses capacités cognitives exceptionnelles et son talent remarquable pour imiter la voix humaine. Espèce sociale et affectueuse, il demande beaucoup d''interaction quotidienne.'
),
(
    'Ara ararauna',
    'Ara ararauna',
    'Psittacidés',
    'ara-ararauna',
    'L''Ara ararauna, aussi appelé Ara bleu et jaune, est l''un des plus grands perroquets du Nouveau Monde. Son plumage spectaculaire bleu cobalt et jaune vif en fait un oiseau de compagnie très prisé.'
),
(
    'Cacatoès à huppe jaune',
    'Cacatua galerita',
    'Cacatuidés',
    'cacatoes-a-huppe-jaune',
    'Le Cacatoès à huppe jaune est un oiseau exubérant et très expressif. Sa huppe jaune vif qu''il déploie en éventail traduit ses émotions. Très joueur, il apprécie les interactions et les jouets variés.'
);

-- -------------------------------------------------------------
-- Oiseaux (5) — statuts variés pour tester le filtrage vitrine
-- 3 disponible, 1 reserve, 1 vendu
-- -------------------------------------------------------------
INSERT INTO oiseau
    (id_espece, slug_fr, sexe, date_naissance, num_bague, prix_cad,
     sevre_main, statut, description_fr)
VALUES
(
    -- Oiseau 1 : Gris du Gabon, disponible
    1, 'coco-gris-du-gabon', 'male', '2023-04-15', 'CA-2023-GG-001', 2800.00,
    1, 'disponible',
    'Coco est un mâle Gris du Gabon né au printemps 2023. Sevré à la main, très câlin et curieux. Premiers mots déjà appris. Idéal pour famille expérimentée.'
),
(
    -- Oiseau 2 : Gris du Gabon, disponible
    1, 'lola-grise-du-gabon', 'femelle', '2023-06-20', 'CA-2023-GG-002', 2750.00,
    1, 'disponible',
    'Lola est une femelle Gris du Gabon vive et affectueuse. Elle adore les jeux d''intelligence et imite déjà quelques sons de la maison.'
),
(
    -- Oiseau 3 : Ara ararauna, disponible
    2, 'rio-ara-ararauna', 'male', '2022-11-10', 'CA-2022-AA-001', 3500.00,
    1, 'disponible',
    'Rio est un superbe Ara ararauna au plumage éclatant. Sociable et joueur, il a été élevé en contact humain permanent. Manteau bleu magnifique, ventre jaune vif.'
),
(
    -- Oiseau 4 : Cacatoès à huppe jaune, réservé
    3, 'soleil-cacatoes-huppe-jaune', 'inconnu', '2024-01-05', 'CA-2024-CJ-001', 1900.00,
    1, 'reserve',
    'Soleil est un jeune Cacatoès à huppe jaune très expressif. Sa huppe se déploie dès qu''il est content. Sevré à la main, habitué aux enfants.'
),
(
    -- Oiseau 5 : Ara ararauna, vendu (ne doit pas apparaître sur la vitrine)
    2, 'maya-ara-ararauna-vendu', 'femelle', '2021-08-30', 'CA-2021-AA-002', 3400.00,
    1, 'vendu',
    'Maya a trouvé sa famille adoptive. Merci à ses nouveaux propriétaires !'
);

-- -------------------------------------------------------------
-- Photos (1 principale par oiseau, chemins fictifs)
-- -------------------------------------------------------------
INSERT INTO photo
    (id_oiseau, chemin_fichier, texte_alt_fr, est_principale, ordre_affichage)
VALUES
(1, '/medias/oiseaux/exemple-1.jpg', 'Coco, mâle Gris du Gabon sevré à la main', 1, 0),
(2, '/medias/oiseaux/exemple-2.jpg', 'Lola, femelle Gris du Gabon affectueuse', 1, 0),
(3, '/medias/oiseaux/exemple-3.jpg', 'Rio, mâle Ara ararauna au plumage bleu et jaune', 1, 0),
(4, '/medias/oiseaux/exemple-4.jpg', 'Soleil, jeune Cacatoès à huppe jaune', 1, 0),
(5, '/medias/oiseaux/exemple-5.jpg', 'Maya, femelle Ara ararauna (vendue)', 1, 0);

-- -------------------------------------------------------------
-- Réservations de test (2)
-- -------------------------------------------------------------
INSERT INTO reservation
    (id_oiseau, nom_client, email_client, telephone, province,
     message, langue_demande, statut_reservation)
VALUES
(
    4,
    'Marie Tremblay',
    'marie.tremblay@exemple.ca',
    '514-555-0192',
    'Québec',
    'Bonjour, je suis très intéressée par Soleil. Pouvez-vous me contacter pour organiser une visite ?',
    'fr',
    'traitee'
),
(
    1,
    'Jean Dupuis',
    'jean.dupuis@exemple.ca',
    '438-555-0311',
    'Québec',
    'Je cherche un Gris du Gabon pour compléter ma famille. Coco m''a l''air parfait !',
    'fr',
    'nouvelle'
);

-- -------------------------------------------------------------
-- Compte admin
-- IMPORTANT : Remplacer le hash ci-dessous par celui généré par
-- generer-hash-admin.php avant d'exécuter sur le serveur.
-- Hash ci-dessous = valeur provisoire à NE PAS utiliser en production.
-- -------------------------------------------------------------
INSERT INTO admin (identifiant, mot_de_passe_hash)
VALUES (
    'admin',
    '$$2y$12$8dPkWQhsq2YmB1NHdb3OU.9ToQ79FDeDgIIegfMfLY7SjXuAgy9He'
);

-- -------------------------------------------------------------
-- Vérification rapide : doit retourner 3 lignes
-- SELECT * FROM oiseau WHERE statut = 'disponible';
-- -------------------------------------------------------------
