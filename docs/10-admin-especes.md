# 10 — Admin : gestion des espèces

> Dépend de : `00`, `02`, `09`.
> Produit : le CRUD complet des espèces dans le back-office.

---

## Objectif

Permettre au propriétaire de créer, lister, modifier et supprimer les espèces de perroquets. Les espèces sont le préalable aux oiseaux (un oiseau appartient à une espèce).

## Fichiers à produire

```
/admin/especes/liste.php       → liste des espèces
/admin/especes/ajouter.php     → création
/admin/especes/modifier.php    → édition
/admin/especes/supprimer.php   → suppression
/modeles/espece-modele.php     → accès aux données espèces
```

Chaque page admin inclut `securite.php` en première ligne.

---

## `espece-modele.php`

Fonctions PDO préparées :
- `listerEspeces()` : toutes les espèces (avec le **nombre d'oiseaux** rattachés, pour info).
- `recupererEspece($id)`.
- `creerEspece($donnees)` : génère `slug_fr` automatiquement via `genererSlug()`.
- `modifierEspece($id, $donnees)`.
- `supprimerEspece($id)`.

## Formulaire d'espèce (ajouter / modifier)

Champs :
- `nom_commun_fr` (obligatoire).
- `nom_scientifique`.
- `famille_fr`.
- `description_fr` (zone de texte).
- **Champs anglais** (`nom_commun_en`, `famille_en`, `description_en`) : présents dans le formulaire mais **masqués/repliés** par défaut (règle bilingue — prêts mais non utilisés au départ). Prévoir un bloc « Version anglaise (optionnel) » repliable.
- `slug_fr` : généré automatiquement depuis `nom_commun_fr`, modifiable manuellement, vérifié unique.

## Liste des espèces
- Tableau : nom commun, nom scientifique, famille, nombre d'oiseaux, actions (modifier / supprimer).

## Suppression — règle métier importante
- **Interdire** la suppression d'une espèce qui contient des oiseaux (cohérence avec `ON DELETE RESTRICT`).
- Afficher un message clair : « Impossible de supprimer : des oiseaux sont rattachés à cette espèce. »
- Demander une **confirmation** avant toute suppression.

## Sécurité
- Requêtes préparées, échappement à l'affichage.
- Vérification de l'authentification (securite.php).
- Protection CSRF : jeton dans les formulaires de modification/suppression.

---

## Critères de validation

- Créer une espèce génère un slug correct et l'ajoute à la liste.
- Modifier une espèce met à jour les données.
- Supprimer une espèce vide fonctionne ; supprimer une espèce avec oiseaux est bloqué avec message.
- Les champs anglais sont présents mais masqués par défaut.
