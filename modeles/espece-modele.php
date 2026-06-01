<?php
require_once __DIR__ . '/../configuration/connexion.php';
require_once __DIR__ . '/../configuration/fonctions.php';

/**
 * Retourne toutes les espèces avec le nombre d'oiseaux rattachés.
 */
function listerEspeces(): array
{
    $pdo = obtenirConnexion();
    $req = $pdo->query("
        SELECT e.*, COUNT(o.id_oiseau) AS nb_oiseaux
        FROM espece e
        LEFT JOIN oiseau o ON o.id_espece = e.id_espece
        GROUP BY e.id_espece
        ORDER BY e.nom_commun_fr ASC
    ");
    return $req->fetchAll();
}

/**
 * Retourne une espèce par son ID.
 */
function recupererEspece(int $id): ?array
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("SELECT * FROM espece WHERE id_espece = :id LIMIT 1");
    $req->execute([':id' => $id]);
    $r = $req->fetch();
    return $r !== false ? $r : null;
}

/**
 * Crée une espèce. Génère slug_fr depuis nom_commun_fr si non fourni.
 * Retourne l'ID inséré ou false.
 */
function creerEspece(array $donnees): int|false
{
    $pdo = obtenirConnexion();

    $slugFr = !empty($donnees['slug_fr'])
        ? genererSlug($donnees['slug_fr'])
        : genererSlug($donnees['nom_commun_fr']);

    $req = $pdo->prepare("
        INSERT INTO espece
            (nom_commun_fr, nom_commun_en, nom_scientifique, famille_fr, famille_en,
             slug_fr, slug_en, description_fr, description_en)
        VALUES
            (:nom_commun_fr, :nom_commun_en, :nom_scientifique, :famille_fr, :famille_en,
             :slug_fr, :slug_en, :description_fr, :description_en)
    ");

    $ok = $req->execute([
        ':nom_commun_fr'  => trim($donnees['nom_commun_fr']),
        ':nom_commun_en'  => trim($donnees['nom_commun_en']  ?? '') ?: null,
        ':nom_scientifique' => trim($donnees['nom_scientifique'] ?? '') ?: null,
        ':famille_fr'     => trim($donnees['famille_fr']     ?? '') ?: null,
        ':famille_en'     => trim($donnees['famille_en']     ?? '') ?: null,
        ':slug_fr'        => $slugFr,
        ':slug_en'        => !empty($donnees['slug_en']) ? genererSlug($donnees['slug_en']) : null,
        ':description_fr' => trim($donnees['description_fr'] ?? '') ?: null,
        ':description_en' => trim($donnees['description_en'] ?? '') ?: null,
    ]);

    return $ok ? (int) $pdo->lastInsertId() : false;
}

/**
 * Modifie une espèce existante.
 */
function modifierEspece(int $id, array $donnees): bool
{
    $pdo = obtenirConnexion();

    $slugFr = !empty($donnees['slug_fr'])
        ? genererSlug($donnees['slug_fr'])
        : genererSlug($donnees['nom_commun_fr']);

    $req = $pdo->prepare("
        UPDATE espece SET
            nom_commun_fr   = :nom_commun_fr,
            nom_commun_en   = :nom_commun_en,
            nom_scientifique = :nom_scientifique,
            famille_fr      = :famille_fr,
            famille_en      = :famille_en,
            slug_fr         = :slug_fr,
            slug_en         = :slug_en,
            description_fr  = :description_fr,
            description_en  = :description_en
        WHERE id_espece = :id
    ");

    return $req->execute([
        ':nom_commun_fr'    => trim($donnees['nom_commun_fr']),
        ':nom_commun_en'    => trim($donnees['nom_commun_en']    ?? '') ?: null,
        ':nom_scientifique' => trim($donnees['nom_scientifique'] ?? '') ?: null,
        ':famille_fr'       => trim($donnees['famille_fr']       ?? '') ?: null,
        ':famille_en'       => trim($donnees['famille_en']       ?? '') ?: null,
        ':slug_fr'          => $slugFr,
        ':slug_en'          => !empty($donnees['slug_en']) ? genererSlug($donnees['slug_en']) : null,
        ':description_fr'   => trim($donnees['description_fr']   ?? '') ?: null,
        ':description_en'   => trim($donnees['description_en']   ?? '') ?: null,
        ':id'               => $id,
    ]);
}

/**
 * Supprime une espèce. Retourne false si des oiseaux y sont rattachés.
 * Retourne 'oiseaux' si bloqué par FK, true si succès.
 */
function supprimerEspece(int $id): bool|string
{
    $pdo = obtenirConnexion();

    // Vérification préalable (redondante avec FK RESTRICT, mais message explicite)
    $req = $pdo->prepare("SELECT COUNT(*) FROM oiseau WHERE id_espece = :id");
    $req->execute([':id' => $id]);
    if ((int) $req->fetchColumn() > 0) {
        return 'oiseaux';
    }

    $req = $pdo->prepare("DELETE FROM espece WHERE id_espece = :id");
    $req->execute([':id' => $id]);
    return $req->rowCount() > 0;
}
