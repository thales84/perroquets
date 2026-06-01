# 04 — Système multilingue

> Dépend de : `00`, `02`.
> Produit : la fonction de traduction et les fichiers de langue.
> **Étape clé** : toutes les pages publiques en dépendent. À construire avant les pages.

---

## Objectif

Mettre en place le mécanisme qui garantit la règle « bilingue par conception, français à l'exécution » : aucun libellé d'interface en dur, tout passe par une fonction de traduction. Activer l'anglais plus tard = remplir un fichier, sans toucher au code.

## Fichiers à produire

```
/langues/fr.php                → libellés français (rempli)
/langues/en.php                → libellés anglais (squelette, mêmes clés, valeurs vides)
/configuration/traduction.php  → moteur de traduction
```

---

## `fr.php` et `en.php`

- Chaque fichier retourne un **tableau associatif** clé → libellé.
- Clés communes aux deux fichiers (mêmes clés, c'est essentiel).
- `fr.php` : valeurs **remplies** en français.
- `en.php` : **mêmes clés**, valeurs **vides** (`''`) — prêtes à remplir plus tard.

Exemples de clés à prévoir (liste non exhaustive, à compléter au fil des pages) :
- Navigation : `nav_accueil`, `nav_oiseaux`, `nav_contact`.
- Liste : `liste_titre`, `filtre_espece`, `filtre_sexe`, `filtre_eam`, `aucun_resultat`.
- Fiche : `fiche_sexe`, `fiche_age`, `fiche_bague`, `fiche_prix`, `fiche_eam_oui`, `fiche_reserver`.
- Statuts : `statut_disponible`, `statut_reserve`, `statut_vendu`.
- Réservation : `resa_titre`, `resa_nom`, `resa_email`, `resa_telephone`, `resa_province`, `resa_message`, `resa_envoyer`, `resa_confirmation`.
- Communs : `prix_sur_demande`, `bouton_retour`, etc.

## `traduction.php`

- Détecte la langue active depuis l'URL (`/fr/` → `fr`). Défaut : `fr`.
- Charge le tableau de la langue active (`fr.php`).
- Fonction `t($cle)` : retourne le libellé correspondant.
  - Si la clé est absente ou vide (cas anglais non rempli), **retombe sur le français** (mécanisme de repli), pour ne jamais afficher de vide à l'écran.
- Fonction `langueActive()` : retourne le code de langue courant.

**Règle absolue** : à partir de maintenant, tout texte d'interface s'affiche via `t('cle')`, jamais écrit en dur dans le HTML.

> Distinction importante : `t()` gère les **libellés d'interface** (boutons, menus, étiquettes). Les **contenus métier** (descriptions d'oiseaux, noms d'espèces) viennent de la base via les colonnes `_fr`/`_en` — gérés directement dans les pages, pas par `t()`.

---

## Critères de validation

- `t('nav_oiseaux')` retourne « Oiseaux ».
- Les fichiers `fr.php` et `en.php` ont exactement les mêmes clés.
- Vider une valeur dans `fr.php` ou simuler la langue `en` déclenche le repli sans page cassée.
- Aucune page ne contient de libellé d'interface écrit en dur.
