# 05 — Page publique : liste des oiseaux

> Dépend de : `00`, `02`, `03`, `04`.
> Produit : la page vitrine listant les oiseaux disponibles, avec filtres.

---

## Objectif

Afficher tous les oiseaux **disponibles** sous forme de grille responsive, avec filtres (espèce, sexe, sevré main). C'est la page centrale de la vitrine.

## Fichiers à produire

```
/oiseaux.php                   → page liste (route /fr/oiseaux)
/modeles/oiseau-modele.php     → fonctions d'accès aux données oiseaux (réutilisable)
```

---

## `oiseau-modele.php` (couche d'accès aux données)

Fonctions PDO réutilisables, requêtes **préparées** :
- `recupererOiseauxDisponibles($filtres)` : retourne les oiseaux `statut = 'disponible'`, avec jointure sur `espece` et sur la `photo` principale. Applique les filtres optionnels (id_espece, sexe, sevre_main).
- `recupererListeEspeces()` : pour alimenter le filtre par espèce.

> Ces fonctions seront aussi utilisées par la fiche (étape 06) et l'admin. Les centraliser ici.

## `oiseaux.php`

- Définit `$titrePage` et `$descriptionPage` (SEO dynamique) — ex. « Perroquets disponibles à la vente au Canada ».
- Inclut `entete.php`.
- Un seul `<h1>` (ex. « Nos perroquets disponibles »).
- **Barre de filtres** (formulaire GET, libellés via `t()`) :
  - Espèce (liste déroulante).
  - Sexe (male/femelle/inconnu).
  - Sevré main (oui/non) — case ou bouton.
- **Grille d'oiseaux** (CSS Grid responsive) : chaque carte affiche :
  - Photo principale (`loading="lazy"`, `alt` = `texte_alt_fr`).
  - Nom commun de l'espèce (`nom_commun_fr`).
  - Sexe, badge EAM si `sevre_main`.
  - Prix formaté (`formaterPrixCad`) ou « Prix sur demande ».
  - Lien vers la fiche : `/fr/oiseau/{slug_fr}`.
- Si aucun résultat : message via `t('aucun_resultat')`.
- Inclut `pied.php`.

## Points d'attention
- Toutes les données affichées passent par `echapper()`.
- Seuls les oiseaux `disponible` apparaissent (jamais reserve/vendu) — c'est la règle « individu unique ».
- Les filtres se combinent (espèce + sexe + EAM en même temps).
- Performance : une seule requête avec jointures, pas de requête dans une boucle.

---

## Critères de validation

- La page affiche exactement les 3 oiseaux disponibles du jeu de test.
- Filtrer par espèce/sexe/EAM réduit correctement la liste.
- La grille s'adapte : 1 colonne en mobile, plusieurs en bureau.
- Les liens mènent vers les bonnes fiches.
- Titre et description SEO présents et dynamiques.
