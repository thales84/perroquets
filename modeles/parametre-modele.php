<?php
/**
 * Modèle des paramètres du site (SEO, réseaux sociaux, analytics).
 * Stockage clé/valeur dans la table `parametre`.
 */
require_once __DIR__ . '/../configuration/connexion.php';

/**
 * Charge tous les paramètres une seule fois par requête (cache statique).
 * Tolérant : si la table n'existe pas encore (migration non jouée),
 * retourne un tableau vide → les valeurs par défaut prennent le relais.
 *
 * @return array<string,string>
 */
function chargerParametres(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    try {
        $pdo = obtenirConnexion();
        $lignes = $pdo->query("SELECT cle, valeur FROM parametre")->fetchAll();
        foreach ($lignes as $ligne) {
            $cache[$ligne['cle']] = $ligne['valeur'];
        }
    } catch (Throwable $e) {
        // Table absente ou BD indisponible : on reste sur les défauts.
        $cache = [];
    }
    return $cache;
}

/**
 * Récupère un paramètre, avec valeur par défaut si vide ou absent.
 */
function param(string $cle, string $defaut = ''): string
{
    $params = chargerParametres();
    return (isset($params[$cle]) && $params[$cle] !== '') ? $params[$cle] : $defaut;
}

/**
 * Provinces et territoires canadiens (code postal officiel => nom français).
 *
 * @return array<string,string>
 */
function provincesCanada(): array
{
    return [
        'AB' => 'Alberta',
        'BC' => 'Colombie-Britannique',
        'PE' => 'Île-du-Prince-Édouard',
        'MB' => 'Manitoba',
        'NB' => 'Nouveau-Brunswick',
        'NS' => 'Nouvelle-Écosse',
        'NU' => 'Nunavut',
        'ON' => 'Ontario',
        'QC' => 'Québec',
        'SK' => 'Saskatchewan',
        'NL' => 'Terre-Neuve-et-Labrador',
        'NT' => 'Territoires du Nord-Ouest',
        'YT' => 'Yukon',
    ];
}

/**
 * Coordonnées publiques de l'entreprise (NAP : nom, adresse, téléphone),
 * gérées dans l'admin (Paramètres > Contact). Valeurs brutes, non échappées.
 *
 * @return array<string,string>
 */
function coordonneesEntreprise(): array
{
    $provinces = provincesCanada();
    $codeProv  = param('entreprise_province');
    $telephone = param('entreprise_telephone');

    return [
        'nom'           => param('site_nom', 'Maple Perroquets'),
        'rue'           => param('entreprise_rue'),
        'ville'         => param('entreprise_ville'),
        'province'      => $codeProv,
        'province_nom'  => $provinces[$codeProv] ?? '',
        'code_postal'   => param('entreprise_code_postal'),
        'telephone'     => $telephone,
        // Lien tel: — chiffres et « + » initial uniquement.
        'telephone_lien'=> preg_replace('/(?!^\+)[^\d]/', '', $telephone) ?? '',
        'courriel'      => param('entreprise_courriel'),
        'annee'         => param('entreprise_annee_creation'),
    ];
}

/**
 * Enregistre (insert ou update) un lot de paramètres.
 *
 * @param array<string,string> $valeurs
 */
function enregistrerParametres(array $valeurs): void
{
    $pdo = obtenirConnexion();
    $req = $pdo->prepare(
        "INSERT INTO parametre (cle, valeur) VALUES (:cle, :valeur)
         ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)"
    );
    foreach ($valeurs as $cle => $valeur) {
        $req->execute([':cle' => $cle, ':valeur' => (string) $valeur]);
    }
}
