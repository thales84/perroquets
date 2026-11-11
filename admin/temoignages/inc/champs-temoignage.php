<?php
// Fragment inclus par ajouter.php et modifier.php.
// Attend : $valeurs (array), $erreurs (array)
?>
<div class="champ <?= isset($erreurs['auteur']) ? 'champ--erreur' : '' ?>">
    <label for="auteur">Nom du client <span aria-hidden="true">*</span></label>
    <input type="text" id="auteur" name="auteur" value="<?= echapper($valeurs['auteur']) ?>" maxlength="120" required>
    <small class="texte-discret">Avec l'accord du client. Un prénom + initiale (ex. « Sophie T. ») suffit.</small>
    <?php if (isset($erreurs['auteur'])) : ?>
        <span class="champ__erreur"><?= echapper($erreurs['auteur']) ?></span>
    <?php endif; ?>
</div>

<div class="champ">
    <label for="ville">Ville</label>
    <input type="text" id="ville" name="ville" value="<?= echapper($valeurs['ville']) ?>" maxlength="80">
</div>

<div class="champ">
    <label for="province">Province / territoire</label>
    <select id="province" name="province">
        <option value="">—</option>
        <?php foreach (provincesCanada() as $code => $nomProv) : ?>
            <option value="<?= echapper($code) ?>" <?= $valeurs['province'] === $code ? 'selected' : '' ?>><?= echapper($nomProv) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="champ">
    <label for="titre_fr">Titre (français)</label>
    <input type="text" id="titre_fr" name="titre_fr" value="<?= echapper($valeurs['titre_fr']) ?>" maxlength="120" placeholder="Une excellente expérience">
</div>

<div class="champ <?= isset($erreurs['texte_fr']) ? 'champ--erreur' : '' ?>">
    <label for="texte_fr">Témoignage (français) <span aria-hidden="true">*</span></label>
    <textarea id="texte_fr" name="texte_fr" rows="5" maxlength="2000" required><?= echapper($valeurs['texte_fr']) ?></textarea>
    <small class="texte-discret">Sans guillemets : ils sont ajoutés automatiquement à l'affichage.</small>
    <?php if (isset($erreurs['texte_fr'])) : ?>
        <span class="champ__erreur"><?= echapper($erreurs['texte_fr']) ?></span>
    <?php endif; ?>
</div>

<div class="champ">
    <label class="champ-bascule">
        <input type="checkbox" name="visible" value="1" <?= !empty($valeurs['visible']) ? 'checked' : '' ?>>
        Publié sur la page d'accueil
    </label>
</div>

<div class="champ">
    <label for="ordre">Ordre d'affichage</label>
    <input type="number" id="ordre" name="ordre" value="<?= (int) $valeurs['ordre'] ?>" step="1">
    <small class="texte-discret">Les plus petits nombres s'affichent en premier. À égalité : le plus récent d'abord.</small>
</div>

<details class="bloc-en">
    <summary>Version anglaise <span class="texte-discret">(optionnel)</span></summary>
    <div class="bloc-en-contenu">
    <div class="champ">
        <label for="titre_en">Titre (anglais)</label>
        <input type="text" id="titre_en" name="titre_en" value="<?= echapper($valeurs['titre_en']) ?>" maxlength="120">
    </div>
    <div class="champ">
        <label for="texte_en">Témoignage (anglais)</label>
        <textarea id="texte_en" name="texte_en" rows="5" maxlength="2000"><?= echapper($valeurs['texte_en']) ?></textarea>
    </div>
    </div>
</details>
