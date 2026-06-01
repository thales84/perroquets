<?php
require_once __DIR__ . '/config.php';

// Connexion PDO unique (singleton simple).
$_connexionPdo = null;

function obtenirConnexion(): PDO
{
    global $_connexionPdo;

    if ($_connexionPdo !== null) {
        return $_connexionPdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        BD_HOTE,
        BD_NOM
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $_connexionPdo = new PDO($dsn, BD_UTILISATEUR, BD_MOT_DE_PASSE, $options);
    } catch (PDOException $e) {
        if (MODE_DEVELOPPEMENT) {
            throw $e;
        }
        // En production : journaliser discrètement, afficher message générique.
        error_log('Erreur de connexion PDO : ' . $e->getMessage());
        http_response_code(503);
        exit('Service temporairement indisponible. Veuillez réessayer plus tard.');
    }

    return $_connexionPdo;
}
