<?php
/**
 * Script jetable — générer le hash bcrypt du mot de passe admin.
 * Étapes :
 *   1. Placer ce fichier temporairement dans un dossier accessible par Apache.
 *   2. Ouvrir http://localhost/perroquets/base-de-donnees/generer-hash-admin.php
 *   3. Copier le hash affiché dans donnees-test.sql (colonne mot_de_passe_hash).
 *   4. SUPPRIMER ce fichier du serveur immédiatement après.
 */

// Définir ici le mot de passe souhaité pour le compte admin.
// Ne jamais committer ce fichier avec un mot de passe réel.
$mot_de_passe = 'Francine84';

$hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

echo '<pre>';
echo "Mot de passe (ne pas stocker) : [masqué]\n";
echo "Hash bcrypt à coller dans donnees-test.sql :\n\n";
echo htmlspecialchars($hash, ENT_QUOTES, 'UTF-8');
echo '</pre>';
echo '<p style="color:red;font-weight:bold;">Supprimer ce fichier du serveur immédiatement après utilisation.</p>';
