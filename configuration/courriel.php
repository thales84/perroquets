<?php
/**
 * Envoi de courriels.
 *
 * 1) SMTP authentifié (recommandé) si SMTP_MOT_DE_PASSE est défini dans le
 *    fichier secret : connexion à la boîte no-reply@… chez Hostinger
 *    (smtp.hostinger.com, port 465 SSL). Meilleure délivrabilité (SPF/DKIM).
 * 2) Sinon, repli sur mail() natif de PHP.
 *
 * Expéditeur : COURRIEL_EXPEDITEUR si défini, sinon SMTP_UTILISATEUR,
 * sinon no-reply@<domaine du site>.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../modeles/parametre-modele.php';

/**
 * Adresse d'expédition des courriels du site.
 */
function adresseExpediteur(): string
{
    if (defined('COURRIEL_EXPEDITEUR') && COURRIEL_EXPEDITEUR !== '') {
        return COURRIEL_EXPEDITEUR;
    }
    if (defined('SMTP_UTILISATEUR') && SMTP_UTILISATEUR !== '') {
        return SMTP_UTILISATEUR;
    }
    $hote = parse_url(URL_SITE, PHP_URL_HOST) ?: '';
    // En local (localhost), on emprunte le domaine de production.
    if ($hote === '' || !str_contains($hote, '.')) {
        $hote = 'mapleperroquets.com';
    }
    return 'no-reply@' . preg_replace('/^www\./', '', $hote);
}

/**
 * Supprime les retours à la ligne (anti-injection d'en-têtes).
 */
function nettoyerEnTete(string $valeur): string
{
    return trim(str_replace(["\r", "\n"], ' ', $valeur));
}

/**
 * Envoie un courriel texte brut en UTF-8.
 * Ne lève jamais d'exception : retourne false et journalise en cas d'échec.
 */
function envoyerCourriel(string $destinataire, string $sujet, string $texte, string $repondreA = ''): bool
{
    $destinataire = nettoyerEnTete($destinataire);
    if (!filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
        error_log('[courriel] Destinataire invalide : ' . $destinataire);
        return false;
    }

    $expediteur = adresseExpediteur();
    $nomSite    = nettoyerEnTete(param('site_nom', 'Maple Perroquets'));

    $entetes = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'From: ' . mb_encode_mimeheader($nomSite, 'UTF-8') . ' <' . $expediteur . '>',
    ];
    $repondreA = nettoyerEnTete($repondreA);
    if ($repondreA !== '' && filter_var($repondreA, FILTER_VALIDATE_EMAIL)) {
        $entetes[] = 'Reply-To: ' . $repondreA;
    }

    $sujetEncode = mb_encode_mimeheader(nettoyerEnTete($sujet), 'UTF-8');
    // Fins de ligne normalisées en CRLF.
    $texte = preg_replace('/\r\n|\r|\n/', "\r\n", $texte);

    try {
        if (smtpConfigure()) {
            $domaine   = substr(strrchr($expediteur, '@'), 1);
            $entetesSmtp = array_merge([
                'Date: ' . date('r'),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domaine . '>',
                'To: <' . $destinataire . '>',
                'Subject: ' . $sujetEncode,
            ], $entetes);
            $ok = envoyerParSmtp($expediteur, $destinataire, implode("\r\n", $entetesSmtp) . "\r\n\r\n" . $texte);
        } else {
            $ok = @mail($destinataire, $sujetEncode, $texte, implode("\r\n", $entetes), '-f' . $expediteur);
        }
    } catch (Throwable $e) {
        error_log('[courriel] ' . $e->getMessage());
        $ok = false;
    }
    if (!$ok) {
        error_log('[courriel] Échec d\'envoi à ' . $destinataire . ' : ' . $sujet);
    }
    return $ok;
}

/**
 * SMTP utilisable ? (mot de passe renseigné dans le fichier secret)
 */
function smtpConfigure(): bool
{
    return defined('SMTP_MOT_DE_PASSE') && SMTP_MOT_DE_PASSE !== '';
}

/**
 * Client SMTP minimal : SSL implicite (465), STARTTLS (587) ou clair (tests).
 * Lève une RuntimeException en cas de réponse inattendue du serveur.
 *
 * @param string $message Message complet (en-têtes + corps), fins de ligne CRLF
 */
function envoyerParSmtp(string $expediteur, string $destinataire, string $message): bool
{
    $hote      = defined('SMTP_HOTE')     ? SMTP_HOTE     : 'smtp.hostinger.com';
    $port      = defined('SMTP_PORT')     ? (int) SMTP_PORT : 465;
    $securite  = defined('SMTP_SECURITE') ? SMTP_SECURITE : ($port === 465 ? 'ssl' : 'tls');
    $utilisateur = defined('SMTP_UTILISATEUR') && SMTP_UTILISATEUR !== '' ? SMTP_UTILISATEUR : $expediteur;

    $prefixe = $securite === 'ssl' ? 'ssl://' : 'tcp://';
    $flux = @stream_socket_client($prefixe . $hote . ':' . $port, $errNo, $errStr, 15);
    if (!$flux) {
        throw new RuntimeException("SMTP connexion impossible ($hote:$port) : $errStr");
    }
    stream_set_timeout($flux, 15);

    // Lit une réponse (éventuellement multi-lignes) et vérifie le code attendu.
    $lire = function (array $codesOk) use ($flux): string {
        $reponse = '';
        while (($ligne = fgets($flux, 1024)) !== false) {
            $reponse .= $ligne;
            if (strlen($ligne) < 4 || $ligne[3] !== '-') {
                break;
            }
        }
        if (!in_array((int) substr($reponse, 0, 3), $codesOk, true)) {
            throw new RuntimeException('SMTP réponse inattendue : ' . trim($reponse));
        }
        return $reponse;
    };
    $commande = function (string $cmd, array $codesOk) use ($flux, $lire): string {
        fwrite($flux, $cmd . "\r\n");
        return $lire($codesOk);
    };

    try {
        $domaineLocal = parse_url(URL_SITE, PHP_URL_HOST) ?: 'localhost';
        $lire([220]);
        $commande('EHLO ' . $domaineLocal, [250]);

        if ($securite === 'tls') {
            $commande('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($flux, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('SMTP : échec STARTTLS');
            }
            $commande('EHLO ' . $domaineLocal, [250]);
        }

        $commande('AUTH LOGIN', [334]);
        $commande(base64_encode($utilisateur), [334]);
        $commande(base64_encode(SMTP_MOT_DE_PASSE), [235]);

        $commande('MAIL FROM:<' . $expediteur . '>', [250]);
        $commande('RCPT TO:<' . $destinataire . '>', [250, 251]);
        $commande('DATA', [354]);
        // Dot-stuffing : une ligne commençant par « . » est doublée.
        $corps = preg_replace('/^\./m', '..', $message);
        $commande($corps . "\r\n.", [250]);
        $commande('QUIT', [221]);
    } finally {
        fclose($flux);
    }
    return true;
}

/**
 * Prévient l'administrateur d'une nouvelle réservation, à l'adresse
 * « Courriel de contact » (Paramètres > Contact & WhatsApp).
 * Sans adresse configurée : aucun envoi.
 *
 * @param int   $idResa  id de la réservation créée
 * @param array $resa    nom_client, email_client, telephone, province, message
 * @param array $oiseau  ligne oiseau (espece_nom_fr, num_bague, prix_cad, slug_fr)
 */
function notifierAdminReservation(int $idResa, array $resa, array $oiseau): bool
{
    $destinataire = param('entreprise_courriel');
    if ($destinataire === '') {
        return false;
    }

    $reference = 'MP-' . str_pad((string) $idResa, 5, '0', STR_PAD_LEFT);
    $espece    = $oiseau['espece_nom_fr'] ?? '';
    $prix      = isset($oiseau['prix_cad']) && $oiseau['prix_cad'] !== null && $oiseau['prix_cad'] !== ''
                 ? number_format((float) $oiseau['prix_cad'], 2, ',', ' ') . ' $ CAD'
                 : 'sur demande';
    $base      = rtrim(URL_SITE, '/');

    $lignes = [
        'Nouvelle demande de réservation reçue sur le site.',
        '',
        'Référence : ' . $reference,
        'Date      : ' . date('Y-m-d H:i'),
        '',
        '--- Oiseau ---',
        'Espèce    : ' . $espece,
        'Bague     : ' . (($oiseau['num_bague'] ?? '') !== '' ? $oiseau['num_bague'] : '—'),
        'Prix      : ' . $prix,
        'Fiche     : ' . $base . '/fr/oiseau/' . ($oiseau['slug_fr'] ?? ''),
        '',
        '--- Client ---',
        'Nom       : ' . $resa['nom_client'],
        'Courriel  : ' . $resa['email_client'],
        'Téléphone : ' . (($resa['telephone'] ?? '') !== '' ? $resa['telephone'] : '—'),
        'Province  : ' . (($resa['province'] ?? '') !== '' ? $resa['province'] : '—'),
        '',
        '--- Message ---',
        ($resa['message'] ?? '') !== '' ? $resa['message'] : '(aucun)',
        '',
        'Gérer la réservation :',
        $base . '/admin/reservations/detail.php?id=' . $idResa,
        '',
        'Répondez directement à ce courriel pour écrire au client.',
    ];

    $sujet = 'Nouvelle réservation ' . $reference . ' — ' . $espece . ' (' . $resa['nom_client'] . ')';

    return envoyerCourriel($destinataire, $sujet, implode("\n", $lignes), $resa['email_client']);
}
