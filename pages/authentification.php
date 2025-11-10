<?php
session_start();
$titre = "Connexion";

include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');

?>

<h1>Connexion à votre compte</h1>
<!-- Formulaire de connexion -->
<form method="POST" action="../scripts/ttConnexion.php">
    <!-- Colonnes Bootstrap -->
    <div class="row my-3">
        <!-- Colonne de gauche -->
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Votre email..." required>
        </div>
        <!-- Colonne de droite -->
        <div class="col-md-6">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe..." required>
        </div>
        <a class="nav-link" href="creationCompteTuteur.php">Pas de compte? Cliquez ici</a>
    </div>
    <!-- Bouton de connexion -->
    <div class="row my-3">
        <div class="d-grid d-md-block">
            <button class="btn btn-outline-primary" type="submit">Connexion</button>
        </div>
    </div>
</form>

<?php
include('../includes/footer.inc.php');
?>