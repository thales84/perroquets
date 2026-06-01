# 12 — Admin : gestion des réservations

> Dépend de : `00`, `02`, `07`, `09`.
> Produit : la consultation et le traitement des demandes de réservation. Dernière étape.

---

## Objectif

Permettre au propriétaire de voir les demandes de réservation reçues, de les traiter et de gérer le lien avec le statut de l'oiseau concerné. C'est ce qui clôt la boucle vitrine → demande → traitement.

## Fichiers à produire

```
/admin/reservations/liste.php       → liste des demandes
/admin/reservations/detail.php      → détail d'une demande + actions
```

Réutilise `reservation-modele.php` (étape 07), à compléter. Inclut `securite.php`.

---

## Fonctions à ajouter à `reservation-modele.php`
- `listerReservations($filtreStatut)` : avec jointure sur l'oiseau et l'espèce concernés.
- `recupererReservation($id)`.
- `changerStatutReservation($id, $statut)` : `nouvelle` → `traitee` ou `annulee`.
- `compterNouvellesReservations()` : déjà utilisée par le tableau de bord (étape 09).

## Liste des réservations
- Tableau trié par date (plus récentes d'abord) : date, nom client, oiseau concerné (lien vers la fiche), statut (badge), action « voir ».
- **Filtre par statut** : nouvelle / traitée / annulée.
- Les demandes **nouvelles** mises en évidence visuellement.

## Détail d'une demande
- Toutes les infos client : nom, courriel (lien `mailto:`), téléphone, province, message, langue de la demande, date.
- Rappel de l'oiseau concerné (photo, nom, statut actuel).
- **Actions** :
  - Marquer « traitée » / « annulée ».
  - **Lien rapide** vers le statut de l'oiseau : depuis ici, pouvoir passer l'oiseau en « réservé » ou « vendu » (cohérence avec l'étape 11).

## Règle métier — articulation des deux statuts
Bien distinguer :
- `statut_reservation` (état de la **demande** : nouvelle/traitée/annulée).
- `statut` de l'**oiseau** (disponible/réservé/vendu).

Traiter une demande **ne change pas automatiquement** le statut de l'oiseau : c'est le propriétaire qui décide (un même oiseau peut avoir reçu plusieurs demandes). Lui fournir les deux leviers côté détail, mais garder le contrôle manuel.

## Sécurité
- Requêtes préparées, échappement de toutes les données client (saisies par le public).
- Jeton CSRF sur les actions de changement de statut.
- Authentification requise (securite.php).

---

## Critères de validation

- Les 2 réservations de test apparaissent, triées par date.
- Le filtre par statut fonctionne.
- Marquer une demande « traitée » met à jour son badge et le compteur du tableau de bord.
- Depuis le détail, passer l'oiseau en « réservé » le retire de la vitrine publique.
- Les données client s'affichent échappées (aucune injection possible via le message).
