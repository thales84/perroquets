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
