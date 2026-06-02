<?php
require_once dirname(__DIR__) . '/inc/securite.php';
require_once RACINE . '/modeles/reservation-modele.php';
require_once RACINE . '/modeles/oiseau-modele.php';

$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$resa = $id ? recupererReservation($id) : null;

if (!$resa) {
    header('Location: ' . URL_SITE . '/admin/reservations/liste.php');
    exit;
}

// Actions POST (changement statut réservation ou oiseau)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';

    if ($action === 'statut_resa' && !empty($_POST['statut_reservation'])) {
        changerStatutReservation($id, $_POST['statut_reservation']);
        header('Location: ' . URL_SITE . '/admin/reservations/detail.php?id=' . $id . '&succes=1');
        exit;
    }

    if ($action === 'statut_oiseau' && !empty($_POST['statut_oiseau'])) {
        changerStatutOiseau((int) $resa['oiseau_id'], $_POST['statut_oiseau']);
        header('Location: ' . URL_SITE . '/admin/reservations/detail.php?id=' . $id . '&succes=1');
        exit;
    }

    if ($action === 'suivi') {
        mettreAJourSuivi($id, [
            'statut_livraison' => $_POST['statut_livraison'] ?? '',
            'numero_suivi'     => $_POST['numero_suivi']     ?? '',
            'transporteur'     => $_POST['transporteur']     ?? '',
            'date_expedition'  => $_POST['date_expedition']  ?? '',
        ]);
        header('Location: ' . URL_SITE . '/admin/reservations/detail.php?id=' . $id . '&succes=1');
        exit;
    }

    if ($action === 'generer_code') {
        assignerCodeSuivi($id);
        header('Location: ' . URL_SITE . '/admin/reservations/detail.php?id=' . $id . '&succes=code');
        exit;
    }
}

// Recharger après action
$resa = recupererReservation($id);
$csrf = genererJetonCsrf();

$titrePage = 'Demande de ' . ($resa['nom_client'] ?? '');
require_once dirname(__DIR__) . '/inc/entete-admin.php';
?>

<div class="admin-conteneur">
    <div class="admin-entete-page">
        <h1>Demande de <?= echapper($resa['nom_client']) ?></h1>
        <a href="<?= echapper(URL_SITE) ?>/admin/reservations/liste.php" class="bouton bouton-contour bouton-sm">← Retour</a>
    </div>

    <?php if (isset($_GET['succes'])) : ?>
        <div class="alerte alerte--succes">
            <?= $_GET['succes'] === 'code' ? '🔐 Nouveau code de suivi généré.' : 'Statut mis à jour.' ?>
        </div>
    <?php endif; ?>

    <div class="detail-grille">

        <!-- Infos client -->
        <section class="detail-section">
            <h2>Coordonnées du client</h2>
            <table class="fiche-tableau">
                <tbody>
                    <tr><th>Nom</th><td><?= echapper($resa['nom_client']) ?></td></tr>
                    <tr>
                        <th>Courriel</th>
                        <td><a href="mailto:<?= echapper($resa['email_client']) ?>"><?= echapper($resa['email_client']) ?></a></td>
                    </tr>
                    <?php if ($resa['telephone']) : ?>
                    <tr><th>Téléphone</th><td><?= echapper($resa['telephone']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($resa['province']) : ?>
                    <tr><th>Province</th><td><?= echapper($resa['province']) ?></td></tr>
                    <?php endif; ?>
                    <tr><th>Langue</th><td><?= echapper(strtoupper($resa['langue_demande'])) ?></td></tr>
                    <tr><th>Date</th><td><?= echapper(formaterDate($resa['date_demande'])) ?></td></tr>
                    <tr>
                        <th>Statut demande</th>
                        <td>
                            <span class="badge-resa badge-resa--<?= echapper($resa['statut_reservation']) ?>">
                                <?= echapper($resa['statut_reservation']) ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if ($resa['message']) : ?>
            <div class="message-client">
                <h3>Message</h3>
                <blockquote><?= nl2br(echapper($resa['message'])) ?></blockquote>
            </div>
            <?php endif; ?>
        </section>

        <!-- Actions -->
        <section class="detail-section">

            <!-- Rappel oiseau -->
            <h2>Oiseau concerné</h2>
            <div class="resa-rappel">
                <?php if ($resa['photo_chemin']) : ?>
                    <img src="<?= echapper($resa['photo_chemin']) ?>"
                         alt="" class="resa-rappel__photo">
                <?php endif; ?>
                <div class="resa-rappel__infos">
                    <p class="resa-rappel__espece"><?= echapper($resa['espece_nom']) ?></p>
                    <p><?= echapper($resa['oiseau_sexe']) ?></p>
                    <p>
                        Statut :
                        <span class="badge badge--<?= echapper($resa['oiseau_statut']) ?>">
                            <?= echapper($resa['oiseau_statut']) ?>
                        </span>
                    </p>
                    <a href="<?= echapper(URL_SITE) ?>/fr/oiseau/<?= echapper($resa['oiseau_slug']) ?>"
                       target="_blank" rel="noopener" class="texte-discret">
                       Voir la fiche publique ↗
                    </a>
                </div>
            </div>

            <!-- Action : statut réservation -->
            <div class="action-bloc">
                <h3>Changer le statut de la demande</h3>
                <form method="post" action="<?= echapper(URL_SITE) ?>/admin/reservations/detail.php?id=<?= $id ?>">
                    <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                    <input type="hidden" name="action" value="statut_resa">
                    <div class="filtres-groupe">
                        <select name="statut_reservation">
                            <?php foreach (['nouvelle','traitee','annulee'] as $s) : ?>
                                <option value="<?= $s ?>" <?= $resa['statut_reservation'] === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="bouton bouton-primaire bouton-sm">Enregistrer</button>
                    </div>
                </form>
            </div>

            <!-- Action : statut oiseau -->
            <div class="action-bloc">
                <h3>Changer le statut de l'oiseau</h3>
                <p class="texte-discret" style="font-size:.85rem;margin-bottom:.5rem;">
                    Le statut de la demande et celui de l'oiseau sont indépendants — contrôle manuel.
                </p>
                <form method="post" action="<?= echapper(URL_SITE) ?>/admin/reservations/detail.php?id=<?= $id ?>">
                    <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                    <input type="hidden" name="action" value="statut_oiseau">
                    <div class="filtres-groupe">
                        <select name="statut_oiseau">
                            <?php foreach (['disponible','reserve','vendu'] as $s) : ?>
                                <option value="<?= $s ?>" <?= $resa['oiseau_statut'] === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="bouton bouton-secondaire bouton-sm">Appliquer</button>
                    </div>
                </form>
            </div>

            <!-- Code de suivi sécurisé -->
            <div class="action-bloc">
                <h3>🔐 Code de suivi client</h3>
                <p class="texte-discret" style="font-size:.85rem;margin-bottom:.75rem;">
                    Dossier interne : <strong><?= echapper(genererReference($id)) ?></strong>.
                    Le code public ci-dessous est <strong>aléatoire</strong> — impossible à deviner.
                    Communiquez-le au client pour qu'il suive sa commande sur la page publique.
                </p>

                <?php $codeSuivi = $resa['code_suivi'] ?? null; ?>
                <?php if ($codeSuivi): ?>
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;background:var(--couleur-fond-alt);border:1px solid var(--couleur-bordure);border-radius:var(--rayon-bordure);padding:.85rem 1rem;margin-bottom:.75rem;">
                    <code style="font-size:1.2rem;font-weight:700;letter-spacing:.08em;color:var(--couleur-primaire);"><?= echapper($codeSuivi) ?></code>
                    <a href="<?= echapper(URL_SITE) ?>/fr/suivi?code=<?= echapper($codeSuivi) ?>"
                       target="_blank" rel="noopener" class="bouton bouton-contour bouton-sm">Tester ↗</a>
                </div>
                <form method="post" action="<?= echapper(URL_SITE) ?>/admin/reservations/detail.php?id=<?= $id ?>" style="margin-bottom:1.25rem;"
                      onsubmit="return confirm('Régénérer le code ? L\'ancien code ne fonctionnera plus.');">
                    <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                    <input type="hidden" name="action" value="generer_code">
                    <button type="submit" class="bouton bouton-contour bouton-sm">🔄 Régénérer le code</button>
                </form>
                <?php else: ?>
                <form method="post" action="<?= echapper(URL_SITE) ?>/admin/reservations/detail.php?id=<?= $id ?>" style="margin-bottom:1.25rem;">
                    <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                    <input type="hidden" name="action" value="generer_code">
                    <button type="submit" class="bouton bouton-primaire bouton-sm">🔐 Générer un code de suivi</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Suivi de remise -->
            <div class="action-bloc">
                <h3>📦 Suivi de remise</h3>
                <p class="texte-discret" style="font-size:.85rem;margin-bottom:.75rem;">
                    Détails affichés au client une fois le code de suivi généré.
                </p>
                <form method="post" action="<?= echapper(URL_SITE) ?>/admin/reservations/detail.php?id=<?= $id ?>">
                    <input type="hidden" name="csrf_token" value="<?= echapper($csrf) ?>">
                    <input type="hidden" name="action" value="suivi">

                    <div class="formulaire-admin" style="max-width:100%;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1rem;">
                            <div>
                                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.3rem;">Statut de livraison</label>
                                <select name="statut_livraison" style="width:100%;padding:.4rem .6rem;border:1px solid var(--couleur-bordure);border-radius:var(--rayon-bordure);background:var(--couleur-fond);">
                                    <option value="">— Non défini —</option>
                                    <option value="en_preparation" <?= ($resa['statut_livraison'] ?? '') === 'en_preparation' ? 'selected' : '' ?>>En préparation</option>
                                    <option value="expedie"        <?= ($resa['statut_livraison'] ?? '') === 'expedie'        ? 'selected' : '' ?>>Expédié</option>
                                    <option value="livre"          <?= ($resa['statut_livraison'] ?? '') === 'livre'          ? 'selected' : '' ?>>Remis à l'adoptant</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.3rem;">Date d'expédition</label>
                                <input type="date" name="date_expedition"
                                       value="<?= echapper($resa['date_expedition'] ?? '') ?>"
                                       style="width:100%;padding:.4rem .6rem;border:1px solid var(--couleur-bordure);border-radius:var(--rayon-bordure);background:var(--couleur-fond);">
                            </div>
                            <div>
                                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.3rem;">Transporteur</label>
                                <select name="transporteur" style="width:100%;padding:.4rem .6rem;border:1px solid var(--couleur-bordure);border-radius:var(--rayon-bordure);background:var(--couleur-fond);">
                                    <option value="">— Non défini —</option>
                                    <?php foreach (['Remise en main propre','Canada Post','Purolator','FedEx','UPS','Autre'] as $t): ?>
                                    <option value="<?= echapper($t) ?>" <?= ($resa['transporteur'] ?? '') === $t ? 'selected' : '' ?>><?= echapper($t) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.3rem;">N° de suivi transporteur</label>
                                <input type="text" name="numero_suivi"
                                       value="<?= echapper($resa['numero_suivi'] ?? '') ?>"
                                       placeholder="ex: 1234567890"
                                       style="width:100%;padding:.4rem .6rem;border:1px solid var(--couleur-bordure);border-radius:var(--rayon-bordure);background:var(--couleur-fond);">
                            </div>
                        </div>
                        <button type="submit" class="bouton bouton-primaire bouton-sm" style="margin-top:.75rem;">
                            Mettre à jour le suivi
                        </button>
                    </div>
                </form>
            </div>

        </section>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/inc/pied-admin.php'; ?>
