<?php
/**
 * Modèle des témoignages clients (affichés sur la page d'accueil,
 * gérés dans l'admin : menu « Témoignages »).
 */
require_once __DIR__ . '/../configuration/connexion.php';

/** Champs éditables (liste blanche). */
const CHAMPS_TEMOIGNAGE = [
    'titre_fr', 'titre_en', 'texte_fr', 'texte_en',
    'auteur', 'ville', 'province', 'visible', 'ordre',
];

/**
 * Liste les témoignages, triés par ordre puis du plus récent au plus ancien.
 * Tolérant : table absente (migration non jouée) → tableau vide.
 *
 * @param bool     $visiblesSeulement true = uniquement ceux publiés (site public)
 * @param int|null $limite            nombre maximum de lignes
 */
function listerTemoignages(bool $visiblesSeulement = false, ?int $limite = null): array
{
    $sql = "SELECT * FROM temoignage"
         . ($visiblesSeulement ? " WHERE visible = 1" : "")
         . " ORDER BY ordre ASC, date_ajout DESC, id_temoignage DESC"
         . ($limite !== null ? " LIMIT :limite" : "");
    try {
        $req = obtenirConnexion()->prepare($sql);
        if ($limite !== null) {
            $req->bindValue(':limite', max(1, $limite), PDO::PARAM_INT);
        }
        $req->execute();
        return $req->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function recupererTemoignage(int $id): ?array
{
    $req = obtenirConnexion()->prepare("SELECT * FROM temoignage WHERE id_temoignage = :id");
    $req->execute([':id' => $id]);
    $ligne = $req->fetch();
    return $ligne !== false ? $ligne : null;
}

/**
 * Normalise les valeurs d'un formulaire (longueurs, types).
 */
function normaliserTemoignage(array $d): array
{
    return [
        ':titre_fr' => mb_substr(trim($d['titre_fr'] ?? ''), 0, 120),
        ':titre_en' => mb_substr(trim($d['titre_en'] ?? ''), 0, 120),
        ':texte_fr' => mb_substr(trim($d['texte_fr'] ?? ''), 0, 2000),
        ':texte_en' => mb_substr(trim($d['texte_en'] ?? ''), 0, 2000),
        ':auteur'   => mb_substr(trim($d['auteur'] ?? ''), 0, 120),
        ':ville'    => mb_substr(trim($d['ville'] ?? ''), 0, 80),
        ':province' => mb_substr(trim($d['province'] ?? ''), 0, 2),
        ':visible'  => !empty($d['visible']) ? 1 : 0,
        ':ordre'    => (int) ($d['ordre'] ?? 0),
    ];
}

/** @return int id créé, 0 si échec */
function creerTemoignage(array $d): int
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        INSERT INTO temoignage (titre_fr, titre_en, texte_fr, texte_en, auteur, ville, province, visible, ordre)
        VALUES (:titre_fr, :titre_en, :texte_fr, :texte_en, :auteur, :ville, :province, :visible, :ordre)
    ");
    return $req->execute(normaliserTemoignage($d)) ? (int) $pdo->lastInsertId() : 0;
}

function modifierTemoignage(int $id, array $d): bool
{
    $req = obtenirConnexion()->prepare("
        UPDATE temoignage SET
            titre_fr = :titre_fr, titre_en = :titre_en, texte_fr = :texte_fr, texte_en = :texte_en,
            auteur = :auteur, ville = :ville, province = :province, visible = :visible, ordre = :ordre
        WHERE id_temoignage = :id
    ");
    return $req->execute(normaliserTemoignage($d) + [':id' => $id]);
}

function supprimerTemoignage(int $id): bool
{
    $req = obtenirConnexion()->prepare("DELETE FROM temoignage WHERE id_temoignage = :id");
    return $req->execute([':id' => $id]);
}

/** Bascule publié / masqué. */
function basculerVisibiliteTemoignage(int $id): bool
{
    $req = obtenirConnexion()->prepare("UPDATE temoignage SET visible = 1 - visible WHERE id_temoignage = :id");
    return $req->execute([':id' => $id]);
}
