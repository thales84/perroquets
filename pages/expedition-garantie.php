<?php
/**
 * Page « Réservation, expédition et garantie » — /fr/expedition-et-garantie
 * Conditions de vente orientées protection de l'acheteur :
 * vérification de l'oiseau avant tout paiement, contrat écrit, prix total
 * fixé d'avance, expédition au Canada uniquement, garantie santé écrite.
 */
require_once __DIR__ . '/../modeles/parametre-modele.php';

$langue = langueActive();
$coord  = coordonneesEntreprise();
$base   = rtrim(URL_SITE, '/');
$lieu   = implode(', ', array_filter([$coord['ville'], $coord['province_nom']]));

$titrePage       = t('conditions_titre');
$descriptionPage = t('conditions_description');
$urlCanonique    = $base . '/' . $langue . '/expedition-et-garantie';
$jsonLd          = genererBreadcrumbJsonLd([
    ['nom' => t('nav_accueil'),     'url' => $base . '/' . $langue . '/'],
    ['nom' => t('conditions_titre'), 'url' => $urlCanonique],
]);

$lienOiseaux = $base . '/' . $langue . '/oiseaux';

require_once __DIR__ . '/../gabarits/entete.php';
?>

<article class="page-conditions">

    <header class="conditions-entete">
        <p class="eyebrow"><?= echapper(t('conditions_eyebrow')) ?></p>
        <h1><?= echapper(t('conditions_titre')) ?></h1>
        <p class="conditions-chapo">
            <?= echapper($coord['nom']) ?> élève ses perroquets à la main<?= $lieu !== '' ? ' à ' . echapper($lieu) . ($coord['annee'] !== '' ? ',' : '') : '' ?><?= $coord['annee'] !== '' ? ' depuis ' . echapper($coord['annee']) : '' ?>.
            Cette page explique, étape par étape, comment se passent la réservation, le paiement,
            la remise ou l'expédition de votre oiseau, et ce que couvre notre garantie.
        </p>
    </header>

    <aside class="conditions-engagements" aria-label="Nos engagements">
        <h2>Nos engagements en bref</h2>
        <ul>
            <li><strong>Vous voyez votre oiseau avant de payer quoi que ce soit</strong>, en visite ou par appel vidéo en direct.</li>
            <li><strong>Un contrat de vente écrit</strong>, remis avant tout acompte.</li>
            <li><strong>Un prix total fixé d'avance</strong> : oiseau + transport. Aucun frais ajouté ensuite.</li>
            <li><strong>Un reçu pour chaque paiement.</strong></li>
            <li><strong>Une garantie santé écrite</strong>, avec sa durée et ce qu'elle couvre.</li>
        </ul>
    </aside>

    <nav class="conditions-sommaire" aria-label="Sommaire">
        <ol>
            <li><a href="#voir">Voir votre oiseau avant de réserver</a></li>
            <li><a href="#reservation">Réservation, contrat et acompte</a></li>
            <li><a href="#paiement">Paiement : nos règles de sécurité</a></li>
            <li><a href="#remise">Remise en personne ou expédition</a></li>
            <li><a href="#sante">Santé et documents</a></li>
            <li><a href="#garantie">Garantie santé</a></li>
            <li><a href="#contact">Nous joindre</a></li>
        </ol>
    </nav>

    <section id="voir">
        <h2>1. Voir votre oiseau avant de réserver</h2>
        <p>Avant tout engagement, vous rencontrez l'oiseau que vous souhaitez accueillir. Deux possibilités, toutes deux <strong>gratuites et sans acompte</strong> :</p>
        <ul>
            <li><strong>Une visite sur rendez-vous</strong> à notre élevage<?= $coord['ville'] !== '' ? ' de ' . echapper($coord['ville']) : '' ?>.</li>
            <li><strong>Un appel vidéo en direct</strong>, si vous habitez loin : nous vous montrons l'oiseau, son comportement et le numéro de sa bague, que vous retrouverez ensuite sur le contrat.</li>
        </ul>
        <p>Pour protéger la santé de nos oiseaux, les visites suivent quelques règles simples : lavage des mains à l'arrivée et, idéalement, pas de visite d'une animalerie ou d'un autre élevage le même jour.</p>
    </section>

    <section id="reservation">
        <h2>2. Réservation, contrat et acompte</h2>
        <ol class="conditions-etapes">
            <li><strong>Demande de réservation</strong> : vous remplissez le formulaire sur la fiche de l'oiseau. C'est gratuit et sans engagement ; nous vous répondons sous 48&nbsp;h.</li>
            <li><strong>Vérification</strong> : visite ou appel vidéo, comme décrit ci-dessus.</li>
            <li><strong>Contrat de vente écrit</strong> : il précise l'espèce, le numéro de bague, la date d'éclosion, le prix de l'oiseau, le mode et le coût du transport, le montant de l'acompte, les conditions de remboursement et la garantie santé.</li>
            <li><strong>Acompte</strong> : une fois le contrat accepté, un acompte confirme la réservation. Nous vous remettons un reçu et l'oiseau est retiré de la vitrine.</li>
            <li><strong>Solde</strong> : il est réglé à la remise en personne ou, en cas d'expédition, avant le départ de l'oiseau, selon les termes du contrat.</li>
        </ol>
        <p>Si, pour une raison qui dépend de nous (par exemple un problème de santé de l'oiseau avant la remise), nous ne pouvons pas vous remettre l'oiseau réservé, <strong>l'acompte vous est remboursé en totalité</strong>. Les autres cas d'annulation sont décrits dans le contrat.</p>
    </section>

    <section id="paiement">
        <h2>3. Paiement : nos règles de sécurité</h2>
        <p>Les paiements se font par <strong>virement Interac</strong>, uniquement après la signature du contrat. Pour votre protection :</p>
        <ul>
            <li>Nous ne demandons <strong>jamais</strong> de carte-cadeau, de cryptomonnaie ni de transfert d'argent de type Western Union ou MoneyGram.</li>
            <li>Nous ne réclamons <strong>jamais de frais imprévus</strong> une fois le contrat signé : pas de « frais d'assurance », de « cage climatisée », de « frais de douane » ou de « frais vétérinaires » de dernière minute.</li>
            <li>Les coordonnées de paiement figurent sur le contrat. Si l'on vous demande de payer ailleurs ou autrement, ne payez pas et contactez-nous par les coordonnées indiquées sur cette page.</li>
        </ul>
        <p class="conditions-note">Achat d'un animal en ligne : le <a href="https://antifraudcentre-centreantifraude.ca/" target="_blank" rel="noopener">Centre antifraude du Canada</a> publie des conseils utiles pour éviter les arnaques. Nous vous encourageons à les lire.</p>
    </section>

    <section id="remise">
        <h2>4. Remise en personne ou expédition</h2>
        <h3>Remise en personne</h3>
        <p>C'est la solution que nous privilégions : vous venez chercher votre oiseau<?= $coord['ville'] !== '' ? ' à ' . echapper($coord['ville']) : '' ?>, et nous prenons le temps de vous présenter ses habitudes, son alimentation et ses besoins.</p>

        <h3>Expédition au Canada</h3>
        <p>Si vous habitez loin, nous pouvons expédier votre oiseau <strong>partout au Canada</strong> par le service de fret d'une compagnie aérienne qui accepte les animaux vivants :</p>
        <ul>
            <li>caisse de transport conforme aux normes de l'IATA pour les animaux vivants, adaptée à la taille de l'oiseau ;</li>
            <li>vols directs privilégiés, et aucun envoi par chaleur ou froid extrême ;</li>
            <li>l'oiseau voyage avec de quoi s'hydrater et se nourrir, ainsi qu'avec ses documents ;</li>
            <li>vous le récupérez au comptoir de fret de l'aéroport convenu, sur présentation d'une pièce d'identité.</li>
        </ul>
        <p>Pour organiser l'envoi, nous vous demandons : votre nom complet, votre adresse, votre numéro de téléphone, l'aéroport de destination et, le cas échéant, le nom de la personne autorisée à récupérer l'oiseau. Dès le vol réservé, nous vous transmettons le numéro de vol et la référence de l'envoi (lettre de transport aérien), que vous pouvez vérifier directement auprès de la compagnie aérienne.</p>

        <h3>Frais de transport</h3>
        <p>Ils dépendent de la destination, de la taille de l'oiseau et de la caisse nécessaire. Leur montant est <strong>inscrit au contrat avant l'acompte</strong> et ne change plus ensuite.</p>

        <h3>Hors du Canada</h3>
        <p>Nous n'expédions pas à l'étranger. L'importation de perroquets, notamment aux États-Unis, est encadrée par la CITES et des règles d'importation strictes (permis, quarantaine) qu'un envoi entre particuliers ne permet pas de respecter.</p>
    </section>

    <section id="sante">
        <h2>5. Santé et documents</h2>
        <p>Chaque oiseau est suivi selon son âge et part en bonne santé, sevré et habitué à une alimentation variée. Avec votre oiseau, vous recevez :</p>
        <ul>
            <li>son identification : espèce, numéro de bague et date d'éclosion ;</li>
            <li>une fiche d'alimentation et de sevrage ;</li>
            <li>son historique de soins ;</li>
            <li>le résultat du sexage ADN, lorsque le test a été réalisé ;</li>
            <li>les documents exigés par la réglementation pour son espèce, le cas échéant ;</li>
            <li>le contrat de vente et les reçus de paiement.</li>
        </ul>
        <p>Nous vous recommandons de faire examiner votre oiseau par un <strong>vétérinaire aviaire</strong> dans les jours qui suivent son arrivée : c'est aussi ce qui permet de faire jouer la garantie.</p>
    </section>

    <section id="garantie">
        <h2>6. Garantie santé</h2>
        <p>La garantie est <strong>écrite dans le contrat de vente</strong>, avec sa durée et son étendue. Elle couvre les problèmes de santé déjà présents au moment de la remise, lorsqu'ils sont constatés par un vétérinaire aviaire dans le délai prévu au contrat.</p>
        <p><strong>Comment la faire jouer :</strong> contactez-nous dès que possible et transmettez-nous le rapport du vétérinaire. Les solutions possibles (remboursement, échange, participation aux frais) sont décrites dans le contrat.</p>
        <p><strong>Ce qu'elle ne couvre pas :</strong> les problèmes causés après la remise par un accident, une alimentation ou un environnement inadaptés, ou un manque de soins.</p>
    </section>

    <section id="contact" class="conditions-contact">
        <h2>7. Nous joindre</h2>
        <p>Pour une question sur un oiseau, une visite, un appel vidéo ou l'organisation d'un transport :</p>
        <address>
            <strong><?= echapper($coord['nom']) ?></strong><br>
            <?php if ($coord['rue'] !== '') : ?><?= echapper($coord['rue']) ?><br><?php endif; ?>
            <?php if ($lieu !== '') : ?><?= echapper(trim($lieu . ' ' . $coord['code_postal'])) ?><br><?php endif; ?>
            <?= echapper(t('pied_pays')) ?><br>
            <?php if ($coord['telephone_lien'] !== '') : ?>
                <a href="tel:<?= echapper($coord['telephone_lien']) ?>"><?= echapper($coord['telephone']) ?></a><br>
            <?php endif; ?>
            <?php if ($coord['courriel'] !== '') : ?>
                <a href="mailto:<?= echapper($coord['courriel']) ?>"><?= echapper($coord['courriel']) ?></a>
            <?php endif; ?>
        </address>
        <a href="<?= echapper($lienOiseaux) ?>" class="btn btn-jungle btn-lg"><?= echapper(t('accueil_voir_oiseaux')) ?></a>
    </section>

</article>

<?php require_once __DIR__ . '/../gabarits/pied.php'; ?>
