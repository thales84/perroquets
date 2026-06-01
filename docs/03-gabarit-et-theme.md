# 03 — Gabarit et thème

> Dépend de : `00`, `02`.
> Produit : la structure HTML partagée, le CSS de base, le thème clair/sombre et le responsive mobile-first.
> C'est le squelette visuel réutilisé par toutes les pages publiques.

---

## Objectif

Construire l'enveloppe commune (en-tête, navigation, pied de page), le système de thème clair/sombre adaptatif, et les fondations CSS responsive mobile-first.

## Fichiers à produire

```
/gabarits/entete.php           → début de page : <head>, <header>, <nav>
/gabarits/pied.php             → fin de page : <footer>, scripts
/ressources/css/style.css      → styles globaux + variables de thème
/ressources/js/theme.js        → bascule clair/sombre
/ressources/js/menu-mobile.js  → menu hamburger
```

---

## `entete.php`

- `<!DOCTYPE html>`, `<html lang="fr-CA">`.
- `<head>` :
  - Charset UTF-8, viewport responsive.
  - **Balises méta dynamiques** : variables `$titrePage` et `$descriptionPage` passées par chaque page (jamais en dur).
  - **Anti-flash thème** : petit script inline AVANT le CSS, qui lit `localStorage` (ou la préférence système) et pose `data-theme` sur `<html>` immédiatement.
  - Lien vers `style.css`.
  - Emplacement prévu (commenté) pour `hreflang` fr-CA (et en-CA en commentaire).
- `<header>` avec logo + `<nav>` :
  - Navigation : Accueil, Oiseaux, (espèces), Contact.
  - **Bouton de bascule de thème** (icône soleil/lune).
  - **Bouton hamburger** visible en mobile seulement.
  - Emplacement prévu (commenté) pour le futur sélecteur de langue FR/EN.
- Ouverture de `<main>`.

## `pied.php`

- Fermeture de `<main>`.
- `<footer>` : mentions, coordonnées canadiennes, année dynamique.
- Inclusion des scripts `theme.js` et `menu-mobile.js`.

## `style.css` — thème par variables

Sous `:root`, définir les variables du **thème clair** :
- `--couleur-fond`, `--couleur-texte`, `--couleur-primaire`, `--couleur-secondaire`, `--couleur-bordure`, `--couleur-carte`, etc.

Sous `[data-theme="sombre"]`, redéfinir ces mêmes variables pour le **thème sombre**.

Le CSS n'utilise **que** ces variables (jamais de couleur en dur), pour que la bascule soit instantanée.

## `style.css` — responsive mobile-first

- Styles de base pensés pour **petit écran** d'abord.
- Points de rupture par `min-width` (ex. 600px tablette, 992px bureau).
- Grilles fluides (CSS Grid / Flexbox) pour la liste d'oiseaux.
- Images fluides (`max-width: 100%`).
- Navigation : menu vertical masqué en mobile, déployé par le hamburger ; barre horizontale en bureau.

## `theme.js`

- Lit le thème courant, bascule clair ↔ sombre au clic.
- Sauvegarde le choix dans `localStorage`.
- Le choix manuel prime sur `prefers-color-scheme`.
- Met à jour l'icône du bouton.

## `menu-mobile.js`

- Ouvre/ferme le menu de navigation au clic sur le hamburger.
- Accessible (attributs `aria-expanded`).

---

## Critères de validation

- Une page de démonstration utilise `entete.php` + `pied.php` et s'affiche correctement.
- La bascule clair/sombre fonctionne, sans flash au rechargement, et le choix persiste.
- Sur mobile, le menu hamburger fonctionne ; en bureau, la barre horizontale s'affiche.
- Le titre et la description de la page sont bien injectés dynamiquement.
- HTML sémantique valide (`header`/`nav`/`main`/`footer`), un seul `<h1>` par page.
