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
            (client_id, id_oiseau, nom_client, email_client, telephone, province,
             message, langue_demande, statut_reservation)
        VALUES
            (:client_id, :id_oiseau, :nom_client, :email_client, :telephone, :province,
             :message, :langue_demande, 'nouvelle')
    ");

    return $req->execute([
        ':client_id'      => isset($donnees['client_id']) ? (int) $donnees['client_id'] : null,
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

/**
 * Met à jour les infos de suivi d'une réservation.
 */
function mettreAJourSuivi(int $id, array $d): bool
{
    $statutsValides = ['en_preparation', 'expedie', 'livre'];
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        UPDATE reservation SET
            statut_livraison = :statut,
            numero_suivi     = :numero,
            transporteur     = :transport,
            date_expedition  = :date_exp
        WHERE id_reservation = :id
    ");
    return $req->execute([
        ':statut'    => in_array($d['statut_livraison'] ?? '', $statutsValides, true)
                        ? $d['statut_livraison'] : null,
        ':numero'    => mb_substr(trim($d['numero_suivi']  ?? ''), 0, 100) ?: null,
        ':transport' => mb_substr(trim($d['transporteur']  ?? ''), 0, 80)  ?: null,
        ':date_exp'  => !empty($d['date_expedition']) ? $d['date_expedition'] : null,
        ':id'        => $id,
    ]);
}

/**
 * Génère un code de suivi aléatoire NON devinable, unique en base.
 * Format : MP-XXXX-XXXX (alphabet sans caractères ambigus 0/O/1/I/L/U).
 * Utilise random_int() (CSPRNG) — résiste à l'énumération/infiltration.
 */
function genererCodeSuiviUnique(): string
{
    $pdo = obtenirConnexion();
    // Alphabet Crockford-like, sans 0 O 1 I L U pour éviter les confusions
    $alphabet = '23456789ABCDEFGHJKMNPQRSTVWXYZ';
    $max      = strlen($alphabet) - 1;

    do {
        $brut = '';
        for ($i = 0; $i < 8; $i++) {
            $brut .= $alphabet[random_int(0, $max)];
        }
        $code = 'MP-' . substr($brut, 0, 4) . '-' . substr($brut, 4, 4);

        // Vérifier l'unicité
        $req = $pdo->prepare("SELECT 1 FROM reservation WHERE code_suivi = :c LIMIT 1");
        $req->execute([':c' => $code]);
        $existe = (bool) $req->fetchColumn();
    } while ($existe);

    return $code;
}

/**
 * Assigne (ou régénère) le code de suivi d'une réservation.
 * Retourne le code généré.
 */
function assignerCodeSuivi(int $id): string
{
    $code = genererCodeSuiviUnique();
    $pdo  = obtenirConnexion();
    $req  = $pdo->prepare("UPDATE reservation SET code_suivi = :c WHERE id_reservation = :id");
    $req->execute([':c' => $code, ':id' => $id]);
    return $code;
}

/**
 * Retourne le code de suivi existant d'une réservation (ou null).
 */
function recupererCodeSuivi(int $id): ?string
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("SELECT code_suivi FROM reservation WHERE id_reservation = :id LIMIT 1");
    $req->execute([':id' => $id]);
    $code = $req->fetchColumn();
    return $code ?: null;
}

/**
 * Retourne les données de suivi public PAR CODE ALÉATOIRE (anti-énumération).
 * Aucune info personnelle. Retourne null si annulée ou code inconnu.
 */
function recupererSuiviParCode(string $code): ?array
{
    // Normaliser : majuscules, supprimer espaces
    $code = strtoupper(trim($code));

    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        SELECT r.id_reservation, r.statut_reservation, r.statut_livraison,
               r.code_suivi, r.numero_suivi, r.transporteur, r.date_expedition,
               e.nom_commun_fr AS espece_nom
        FROM reservation r
        INNER JOIN oiseau o ON o.id_oiseau = r.id_oiseau
        INNER JOIN espece e ON e.id_espece = o.id_espece
        WHERE r.code_suivi = :code
          AND r.statut_reservation != 'annulee'
        LIMIT 1
    ");
    $req->execute([':code' => $code]);
    $r = $req->fetch();
    return $r ?: null;
}

/**
 * Référence interne lisible (affichage admin / dossier). NON utilisée pour
 * la recherche publique — celle-ci passe par le code aléatoire.
 */
function genererReference(int $id): string
{
    return 'MP-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
}
