<?php
// Verifie si une session a defini un message de succes
if (isset($_SESSION['message'])) {

    // Affiche une alerte Bootstrap de type "success"
    echo "<div class='alert alert-success alert-dismissible fade show auto-close' role='alert'>";

    // Affiche le contenu du message stocke en session
    echo $_SESSION["message"];

    // Bouton pour fermer (Bootstrap)
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";

    // Supprime le message de la session pour ne pas reafficher l'alerte
    unset($_SESSION['message']);
}

// Verifie s'il y a une erreur dans la session
if (isset($_SESSION['erreur'])) {

    // Affiche une alerte danger (Bootstrap)
    echo "<div class='alert alert-danger alert-dismissible fade show auto-close' role='alert'>";

    // Affiche le contenu de l'erreur stockee en session
    echo $_SESSION["erreur"];

    // Bouton pour fermer (Bootstrap)
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";

    // Supprime l'erreur de la session pour ne pas reafficher l'alerte
    unset($_SESSION['erreur']);
}
?>


<script>
  // Ferme chaque alert .auto-close après 3000ms
  document.querySelectorAll('.auto-close').forEach(function (el) {
  setTimeout(function () {
      // Utilise l'API Bootstrap 5 pour fermer proprement l'alerte
      var bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      bsAlert.close();
  }, 3000);
  });
</script>