<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/oiseau-modele.php';

$action   = $_POST['action']   ?? '';
$idOiseau = (int) ($_POST['id_oiseau'] ?? 0);
$idPhoto  = (int) ($_POST['id_photo']  ?? 0);

if (!$idOiseau || !verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
    header('Location: ' . URL_SITE . '/admin/oiseaux/liste.php');
    exit;
}

$retour = URL_SITE . '/admin/oiseaux/modifier.php?id=' . $idOiseau;

switch ($action) {

    case 'upload':
        $TYPES_AUTORISES = ['image/jpeg', 'image/png', 'image/webp'];
        $TAILLE_MAX      = 5 * 1024 * 1024; // 5 Mo
        $EXT_PAR_TYPE    = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        $altFr    = mb_substr(trim($_POST['texte_alt_fr'] ?? ''), 0, 200);
        $fichiers = $_FILES['fichiers'] ?? null;

        if (!$fichiers || empty($fichiers['tmp_name'][0])) {
            header('Location: ' . $retour . '&upload_erreur=' . urlencode('Aucun fichier reçu.'));
            exit;
        }

        $dossier = $_SERVER['DOCUMENT_ROOT'] . CHEMIN_MEDIAS;
        $erreurUpload = null;

        foreach ($fichiers['tmp_name'] as $i => $tmpName) {
            if ($fichiers['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($fichiers['size'][$i] > $TAILLE_MAX) {
                $erreurUpload = 'Fichier trop volumineux (max 5 Mo).';
                break;
            }

            // Vérification MIME réelle (pas l'extension)
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($tmpName);

            if (!in_array($mime, $TYPES_AUTORISES, true)) {
                $erreurUpload = 'Type de fichier non autorisé : ' . $mime;
                break;
            }

            $ext      = $EXT_PAR_TYPE[$mime];
            $nomFichier = uniqid('oiseau-', true) . '.' . $ext;
            $destination = $dossier . $nomFichier;

            if (!move_uploaded_file($tmpName, $destination)) {
                $erreurUpload = 'Erreur lors du déplacement du fichier.';
                break;
            }

            $chemin = CHEMIN_MEDIAS . $nomFichier;
            // Ordre = nombre de photos actuelles
            $pdo = obtenirConnexion();
            $req = $pdo->prepare("SELECT COUNT(*) FROM photo WHERE id_oiseau = :id");
            $req->execute([':id' => $idOiseau]);
            $ordre = (int) $req->fetchColumn();

            ajouterPhoto($idOiseau, $chemin, $altFr, $ordre);
        }

        if ($erreurUpload) {
            header('Location: ' . $retour . '&upload_erreur=' . urlencode($erreurUpload));
        } else {
            header('Location: ' . $retour . '&succes=modifie');
        }
        exit;

    case 'principal':
        definirPhotoPrincipale($idPhoto, $idOiseau);
        header('Location: ' . $retour);
        exit;

    case 'supprimer':
        supprimerPhoto($idPhoto, $idOiseau);
        header('Location: ' . $retour);
        exit;

    default:
        header('Location: ' . $retour);
        exit;
}
