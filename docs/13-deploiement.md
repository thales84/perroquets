# Étape 13 — Déploiement en production & pipeline CI/CD

Production : **hébergement mutualisé cPanel/FTP**, domaine `mapleperroquets.com`.
Déploiement **automatique par GitHub Actions** sur `push` vers `main`.

---

## 1. Vue d'ensemble du pipeline

Fichier : [.github/workflows/ci-cd.yml](../.github/workflows/ci-cd.yml)

| Job | Déclencheur | Rôle |
|-----|-------------|------|
| `lint` | push + pull request | `php -l` sur tous les `.php` ; refuse un `config.php` committé |
| `deploy` | push sur `main` uniquement | Envoi FTPS vers le dossier web, après succès de `lint` |

Le `deploy` dépend de `lint` (`needs: lint`) : pas de mise en ligne si la
syntaxe PHP est cassée.

---

## 2. Secrets GitHub à créer

Dépôt GitHub → **Settings → Secrets and variables → Actions → New repository secret** :

| Secret | Exemple | Description |
|--------|---------|-------------|
| `FTP_SERVER` | `ftp.mapleperroquets.com` | Hôte FTP/FTPS (fourni par l'hébergeur) |
| `FTP_USERNAME` | `user@mapleperroquets.com` | Identifiant FTP cPanel |
| `FTP_PASSWORD` | `••••••••` | Mot de passe FTP |
| `FTP_SERVER_DIR` | `public_html/` | Dossier racine web (avec `/` final) |

> Protocole : `ftps` (FTP chiffré) par défaut. Si l'hébergeur ne le supporte
> pas, remplacer `protocol: ftps` par `protocol: ftp` dans le workflow.

---

## 3. Préparation du serveur (une seule fois)

1. **Base de données** : créer la BD + l'utilisateur dans cPanel (MySQL Databases),
   puis importer via phpMyAdmin, dans l'ordre :
   - `base-de-donnees/schema.sql`
   - `base-de-donnees/migration-client.sql`
   - `base-de-donnees/migration-suivi.sql`
   - `base-de-donnees/migration-code-suivi.sql`
   - (optionnel) `base-de-donnees/donnees-test.sql`

   > Les fichiers `base-de-donnees/**` sont **exclus du déploiement FTP** :
   > on les exécute manuellement, ils ne sont pas servis sur le web.

2. **Configuration** : créer `configuration/config.php` directement sur le
   serveur (copie de `config.exemple.php`) avec les identifiants de prod :
   ```php
   define('BD_HOTE',         'localhost');
   define('BD_NOM',          'cpanel_perroquets');
   define('BD_UTILISATEUR',  'cpanel_user');
   define('BD_MOT_DE_PASSE', 'motdepasse_reel');
   define('URL_SITE',        'https://mapleperroquets.com');
   ```
   Ce fichier est **gitignored ET exclu du déploiement** : il ne sera jamais
   écrasé par une mise en ligne.

3. **Dossier médias** : créer `medias/oiseaux/` (droits d'écriture) — vide dans
   le dépôt, il accueille les photos téléversées via l'admin.

4. **HTTPS** : activer le certificat SSL (Let's Encrypt cPanel). Le `.htaccess`
   force déjà HTTPS sur le domaine de production.

---

## 4. Comportement du `.htaccess` (local ↔ prod)

Le [.htaccess](../.htaccess) est **agnostique** :
- aucune `RewriteBase` (substitutions relatives) → fonctionne à la racine en
  prod comme dans `/perroquets/` en local XAMPP ;
- le forçage HTTPS ne s'applique qu'au domaine `mapleperroquets.com`, donc le
  développement local en `http://localhost` reste intact.

---

## 5. Cycle de déploiement courant

```
git push origin main
        │
        ▼
   GitHub Actions
   ├── lint  (php -l)        ── échec ⇒ stop, rien n'est mis en ligne
   └── deploy (FTPS)         ── synchronise les fichiers modifiés
```

Le déploiement est **incrémental** (FTP-Deploy-Action garde un état de
synchronisation `.ftp-deploy-sync-state.json` sur le serveur) : seuls les
fichiers changés sont renvoyés.

---

## 6. Fichiers exclus du déploiement

`.git*`, `.github/`, `docs/`, `base-de-donnees/`, tous les `*.md`, `CLAUDE.md`,
`configuration/config.php`, et les scripts de dépannage admin
(`fix-admin-hash.php`, `debloquer-admin.php`, `generer-hash-admin.php`).

---

## 7. Critères de validation

- [ ] Les 4 secrets FTP existent dans GitHub Actions.
- [ ] `config.php` créé sur le serveur, BD importée.
- [ ] Un `push` sur `main` déclenche `lint` puis `deploy` (onglet Actions).
- [ ] `https://mapleperroquets.com` répond, HTTPS forcé, accueil `/fr/` OK.
- [ ] `config.php` du serveur intact après un déploiement.
- [ ] Le développement local (`http://localhost/perroquets`) fonctionne toujours.
