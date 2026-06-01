# 09 — Admin : authentification

> Dépend de : `00`, `02`.
> Produit : la connexion sécurisée à l'espace d'administration. Première brique du back-office.

---

## Objectif

Protéger l'espace admin par un système de connexion sécurisé (sessions, mots de passe hachés). Toutes les pages admin suivantes (10, 11, 12) en dépendent.

## Fichiers à produire

```
/admin/connexion.php           → formulaire de connexion + traitement
/admin/deconnexion.php         → fermeture de session
/admin/inc/securite.php        → garde d'accès (à inclure en tête de chaque page admin)
/admin/inc/entete-admin.php    → gabarit admin (en-tête)
/admin/inc/pied-admin.php      → gabarit admin (pied)
```

---

## `connexion.php`

- Démarre la session.
- Affiche un formulaire (identifiant + mot de passe), méthode POST.
- Au traitement :
  - Récupère l'admin par `identifiant` (requête **préparée**).
  - Vérifie le mot de passe avec `password_verify()`.
  - Si OK : enregistre l'authentification en session (ex. `$_SESSION['admin_id']`), régénère l'ID de session (`session_regenerate_id`), redirige vers le tableau de bord.
  - Si échec : message d'erreur générique (ne pas préciser si c'est l'identifiant ou le mot de passe qui est faux).
- **Limitation des tentatives** : compteur simple en session pour ralentir le forçage brut.

## `securite.php` (garde d'accès)

- À inclure **en première ligne** de chaque page admin protégée.
- Démarre la session.
- Si `$_SESSION['admin_id']` absent → redirige vers `connexion.php`.
- Fournit une fonction `verifierAdminConnecte()`.

## `deconnexion.php`

- Détruit la session proprement (`session_destroy`, suppression du cookie).
- Redirige vers la page de connexion.

## Gabarits admin

- `entete-admin.php` / `pied-admin.php` : enveloppe visuelle simple et responsive du back-office.
- Menu admin : Tableau de bord, Espèces, Oiseaux, Réservations, Déconnexion.
- Le thème clair/sombre peut être réutilisé ici aussi (variables CSS de l'étape 03).
- Libellés admin : peuvent rester en français en dur (interface privée), mais rester cohérents.

## Tableau de bord (`/admin/index.php`)
- Page d'accueil admin après connexion.
- Compteurs : oiseaux disponibles, réservés, vendus, et **demandes nouvelles** (statut `nouvelle`).

---

## Sécurité (rappels)
- Requêtes préparées partout.
- Mots de passe jamais en clair, jamais journalisés.
- Cookies de session avec attributs `HttpOnly` (et `Secure` si HTTPS).
- Le dossier `/admin/` est interdit aux moteurs (robots.txt, étape 08).

---

## Critères de validation

- Connexion réussie avec le compte test (`admin`) → accès au tableau de bord.
- Mauvais identifiants → message générique, pas d'accès.
- Accès direct à une page admin sans être connecté → redirection vers la connexion.
- Déconnexion → session détruite, retour à la connexion.
- Les compteurs du tableau de bord reflètent le jeu de test.
