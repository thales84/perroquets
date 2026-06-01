<?php
// Fragment partagé ajouter.php / modifier.php
// Attend : $especes, $valeurs, $erreurs
?>
<div class="champ <?= isset($erreurs['id_espece']) ? 'champ--erreur' : '' ?>">
    <label for="id_espece">Espèce <span aria-hidden="true">*</span></label>
    <select id="id_espece" name="id_espece" required>
        <option value="">— Choisir —</option>
        <?php foreach ($especes as $e) : ?>
            <option value="<?= (int)$e['id_espece'] ?>"
                <?= (string)$valeurs['id_espece'] === (string)$e['id_espece'] ? 'selected' : '' ?>>
                <?= echapper($e['nom_commun_fr']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($erreurs['id_espece'])) : ?>
        <span class="champ__erreur"><?= echapper($erreurs['id_espece']) ?></span>
    <?php endif; ?>
</div>

<div class="champ">
    <label for="sexe">Sexe</label>
    <select id="sexe" name="sexe">
        <option value="inconnu" <?= $valeurs['sexe'] === 'inconnu' ? 'selected' : '' ?>>Non déterminé</option>
        <option value="male"    <?= $valeurs['sexe'] === 'male'    ? 'selected' : '' ?>>Mâle</option>
        <option value="femelle" <?= $valeurs['sexe'] === 'femelle' ? 'selected' : '' ?>>Femelle</option>
    </select>
</div>

<div class="champ">
    <label for="date_naissance">Date de naissance</label>
    <input type="date" id="date_naissance" name="date_naissance"
           value="<?= echapper($valeurs['date_naissance']) ?>">
</div>

<div class="champ">
    <label for="num_bague">Numéro de bague</label>
    <input type="text" id="num_bague" name="num_bague"
           value="<?= echapper($valeurs['num_bague']) ?>" maxlength="60">
</div>

<div class="champ">
    <label for="prix_cad">Prix (CAD)</label>
    <input type="number" id="prix_cad" name="prix_cad"
           value="<?= echapper($valeurs['prix_cad']) ?>"
           min="0" step="0.01" placeholder="Laisser vide = Prix sur demande">
</div>

<div class="champ">
    <label class="champ-checkbox">
        <input type="checkbox" name="sevre_main" value="1"
               <?= !empty($valeurs['sevre_main']) ? 'checked' : '' ?>>
        Sevré à la main (EAM)
    </label>
</div>

<div class="champ">
    <label for="statut">Statut</label>
    <select id="statut" name="statut">
        <option value="disponible" <?= $valeurs['statut'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
        <option value="reserve"    <?= $valeurs['statut'] === 'reserve'    ? 'selected' : '' ?>>Réservé</option>
        <option value="vendu"      <?= $valeurs['statut'] === 'vendu'      ? 'selected' : '' ?>>Vendu</option>
    </select>
</div>

<div class="champ">
    <label for="description_fr">Description (français)</label>
    <textarea id="description_fr" name="description_fr" rows="5"><?= echapper($valeurs['description_fr']) ?></textarea>
</div>

<details class="bloc-en">
    <summary>Version anglaise <span class="texte-discret">(optionnel)</span></summary>
    <div class="bloc-en-contenu">
        <div class="champ">
            <label for="description_en">Description (anglais)</label>
            <textarea id="description_en" name="description_en" rows="5"><?= echapper($valeurs['description_en']) ?></textarea>
        </div>
    </div>
</details>
