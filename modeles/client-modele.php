<?php
require_once __DIR__ . '/../configuration/connexion.php';

/**
 * Inscrit un nouveau client. Retourne l'id_client inséré ou false si email déjà pris.
 */
function inscrireClient(array $d): int|false
{
    $pdo = obtenirConnexion();

    // Email unique
    $chk = $pdo->prepare("SELECT id_client FROM client WHERE email = :e LIMIT 1");
    $chk->execute([':e' => mb_strtolower(trim($d['email']))]);
    if ($chk->fetch()) return false;

    $req = $pdo->prepare("
        INSERT INTO client (prenom, nom, email, mot_de_passe_hash, province, telephone)
        VALUES (:prenom, :nom, :email, :hash, :province, :telephone)
    ");

    $ok = $req->execute([
        ':prenom'    => mb_substr(trim($d['prenom']), 0, 80),
        ':nom'       => mb_substr(trim($d['nom']), 0, 80),
        ':email'     => mb_strtolower(mb_substr(trim($d['email']), 0, 160)),
        ':hash'      => password_hash($d['mot_de_passe'], PASSWORD_BCRYPT, ['cost' => 12]),
        ':province'  => mb_substr(trim($d['province']  ?? ''), 0, 60)  ?: null,
        ':telephone' => mb_substr(trim($d['telephone'] ?? ''), 0, 30)  ?: null,
    ]);

    return $ok ? (int) $pdo->lastInsertId() : false;
}

/**
 * Authentifie un client. Retourne ses données ou null si identifiants invalides.
 */
function authentifierClient(string $email, string $mdp): ?array
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("SELECT * FROM client WHERE email = :e LIMIT 1");
    $req->execute([':e' => mb_strtolower(trim($email))]);
    $client = $req->fetch();

    if (!$client || !password_verify($mdp, $client['mot_de_passe_hash'])) {
        return null;
    }
    return $client;
}

/**
 * Retourne un client par son ID (sans le hash du mot de passe).
 */
function recupererClientParId(int $id): ?array
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        SELECT id_client, prenom, nom, email, province, telephone, date_inscription
        FROM client WHERE id_client = :id LIMIT 1
    ");
    $req->execute([':id' => $id]);
    $r = $req->fetch();
    return $r ?: null;
}

/**
 * Met à jour province et téléphone d'un client.
 */
function mettreAJourProfil(int $id, array $d): bool
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        UPDATE client SET province = :province, telephone = :telephone
        WHERE id_client = :id
    ");
    return $req->execute([
        ':province'  => mb_substr(trim($d['province']  ?? ''), 0, 60) ?: null,
        ':telephone' => mb_substr(trim($d['telephone'] ?? ''), 0, 30) ?: null,
        ':id'        => $id,
    ]);
}

/**
 * Retourne toutes les réservations d'un client avec espèce et statut oiseau.
 */
function recupererReservationsClient(int $idClient): array
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        SELECT r.id_reservation, r.date_demande, r.statut_reservation,
               r.statut_livraison, r.code_suivi, r.numero_suivi, r.transporteur, r.date_expedition,
               o.slug_fr        AS oiseau_slug,
               o.statut         AS oiseau_statut,
               e.nom_commun_fr  AS espece_nom,
               p.chemin_fichier AS photo_chemin
        FROM reservation r
        INNER JOIN oiseau o ON o.id_oiseau = r.id_oiseau
        INNER JOIN espece e ON e.id_espece = o.id_espece
        LEFT  JOIN photo  p ON p.id_oiseau = o.id_oiseau AND p.est_principale = 1
        WHERE r.client_id = :id
        ORDER BY r.date_demande DESC
    ");
    $req->execute([':id' => $idClient]);
    return $req->fetchAll();
}
