<?php
session_start();
$titre = "Creation d'un sujet";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 3)) {
    $_SESSION['erreur'] = "Accès interdit ! Seuls les Tuteurs ou Admins peuvent créer un sujet.";
    header("Location: index.php");
    exit;
}

// Maintenant on peut inclure les fichiers
include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');
require_once("../includes/param.inc.php");
?>

<div class="container my-5">
  <h1 class="mb-4">Creation d'un nouveau sujet</h1>

  <!-- Formulaire de creation de sujet -->
  <form method="POST" action="../scripts/ttCreationSujet.php" enctype="multipart/form-data">

    <!-- Titre du sujet -->
    <div class="mb-3">
      <label for="nom" class="form-label">Titre du sujet</label>
      <input type="text" class="form-control" id="nom" name="nom" required>
    </div>

    <!-- Description du sujet -->
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
    </div>

    <!-- Document PDF (optionnel) -->
    <div class="mb-3">
      <label for="pdfFile" class="form-label">Document PDF (optionnel)</label>
      <input class="form-control" type="file" id="pdfFile" name="pdfFile" accept="application/pdf">
      <small class="text-muted">Ce document sera visible lors de la consultation du sujet.</small>
    </div>

    <!-- Image (optionnel) -->
    <div class="mb-3">
      <label for="image" class="form-label">Image (optionnel)</label>
      <input type="file" class="form-control" id="image" name="image" accept="image/*">
    </div>

    <!-- Nombre d'équipes -->
    <div class="mb-3">
      <label for="nbTeams" class="form-label">Nombre d'équipes</label>
      <input type="number" class="form-control" id="nbTeams" name="nbTeams" min="1" required>
    </div>

    <!-- Confidentialité -->
    <div class="mb-3">
      <label for="confidentiality" class="form-label">Confidentialité</label>
      <select class="form-select" id="confidentiality" name="confidentiality" required>
        <option value="0" selected>Public</option>
        <option value="1">Confidentiel</option>
      </select>
    </div>

    <!-- Bouton de soumission -->
    <button type="submit" class="btn btn-outline-primary">Créer le sujet</button>
  </form>
</div>

<?php
include('../includes/footer.inc.php');
?>