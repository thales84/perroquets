-- =============================================================
-- Projet : mapleperroquets.com
-- Fichier : migration-especes-catalogue.sql (LOCAL)
-- Objet   : Catalogue d'espèces curaté (~12) pour la vitrine.
--           Espèces seulement — les oiseaux individuels sont
--           ajoutés via l'admin (données réelles : prix, bague, sexe).
-- Source   : recensement psittacofolie.com (espèces phares Québec).
-- Bilingue : colonnes _en laissées NULL (règle FR d'abord).
-- Idempotent : INSERT IGNORE sur slug_fr (UNIQUE) → relançable,
--              n'écrase pas les espèces déjà présentes.
-- =============================================================

USE perroquets;

INSERT IGNORE INTO espece
    (nom_commun_fr, nom_scientifique, famille_fr, slug_fr, description_fr)
VALUES
(
    'Gris du Gabon',
    'Psittacus erithacus',
    'Psittacidés',
    'gris-du-gabon',
    'Le Gris du Gabon est réputé pour ses capacités cognitives exceptionnelles et son talent remarquable pour imiter la voix humaine. Espèce sociale et affectueuse, il demande beaucoup d''interaction quotidienne et un environnement stimulant.'
),
(
    'Ara ararauna',
    'Ara ararauna',
    'Psittacidés',
    'ara-ararauna',
    'L''Ara ararauna, ou Ara bleu et jaune, est l''un des plus grands perroquets du Nouveau Monde. Son plumage spectaculaire bleu cobalt et jaune vif, son intelligence et son tempérament joueur en font un compagnon hors du commun pour propriétaires expérimentés.'
),
(
    'Ara macao',
    'Ara macao',
    'Psittacidés',
    'ara-macao',
    'L''Ara macao, ou Ara rouge, arbore un plumage écarlate éclatant rehaussé de jaune et de bleu. Très expressif et vocal, c''est un oiseau majestueux qui crée des liens forts avec sa famille humaine.'
),
(
    'Ara hyacinthe',
    'Anodorhynchus hyacinthinus',
    'Psittacidés',
    'ara-hyacinthe',
    'L''Ara hyacinthe est le plus grand perroquet volant du monde, reconnaissable à son bleu cobalt profond et au contour jaune vif de ses yeux et de son bec. Espèce rare et protégée, douce et placide, réservée aux éleveurs et propriétaires aguerris.'
),
(
    'Cacatoès alba',
    'Cacatua alba',
    'Cacatuidés',
    'cacatoes-alba',
    'Le Cacatoès alba, ou Cacatoès à huppe blanche, déploie une magnifique huppe en éventail lorsqu''il est excité. Extrêmement affectueux et démonstratif, il réclame beaucoup d''attention et convient aux foyers très disponibles.'
),
(
    'Cacatoès rosalbin',
    'Eolophus roseicapilla',
    'Cacatuidés',
    'cacatoes-rosalbin',
    'Le Cacatoès rosalbin séduit par son plumage rose tendre et gris perle. Vif, joueur et robuste, c''est l''un des cacatoès les plus équilibrés, apprécié pour son caractère enjoué et sa sociabilité.'
),
(
    'Cacatoès à huppe jaune',
    'Cacatua galerita',
    'Cacatuidés',
    'cacatoes-a-huppe-jaune',
    'Le Cacatoès à huppe jaune est un oiseau exubérant et très expressif. Sa huppe jaune vif déployée en éventail traduit ses émotions. Très joueur, il apprécie les interactions et une grande variété de jouets.'
),
(
    'Cacatoès de Goffin',
    'Cacatua goffiniana',
    'Cacatuidés',
    'cacatoes-de-goffin',
    'Le Cacatoès de Goffin est le plus petit des cacatoès à huppe blanche. Curieux, ingénieux et réputé pour ses capacités à résoudre des problèmes, c''est un compagnon vif et attachant à l''énergie débordante.'
),
(
    'Éclectus',
    'Eclectus roratus',
    'Psittacidés',
    'eclectus',
    'L''Éclectus présente un dimorphisme sexuel unique : le mâle est vert émeraude, la femelle rouge et violette. Calme, observateur et doux, il apprécie une alimentation riche en fruits et légumes frais.'
),
(
    'Amazone à front bleu',
    'Amazona aestiva',
    'Psittacidés',
    'amazone-a-front-bleu',
    'L''Amazone à front bleu, au plumage vert rehaussé de bleu et de jaune au visage, est réputée pour son tempérament jovial et ses talents d''imitation. Robuste et longévive, elle s''épanouit dans une famille active.'
),
(
    'Youyou du Sénégal',
    'Poicephalus senegalus',
    'Psittacidés',
    'youyou-du-senegal',
    'Le Youyou du Sénégal est un perroquet de taille moyenne, calme et discret, idéal pour la vie en appartement. Attaché à son propriétaire, il offre une belle alternative aux grandes espèces plus bruyantes.'
),
(
    'Calopsitte',
    'Nymphicus hollandicus',
    'Cacatuidés',
    'calopsitte',
    'La Calopsitte élégante, petit cousin des cacatoès, est l''un des oiseaux de compagnie les plus populaires. Douce, sociable et facile à apprivoiser, elle siffle des mélodies et convient parfaitement aux familles débutantes.'
);
