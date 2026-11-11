<?php
// Fragment partagé par ajouter.php et modifier.php : lecture + validation du POST.
// Attend : $valeurs (array) ; remplit $valeurs et $erreurs.

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
        $erreurs['_global'] = 'Jeton de sécurité invalide. Réessayez.';
    } else {
        foreach (CHAMPS_TEMOIGNAGE as $cle) {
            $valeurs[$cle] = trim((string) ($_POST[$cle] ?? ''));
        }
        $valeurs['visible'] = isset($_POST['visible']) ? 1 : 0;
        $valeurs['ordre']   = (int) $valeurs['ordre'];
        // Guillemets de citation retirés : ajoutés à l'affichage.
        $valeurs['texte_fr'] = trim($valeurs['texte_fr'], " \t\n\r«»\"“”");
        $valeurs['texte_en'] = trim($valeurs['texte_en'], " \t\n\r«»\"“”");

        if ($valeurs['auteur'] === '') {
            $erreurs['auteur'] = 'Ce champ est requis.';
        }
        if ($valeurs['texte_fr'] === '') {
            $erreurs['texte_fr'] = 'Ce champ est requis.';
        }
        if (!array_key_exists($valeurs['province'], provincesCanada())) {
            $valeurs['province'] = '';
        }
    }
}
