# 11 — Admin : gestion des oiseaux

> Dépend de : `00`, `02`, `09`, `10`.
> Produit : le CRUD des oiseaux, l'upload et la gestion des photos, et le changement de statut.
> C'est l'étape admin la plus riche.

---

## Objectif

Permettre au propriétaire de gérer ses oiseaux : ajouter un oiseau, l'associer à une espèce, téléverser ses photos, renseigner ses caractéristiques, et changer son statut (disponible / réservé / vendu).

## Fichiers à produire

```
/admin/oiseaux/liste.php       → liste des oiseaux
/admin/oiseaux/ajouter.php     → création
/admin/oiseaux/modifier.php    → édition + gestion photos
/admin/oiseaux/supprimer.php   → suppression
/admin/oiseaux/photos.php      → traitement upload / suppression photos
```

Réutilise `oiseau-modele.php` (étape 05), à compléter avec les fonctions d'écriture. Inclut `securite.php`.

---

## Fonctions à ajouter à `oiseau-modele.php`
- `listerTousLesOiseaux($filtres)` : pour l'admin, **tous statuts** (pas seulement disponibles).
- `creerOiseau($donnees)` : génère `slug_fr` (espèce + sexe + année + n° bague pour l'unicité).
- `modifierOiseau($id, $donnees)`.
- `changerStatutOiseau($id, $statut)`.
- `supprimerOiseau($id)` (les photos liées sont supprimées en cascade côté base ; supprimer aussi les fichiers physiques).

## Formulaire d'oiseau
Champs :
- Espèce (liste déroulante alimentée par `listerEspeces()`) — obligatoire.
- Sexe (male / femelle / inconnu).
- Date de naissance.
- Numéro de bague.
- Prix CAD.
- Sevré main (case à cocher EAM).
- Statut (disponible / réservé / vendu).
- `description_fr` (zone de texte).
- Bloc **anglais repliable** (`description_en`) masqué par défaut.

## Gestion des photos (`photos.php`)
- **Upload multiple** d'images pour un oiseau.
- **Validation** : types autorisés (jpg, png, webp), taille max, renommage sécurisé du fichier (jamais le nom d'origine brut).
- Stockage dans `/medias/oiseaux/`.
- Pour chaque photo : champ `texte_alt_fr` (important SEO/accessibilité).
- Désigner la **photo principale** (une seule par oiseau — décocher les autres automatiquement).
- Réordonner les photos (`ordre_affichage`).
- Supprimer une photo (fichier physique + ligne base).

## Liste des oiseaux (admin)
- Tableau : miniature, espèce, sexe, prix, statut (badge coloré), date d'ajout, actions.
- Filtres par statut et par espèce.
- **Changement rapide de statut** depuis la liste (ex. passer « disponible » → « réservé »).

## Sécurité
- Requêtes préparées, échappement.
- Validation stricte des fichiers téléversés (ne jamais faire confiance à l'extension seule — vérifier le type réel).
- Jeton CSRF sur les formulaires.
- Confirmation avant suppression.

---

## Critères de validation

- Créer un oiseau, l'associer à une espèce, lui ajouter 2 photos dont une principale.
- L'oiseau apparaît immédiatement dans la vitrine publique s'il est « disponible ».
- Passer son statut à « vendu » le retire de la vitrine.
- Le slug généré est unique et lisible.
- Upload : un fichier non-image est refusé proprement.
- Supprimer un oiseau supprime aussi ses fichiers photos.
