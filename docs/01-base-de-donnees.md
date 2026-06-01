# 01 — Base de données

> Dépend de : `00-cahier-des-charges.md`.
> Produit : le schéma SQL complet (5 tables) + un jeu de données de test.
> Aucune autre étape ne peut commencer sans cette base.

---

## Objectif

Créer le script SQL qui génère la base MySQL complète, puis un script de données de test pour voir le site tourner immédiatement.

## Fichiers à produire

```
/base-de-donnees/schema.sql          → création des tables
/base-de-donnees/donnees-test.sql    → données d'exemple
```

---

## Spécifications du schéma

Base en **UTF-8** (`utf8mb4_unicode_ci`), moteur **InnoDB** (clés étrangères).

### Table `espece`
- `id_espece` INT, clé primaire, auto-incrément.
- `nom_commun_fr` VARCHAR(120), NOT NULL.
- `nom_commun_en` VARCHAR(120), NULL (vide au départ — règle bilingue).
- `nom_scientifique` VARCHAR(120), NULL.
- `famille_fr` VARCHAR(80), NULL.
- `famille_en` VARCHAR(80), NULL.
- `slug_fr` VARCHAR(140), NOT NULL, **UNIQUE**.
- `slug_en` VARCHAR(140), NULL, UNIQUE.
- `description_fr` TEXT, NULL.
- `description_en` TEXT, NULL.

### Table `oiseau`
- `id_oiseau` INT, clé primaire, auto-incrément.
- `id_espece` INT, NOT NULL, **clé étrangère** → `espece(id_espece)`.
- `slug_fr` VARCHAR(180), NOT NULL, UNIQUE.
- `slug_en` VARCHAR(180), NULL, UNIQUE.
- `sexe` ENUM('male','femelle','inconnu'), NOT NULL, défaut 'inconnu'.
- `date_naissance` DATE, NULL.
- `num_bague` VARCHAR(60), NULL, UNIQUE.
- `prix_cad` DECIMAL(10,2), NULL.
- `sevre_main` TINYINT(1), NOT NULL, défaut 0.
- `statut` ENUM('disponible','reserve','vendu'), NOT NULL, défaut 'disponible'.
- `description_fr` TEXT, NULL.
- `description_en` TEXT, NULL.
- `date_ajout` DATETIME, NOT NULL, défaut CURRENT_TIMESTAMP.
- Index sur `statut` (filtrage vitrine fréquent) et `id_espece`.
- Clé étrangère : `ON DELETE RESTRICT` (interdire de supprimer une espèce qui a des oiseaux).

### Table `photo`
- `id_photo` INT, clé primaire, auto-incrément.
- `id_oiseau` INT, NOT NULL, clé étrangère → `oiseau(id_oiseau)` `ON DELETE CASCADE`.
- `chemin_fichier` VARCHAR(255), NOT NULL.
- `texte_alt_fr` VARCHAR(200), NULL.
- `texte_alt_en` VARCHAR(200), NULL.
- `est_principale` TINYINT(1), NOT NULL, défaut 0.
- `ordre_affichage` INT, NOT NULL, défaut 0.

### Table `reservation`
- `id_reservation` INT, clé primaire, auto-incrément.
- `id_oiseau` INT, NOT NULL, clé étrangère → `oiseau(id_oiseau)` `ON DELETE CASCADE`.
- `nom_client` VARCHAR(120), NOT NULL.
- `email_client` VARCHAR(160), NOT NULL.
- `telephone` VARCHAR(30), NULL.
- `province` VARCHAR(60), NULL.
- `message` TEXT, NULL.
- `langue_demande` ENUM('fr','en'), NOT NULL, défaut 'fr'.
- `statut_reservation` ENUM('nouvelle','traitee','annulee'), NOT NULL, défaut 'nouvelle'.
- `date_demande` DATETIME, NOT NULL, défaut CURRENT_TIMESTAMP.
- Index sur `statut_reservation`.

### Table `admin`
- `id_admin` INT, clé primaire, auto-incrément.
- `identifiant` VARCHAR(60), NOT NULL, UNIQUE.
- `mot_de_passe_hash` VARCHAR(255), NOT NULL.

---

## Jeu de données de test (`donnees-test.sql`)

- **3 espèces** : Gris du Gabon, Ara ararauna, Cacatoès à huppe jaune (avec `nom_scientifique`, `famille_fr`, `slug_fr`, `description_fr` ; colonnes `_en` laissées NULL).
- **5 oiseaux** répartis sur ces espèces, statuts variés (au moins 3 `disponible`, 1 `reserve`, 1 `vendu`) pour tester le filtrage vitrine. Renseigner sexe, date_naissance, num_bague, prix_cad, sevre_main.
- **1 photo principale** par oiseau (chemins fictifs `/medias/oiseaux/exemple-1.jpg`, etc.), avec `texte_alt_fr`.
- **2 réservations** de test (statuts `nouvelle` et `traitee`).
- **1 compte admin** : identifiant `admin`, mot de passe haché avec `password_hash()`.
  - Fournir un petit script PHP jetable `generer-hash-admin.php` qui affiche le hash d'un mot de passe choisi, à coller dans le SQL. Ne pas mettre de mot de passe en clair dans le dépôt.

---

## Critères de validation

- Les deux scripts s'exécutent sans erreur sur MySQL 8.
- Les clés étrangères et contraintes UNIQUE sont effectives.
- Une requête `SELECT` sur les oiseaux `disponible` retourne bien 3 lignes.
- Commentaires SQL en français expliquant chaque table.
