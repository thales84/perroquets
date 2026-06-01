<?php
require_once __DIR__ . '/../configuration/connexion.php';

/**
 * Retourne les oiseaux disponibles avec jointure espèce et photo principale.
 * Filtres optionnels : id_espece (int), sexe (string), sevre_main (int 0|1).
 *
 * @param  array $filtres Tableau associatif de filtres (clés optionnelles)
 * @return array          Tableau de lignes associatives
 */
function recupererOiseauxDisponibles(array $filtres = []): array
{
    $pdo = obtenirConnexion();

    $conditions = ["o.statut = 'disponible'"];
    $parametres = [];

    if (!empty($filtres['id_espece'])) {
        $conditions[] = 'o.id_espece = :id_espece';
        $parametres[':id_espece'] = (int) $filtres['id_espece'];
    }

    if (!empty($filtres['sexe']) && in_array($filtres['sexe'], ['male', 'femelle', 'inconnu'], true)) {
        $conditions[] = 'o.sexe = :sexe';
        $parametres[':sexe'] = $filtres['sexe'];
    }

    if (isset($filtres['sevre_main']) && $filtres['sevre_main'] !== '') {
        $conditions[] = 'o.sevre_main = :sevre_main';
        $parametres[':sevre_main'] = (int) $filtres['sevre_main'];
    }

    $clauseWhere = implode(' AND ', $conditions);

    $sql = "
        SELECT
            o.id_oiseau,
            o.slug_fr,
            o.sexe,
            o.date_naissance,
            o.prix_cad,
            o.sevre_main,
            o.statut,
            e.nom_commun_fr   AS espece_nom,
            e.slug_fr         AS espece_slug,
            p.chemin_fichier  AS photo_chemin,
            p.texte_alt_fr    AS photo_alt
        FROM oiseau o
        INNER JOIN espece e ON e.id_espece = o.id_espece
        LEFT JOIN photo p   ON p.id_oiseau = o.id_oiseau AND p.est_principale = 1
        WHERE {$clauseWhere}
        ORDER BY o.date_ajout DESC
    ";

    $req = $pdo->prepare($sql);
    $req->execute($parametres);

    return $req->fetchAll();
}

/**
 * Retourne toutes les espèces pour alimenter le filtre de la liste.
 *
 * @return array Tableau de lignes (id_espece, nom_commun_fr, slug_fr)
 */
function recupererListeEspeces(): array
{
    $pdo = obtenirConnexion();

    $req = $pdo->prepare("
        SELECT id_espece, nom_commun_fr, slug_fr
        FROM espece
        ORDER BY nom_commun_fr ASC
    ");
    $req->execute();

    return $req->fetchAll();
}

/**
 * Retourne un oiseau par son slug (toutes langues), avec espèce et photos.
 * Utilisée par la fiche oiseau (étape 06).
 *
 * @param  string $slug   Slug FR ou EN de l'oiseau
 * @return array|null     Données de l'oiseau ou null si introuvable
 */
function recupererOiseauParSlug(string $slug): ?array
{
    $pdo = obtenirConnexion();

    $req = $pdo->prepare("
        SELECT
            o.*,
            e.nom_commun_fr  AS espece_nom_fr,
            e.nom_commun_en  AS espece_nom_en,
            e.slug_fr        AS espece_slug_fr,
            e.description_fr AS espece_description_fr,
            e.description_en AS espece_description_en
        FROM oiseau o
        INNER JOIN espece e ON e.id_espece = o.id_espece
        WHERE o.slug_fr = :slug_fr OR o.slug_en = :slug_en
        LIMIT 1
    ");
    $req->execute([':slug_fr' => $slug, ':slug_en' => $slug]);

    $resultat = $req->fetch();
    return $resultat !== false ? $resultat : null;
}

/**
 * Retourne toutes les photos d'un oiseau, triées par ordre_affichage.
 *
 * @param  int   $idOiseau
 * @return array Tableau de lignes photo
 */
function recupererPhotosOiseau(int $idOiseau): array
{
    $pdo = obtenirConnexion();

    $req = $pdo->prepare("
        SELECT chemin_fichier, texte_alt_fr, texte_alt_en, est_principale, ordre_affichage
        FROM photo
        WHERE id_oiseau = :id_oiseau
        ORDER BY est_principale DESC, ordre_affichage ASC
    ");
    $req->execute([':id_oiseau' => $idOiseau]);

    return $req->fetchAll();
}

// ============================================================
// Fonctions d'écriture — utilisées par l'admin (étape 11)
// ============================================================

/**
 * Liste tous les oiseaux (tous statuts) avec espèce et photo principale.
 * Filtres optionnels : statut, id_espece.
 */
function listerTousLesOiseaux(array $filtres = []): array
{
    $pdo        = obtenirConnexion();
    $conditions = [];
    $parametres = [];

    if (!empty($filtres['statut'])) {
        $conditions[] = 'o.statut = :statut';
        $parametres[':statut'] = $filtres['statut'];
    }
    if (!empty($filtres['id_espece'])) {
        $conditions[] = 'o.id_espece = :id_espece';
        $parametres[':id_espece'] = (int) $filtres['id_espece'];
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $req = $pdo->prepare("
        SELECT o.id_oiseau, o.slug_fr, o.sexe, o.statut, o.prix_cad,
               o.sevre_main, o.date_naissance, o.date_ajout, o.num_bague,
               e.nom_commun_fr AS espece_nom,
               p.chemin_fichier AS photo_chemin
        FROM oiseau o
        INNER JOIN espece e ON e.id_espece = o.id_espece
        LEFT JOIN photo p   ON p.id_oiseau = o.id_oiseau AND p.est_principale = 1
        {$where}
        ORDER BY o.date_ajout DESC
    ");
    $req->execute($parametres);
    return $req->fetchAll();
}

/**
 * Retourne un oiseau complet par son ID (pour le formulaire admin).
 */
function recupererOiseauParId(int $id): ?array
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        SELECT o.*, e.nom_commun_fr AS espece_nom, e.slug_fr AS espece_slug_fr
        FROM oiseau o
        INNER JOIN espece e ON e.id_espece = o.id_espece
        WHERE o.id_oiseau = :id LIMIT 1
    ");
    $req->execute([':id' => $id]);
    $r = $req->fetch();
    return $r !== false ? $r : null;
}

/**
 * Crée un oiseau. Génère un slug unique depuis espèce + sexe + année + bague.
 * Retourne l'ID inséré ou false.
 */
function creerOiseau(array $donnees): int|false
{
    $pdo = obtenirConnexion();
    require_once __DIR__ . '/../configuration/fonctions.php';

    $base  = genererSlug(($donnees['espece_nom'] ?? 'oiseau') . '-' . ($donnees['sexe'] ?? '') . '-' . date('Y'));
    if (!empty($donnees['num_bague'])) {
        $base .= '-' . genererSlug($donnees['num_bague']);
    }

    // Garantir l'unicité du slug
    $slug   = $base;
    $suffix = 1;
    while (true) {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM oiseau WHERE slug_fr = :s");
        $chk->execute([':s' => $slug]);
        if ((int) $chk->fetchColumn() === 0) break;
        $slug = $base . '-' . (++$suffix);
    }

    $req = $pdo->prepare("
        INSERT INTO oiseau
            (id_espece, slug_fr, sexe, date_naissance, num_bague, prix_cad,
             sevre_main, statut, description_fr, description_en)
        VALUES
            (:id_espece, :slug_fr, :sexe, :date_naissance, :num_bague, :prix_cad,
             :sevre_main, :statut, :description_fr, :description_en)
    ");

    $ok = $req->execute([
        ':id_espece'      => (int) $donnees['id_espece'],
        ':slug_fr'        => $slug,
        ':sexe'           => in_array($donnees['sexe'] ?? '', ['male','femelle','inconnu'], true)
                             ? $donnees['sexe'] : 'inconnu',
        ':date_naissance' => !empty($donnees['date_naissance']) ? $donnees['date_naissance'] : null,
        ':num_bague'      => !empty($donnees['num_bague'])  ? mb_substr(trim($donnees['num_bague']), 0, 60)   : null,
        ':prix_cad'       => is_numeric($donnees['prix_cad'] ?? '') ? (float) $donnees['prix_cad'] : null,
        ':sevre_main'     => !empty($donnees['sevre_main']) ? 1 : 0,
        ':statut'         => in_array($donnees['statut'] ?? '', ['disponible','reserve','vendu'], true)
                             ? $donnees['statut'] : 'disponible',
        ':description_fr' => trim($donnees['description_fr'] ?? '') ?: null,
        ':description_en' => trim($donnees['description_en'] ?? '') ?: null,
    ]);

    return $ok ? (int) $pdo->lastInsertId() : false;
}

/**
 * Modifie un oiseau existant.
 */
function modifierOiseau(int $id, array $donnees): bool
{
    $pdo = obtenirConnexion();

    $req = $pdo->prepare("
        UPDATE oiseau SET
            id_espece      = :id_espece,
            sexe           = :sexe,
            date_naissance = :date_naissance,
            num_bague      = :num_bague,
            prix_cad       = :prix_cad,
            sevre_main     = :sevre_main,
            statut         = :statut,
            description_fr = :description_fr,
            description_en = :description_en
        WHERE id_oiseau = :id
    ");

    return $req->execute([
        ':id_espece'      => (int) $donnees['id_espece'],
        ':sexe'           => in_array($donnees['sexe'] ?? '', ['male','femelle','inconnu'], true)
                             ? $donnees['sexe'] : 'inconnu',
        ':date_naissance' => !empty($donnees['date_naissance']) ? $donnees['date_naissance'] : null,
        ':num_bague'      => !empty($donnees['num_bague'])  ? mb_substr(trim($donnees['num_bague']), 0, 60)   : null,
        ':prix_cad'       => is_numeric($donnees['prix_cad'] ?? '') ? (float) $donnees['prix_cad'] : null,
        ':sevre_main'     => !empty($donnees['sevre_main']) ? 1 : 0,
        ':statut'         => in_array($donnees['statut'] ?? '', ['disponible','reserve','vendu'], true)
                             ? $donnees['statut'] : 'disponible',
        ':description_fr' => trim($donnees['description_fr'] ?? '') ?: null,
        ':description_en' => trim($donnees['description_en'] ?? '') ?: null,
        ':id'             => $id,
    ]);
}

/**
 * Change le statut d'un oiseau (action rapide depuis la liste).
 */
function changerStatutOiseau(int $id, string $statut): bool
{
    if (!in_array($statut, ['disponible', 'reserve', 'vendu'], true)) return false;
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("UPDATE oiseau SET statut = :statut WHERE id_oiseau = :id");
    return $req->execute([':statut' => $statut, ':id' => $id]);
}

/**
 * Supprime un oiseau et ses fichiers photos physiques.
 * La cascade DB (ON DELETE CASCADE) supprime photos/réservations.
 */
function supprimerOiseau(int $id): bool
{
    $pdo = obtenirConnexion();

    // Récupérer les fichiers avant suppression DB
    $req = $pdo->prepare("SELECT chemin_fichier FROM photo WHERE id_oiseau = :id");
    $req->execute([':id' => $id]);
    $fichiers = $req->fetchAll(PDO::FETCH_COLUMN);

    $req = $pdo->prepare("DELETE FROM oiseau WHERE id_oiseau = :id");
    $req->execute([':id' => $id]);

    if ($req->rowCount() > 0) {
        // Supprimer les fichiers physiques
        foreach ($fichiers as $chemin) {
            $absolu = $_SERVER['DOCUMENT_ROOT'] . $chemin;
            if (is_file($absolu)) {
                @unlink($absolu);
            }
        }
        return true;
    }
    return false;
}

// --- Fonctions photos ---

/**
 * Enregistre une photo en base après upload.
 */
function ajouterPhoto(int $idOiseau, string $chemin, string $altFr, int $ordre = 0): bool
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        INSERT INTO photo (id_oiseau, chemin_fichier, texte_alt_fr, est_principale, ordre_affichage)
        VALUES (:id_oiseau, :chemin, :alt, 0, :ordre)
    ");
    return $req->execute([
        ':id_oiseau' => $idOiseau,
        ':chemin'    => $chemin,
        ':alt'       => mb_substr($altFr, 0, 200),
        ':ordre'     => $ordre,
    ]);
}

/**
 * Définit une photo comme principale (désactive les autres du même oiseau).
 */
function definirPhotoPrincipale(int $idPhoto, int $idOiseau): bool
{
    $pdo = obtenirConnexion();
    $pdo->prepare("UPDATE photo SET est_principale = 0 WHERE id_oiseau = :io")->execute([':io' => $idOiseau]);
    $req = $pdo->prepare("UPDATE photo SET est_principale = 1 WHERE id_photo = :ip AND id_oiseau = :io");
    return $req->execute([':ip' => $idPhoto, ':io' => $idOiseau]);
}

/**
 * Supprime une photo (ligne DB + fichier physique).
 */
function supprimerPhoto(int $idPhoto, int $idOiseau): bool
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("SELECT chemin_fichier FROM photo WHERE id_photo = :id AND id_oiseau = :io");
    $req->execute([':id' => $idPhoto, ':io' => $idOiseau]);
    $photo = $req->fetch();
    if (!$photo) return false;

    $pdo->prepare("DELETE FROM photo WHERE id_photo = :id")->execute([':id' => $idPhoto]);

    $absolu = $_SERVER['DOCUMENT_ROOT'] . $photo['chemin_fichier'];
    if (is_file($absolu)) @unlink($absolu);

    return true;
}

/**
 * Retourne toutes les photos d'un oiseau avec leur ID (pour l'admin).
 */
function recupererPhotosOiseauAdmin(int $idOiseau): array
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare("
        SELECT id_photo, chemin_fichier, texte_alt_fr, est_principale, ordre_affichage
        FROM photo WHERE id_oiseau = :id
        ORDER BY est_principale DESC, ordre_affichage ASC
    ");
    $req->execute([':id' => $idOiseau]);
    return $req->fetchAll();
}
