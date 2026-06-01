<?php
// Fragment inclus par ajouter.php et modifier.php.
// Attend : $valeurs (array), $erreurs (array)
?>
<div class="champ <?= isset($erreurs['nom_commun_fr']) ? 'champ--erreur' : '' ?>">
    <label for="nom_commun_fr">Nom commun (français) <span aria-hidden="true">*</span></label>
    <input type="text" id="nom_commun_fr" name="nom_commun_fr"
           value="<?= echapper($valeurs['nom_commun_fr']) ?>"
           required oninput="autoSlug(this.value)">
    <?php if (isset($erreurs['nom_commun_fr'])) : ?>
        <span class="champ__erreur"><?= echapper($erreurs['nom_commun_fr']) ?></span>
    <?php endif; ?>
</div>

<div class="champ">
    <label for="slug_fr">Slug URL (fr) <span class="texte-discret">— généré automatiquement</span></label>
    <input type="text" id="slug_fr" name="slug_fr"
           value="<?= echapper($valeurs['slug_fr']) ?>"
           pattern="[a-z0-9\-]+"
           title="Minuscules, chiffres et tirets uniquement">
</div>

<div class="champ">
    <label for="nom_scientifique">Nom scientifique</label>
    <input type="text" id="nom_scientifique" name="nom_scientifique"
           value="<?= echapper($valeurs['nom_scientifique']) ?>">
</div>

<div class="champ">
    <label for="famille_fr">Famille (français)</label>
    <input type="text" id="famille_fr" name="famille_fr"
           value="<?= echapper($valeurs['famille_fr']) ?>">
</div>

<div class="champ">
    <label for="description_fr">Description (français)</label>
    <textarea id="description_fr" name="description_fr" rows="5"><?= echapper($valeurs['description_fr']) ?></textarea>
</div>

<!-- Bloc anglais replié par défaut -->
<details class="bloc-en">
    <summary>Version anglaise <span class="texte-discret">(optionnel)</span></summary>
    <div class="bloc-en-contenu">
        <div class="champ">
            <label for="nom_commun_en">Nom commun (anglais)</label>
            <input type="text" id="nom_commun_en" name="nom_commun_en"
                   value="<?= echapper($valeurs['nom_commun_en']) ?>">
        </div>
        <div class="champ">
            <label for="slug_en">Slug URL (en)</label>
            <input type="text" id="slug_en" name="slug_en"
                   value="<?= echapper($valeurs['slug_en']) ?>"
                   pattern="[a-z0-9\-]+">
        </div>
        <div class="champ">
            <label for="famille_en">Famille (anglais)</label>
            <input type="text" id="famille_en" name="famille_en"
                   value="<?= echapper($valeurs['famille_en']) ?>">
        </div>
        <div class="champ">
            <label for="description_en">Description (anglais)</label>
            <textarea id="description_en" name="description_en" rows="5"><?= echapper($valeurs['description_en']) ?></textarea>
        </div>
    </div>
</details>

<script>
// Génère le slug_fr depuis le nom commun si le champ slug est vide ou non modifié manuellement
var slugModifieManuel = <?= json_encode(!empty($valeurs['slug_fr'])) ?>;
var champSlug = document.getElementById('slug_fr');

champSlug.addEventListener('input', function () { slugModifieManuel = true; });

function autoSlug(valeur) {
    if (slugModifieManuel) return;
    // Translittération simplifiée côté JS (le serveur refait la vraie conversion)
    champSlug.value = valeur
        .toLowerCase()
        .normalize('NFD').replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}
</script>
