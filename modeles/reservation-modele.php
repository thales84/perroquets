<?php
require_once __DIR__ . '/../configuration/connexion.php';

/**
 * Enregistre une demande de réservation en base (statut = 'nouvelle').
 *
 * @param  array $donnees Champs validés : id_oiseau, nom_client, email_client,
 *                        telephone, province, message, langue_demande
 * @return bool           true si insertion réussie
 */
function enregistrerReservation(array $donnees): bool
{
    $pdo = obtenirConnexion();

    $req = $pdo->prepare("
        INSERT INTO reservation
            (id_oiseau, nom_client, email_client, telephone, province,
             message, langue_demande, statut_reservation)
        VALUES
            (:id_oiseau, :nom_client, :email_client, :telephone, :province,
             :message, :langue_demande, 'nouvelle')
    ");

    return $req->execute([
        ':id_oiseau'      => (int) $donnees['id_oiseau'],
        ':nom_client'     => mb_substr(trim($donnees['nom_client']), 0, 120),
        ':email_client'   => mb_substr(trim($donnees['email_client']), 0, 160),
        ':telephone'      => mb_substr(trim($donnees['telephone'] ?? ''), 0, 30),
        ':province'       => mb_substr(trim($donnees['province'] ?? ''), 0, 60),
        ':message'        => mb_substr(trim($donnees['message'] ?? ''), 0, 5000),
        ':langue_demande' => in_array($donnees['langue_demande'] ?? '', ['fr', 'en'], true)
                             ? $donnees['langue_demande'] : 'fr',
    ]);
}

// ============================================================
// Fonctions de lecture/administration — utilisées par l'admin (étape 12)
// ============================================================

/**
 * Liste toutes les réservations avec jointure oiseau et espèce.
 * Filtre optionnel par statut_reservation.
 */
function listerReservations(string $filtreStatut = ''): array
{
    $pdo        = obtenirConnexion();
    $conditions = [];
    $parametres = [];

    if ($filtreStatut !== '' && in_array($filtreStatut, ['nouvelle','traitee','annulee'], true)) {
        $conditions[] = 'r.statut_reservation = :statut';
        $parametres[':statut'] = $filtreStatut;
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $req = $pdo->prepare("
        SELECT r.*,
               o.slug_fr       AS oiseau_slug,
               o.statut        AS oiseau_statut,
               e.nom_commun_fr AS espece_nom
        FROM reservation r
        INNER JOIN oiseau  o ON o.id_oiseau  = r.id_oiseau
        INNER JOIN espece  e ON e.id_espece  = o.id_espece
        {$where}
        ORDER BY r.date_demande DESC
    ");
    $req->execute($parametres);
    return $req->fetchAll();
}

/**
 * Retourne une réservation complète par son ID.
 */
function recupererReservation(int $id): ?array
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        SELECT r.*,
               o.id_oiseau     AS oiseau_id,
               o.slug_fr       AS oiseau_slug,
               o.sexe          AS oiseau_sexe,
               o.statut        AS oiseau_statut,
               o.prix_cad      AS oiseau_prix,
               e.nom_commun_fr AS espece_nom,
               p.chemin_fichier AS photo_chemin
        FROM reservation r
        INNER JOIN oiseau o ON o.id_oiseau = r.id_oiseau
        INNER JOIN espece e ON e.id_espece = o.id_espece
        LEFT JOIN  photo  p ON p.id_oiseau = o.id_oiseau AND p.est_principale = 1
        WHERE r.id_reservation = :id
        LIMIT 1
    ");
    $req->execute([':id' => $id]);
    $r = $req->fetch();
    return $r !== false ? $r : null;
}

/**
 * Change le statut d'une réservation.
 */
function changerStatutReservation(int $id, string $statut): bool
{
    if (!in_array($statut, ['nouvelle','traitee','annulee'], true)) return false;
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("UPDATE reservation SET statut_reservation = :s WHERE id_reservation = :id");
    return $req->execute([':s' => $statut, ':id' => $id]);
}

/**
 * Compte les demandes en statut 'nouvelle' (tableau de bord).
 */
function compterNouvellesReservations(): int
{
    $pdo = obtenirConnexion();
    $req = $pdo->query("SELECT COUNT(*) FROM reservation WHERE statut_reservation = 'nouvelle'");
    return (int) $req->fetchColumn();
}
