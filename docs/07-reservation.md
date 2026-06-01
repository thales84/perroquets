# 07 — Réservation

> Dépend de : `00`, `02`, `03`, `04`, `06`.
> Produit : le formulaire de demande de réservation, sa validation, son enregistrement et la confirmation.

---

## Objectif

Permettre au visiteur d'envoyer une demande de réservation sur un oiseau précis. Aucune transaction en ligne : on enregistre la demande, le propriétaire traite hors ligne.

## Fichiers à produire

```
/reservation.php                  → formulaire + traitement (route /fr/reservation/{slug})
/modeles/reservation-modele.php   → enregistrement en base
/confirmation.php                 → page d'accusé de réception
```

---

## `reservation.php`

- Récupère l'oiseau via son slug (réutilise `recupererOiseauParSlug`).
- Si l'oiseau n'est pas `disponible` → bloquer la réservation avec message clair.
- Affiche un rappel de l'oiseau concerné (photo + nom) en haut du formulaire.
- **Formulaire** (méthode POST, libellés via `t()`) :
  - Nom (obligatoire).
  - Courriel (obligatoire, format valide).
  - Téléphone (format nord-américain `+1 (XXX) XXX-XXXX`).
  - Province canadienne (liste déroulante des provinces/territoires).
  - Message (optionnel).
  - Champ caché : id de l'oiseau + `langue_demande` = langue active.
- **Protection anti-spam** : champ « pot de miel » (honeypot) invisible ; si rempli → rejet silencieux.

## Validation (côté serveur, prioritaire)

- Nom et courriel non vides ; courriel valide (`filter_var`).
- Téléphone : format nord-américain accepté (validation souple).
- Toutes les entrées **assainies** avant insertion.
- En cas d'erreur : réafficher le formulaire avec les messages et **conserver les valeurs saisies**.
- Validation **JavaScript** en complément (confort), mais jamais en remplacement du serveur.

## `reservation-modele.php`

- `enregistrerReservation($donnees)` : insertion **préparée** dans `reservation`, statut `nouvelle`.

## Après enregistrement

- Rediriger vers `/fr/confirmation` (motif POST-redirect-GET pour éviter la double soumission).
- `confirmation.php` : message d'accusé de réception via `t('resa_confirmation')`, rappel des prochaines étapes (le propriétaire recontacte le client).

## (Optionnel, à signaler) Notification courriel
- Prévoir un emplacement commenté pour l'envoi d'un courriel au propriétaire à chaque nouvelle demande (`mail()` ou service externe), à activer selon l'hébergement.

---

## Critères de validation

- Une demande valide s'enregistre en base avec statut `nouvelle` et apparaît côté admin (étape 12).
- Un courriel invalide ou un nom vide bloque l'envoi et réaffiche le formulaire rempli.
- Le honeypot rejette les soumissions automatisées.
- Impossible de réserver un oiseau non disponible.
- La confirmation s'affiche sans risque de double soumission au rafraîchissement.
