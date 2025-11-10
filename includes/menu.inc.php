<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="mb-2 navbar navbar-expand-md bg-light border-bottom border-body" data-bs-theme="light">
  <div class="container-fluid" id="navbarContainer">
    <!-- Logo -->
    <a class="navbar-brand" href="https://www.esigelec.fr/fr" target="_blank">
    <img src="../src/esigeleclogo_light.png" alt="Logo Esigelec" style="height: 60px;"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarText">
  <!-- Liens de gauche -->
  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item">
    <a class="nav-link" href="../pages/index.php">Accueil</a>
</li>
    <?php if (isset($_SESSION['user_id'])): ?>
    <?php if ($_SESSION['role'] == 2 || $_SESSION['role'] == 3): // Responsable PING ou Admin ?> 
    <li class="nav-item">
    <a class="nav-link" href="../pages/gestionDesRoles.php">Gestion des rôles</a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="../pages/gestionSujet.php">Gestion des sujets</a>
    </li>
    <?php endif; ?>
    <?php if ($_SESSION['role'] == 1 || $_SESSION['role'] == 3): // Tuteur ou Admin ?>
    <li class="nav-item">
    <a class="nav-link" href="../pages/creationSujet.php">Créer un sujet</a>
    </li>
    <li class="nav-item">
    <a class="nav-link" href="../pages/sujetsEnvoyes.php">Mes sujets envoyés</a>
    </li>
    <?php endif; ?>
    <?php endif; ?>
    </ul>

      <!-- Partie droite -->
      <ul class="navbar-nav">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item" id="userNameNavItem"> 
            <span id="userName" class="badge rounded-pill me-2">
              Bonjour, <?= htmlspecialchars($_SESSION['prenom']); ?>
            </span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../scripts/deconnexion.php">Déconnexion</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="../pages/creationCompteTuteur.php">Inscription</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../pages/authentification.php">Connexion</a>
          </li>
        <?php endif; ?>
      </ul>
      <!-- Dark mode affiché pour tous les utilisateurs donc en dehors de la requete pour savoir si on est pas connecte -->
      <button type="button" class="btn" id="darkModeButton">
        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 25 25" id="darkModeIcon">
          <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
        </svg>
      </button>
    </div>
  </div>
</nav>

<div class="container">

<!-- Script JS pour le mode sombre -->

<script>
// Fonction pour activer/désactiver le mode sombre
function setDarkMode(enabled) {
  const nav = document.querySelector('nav.navbar');
  // Sélection du logo de la navbar
  const logo = document.querySelector('.navbar-brand img');
  // Sélection du SVG du bouton darkmode
  const darkModeIcon = document.getElementById('darkModeIcon');
  // Sélectionne l'accordéon sur la page (si présent)
  const accordion = document.getElementById('accordionSujets');
  if (enabled) {
    // Active le mode sombre sur le body
    document.body.classList.add('dark-mode');
    // Stocke le mode sombre dans le localStorage
    localStorage.setItem('darkMode', 'on');
    // Coche le switch
    document.getElementById('darkModeButton').checked = true;
    // Change la navbar en mode sombre
    if (nav) {
      nav.classList.remove('bg-light');
      nav.classList.add('bg-dark');
      nav.setAttribute('data-bs-theme', 'dark');
    }
    // Change le logo pour la version sombre
    if (logo) logo.src = '../src/esigeleclogo_dark.png';
    // Change l'icône du bouton pour la version sombre
    if (darkModeIcon) {
      darkModeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16"><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>';
    }
    // Met l'accordéon en mode sombre si présent
    if (accordion) {
      accordion.classList.add('accordion-dark');
    }
  } else {
    // Désactive le mode sombre sur le body
    document.body.classList.remove('dark-mode');
    // Stocke le mode clair dans le localStorage
    localStorage.setItem('darkMode', 'off');
    // Décoche le switch
    document.getElementById('darkModeButton').checked = false;
    // Change la navbar en mode clair
    if (nav) {
      nav.classList.remove('bg-dark');
      nav.classList.add('bg-light');
      nav.setAttribute('data-bs-theme', 'light');
    }
    // Change le logo pour la version claire
    if (logo) logo.src = '../src/esigeleclogo_light.png';
    // Change l'icône du bouton pour la version claire
    if (darkModeIcon) {
      darkModeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg>';
    }
    // Met l'accordéon en mode clair si présent
    if (accordion) {
      accordion.classList.remove('accordion-dark');
    }
  }
}

// Appliquer le mode sombre au chargement
document.addEventListener('DOMContentLoaded', function() {
  const darkMode = localStorage.getItem('darkMode');
  const button = document.getElementById('darkModeButton');
  const nav = document.querySelector('nav.navbar');
  // Sélection du logo de la navbar
  const logo = document.querySelector('.navbar-brand img');
  // Sélection du SVG du bouton darkmode
  const darkModeIcon = document.getElementById('darkModeIcon');
  // Sélectionne l'accordéon sur la page (si présent)
  const accordion = document.getElementById('accordionSujets');
  if (darkMode === 'on') {
    // Active le mode sombre sur le body
    document.body.classList.add('dark-mode');
    if (button) button.checked = true;
    // Change la navbar en mode sombre
    if (nav) {
      nav.classList.remove('bg-light');
      nav.classList.add('bg-dark');
      nav.setAttribute('data-bs-theme', 'dark');
    }
    // Change le logo pour la version sombre
    if (logo) logo.src = '../src/esigeleclogo_dark.png';
    // Change l'icône du bouton pour la version sombre
    if (darkModeIcon) {
      darkModeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16"><path d="M8 4.5a3.5 3.5 0 1 1 0 7a3.5 3.5 0 0 1 0-7zm0-3a.5.5 0 0 1 .5.5V3a.5.5 0 0 1-1 0V2A.5.5 0 0 1 8 1.5zm0 13a.5.5 0 0 1-.5-.5V13a.5.5 0 0 1 1 0v1.5a.5.5 0 0 1-.5.5zm6.5-6.5a.5.5 0 0 1-.5.5H13a.5.5 0 0 1 0-1h1.5a.5.5 0 0 1 .5.5zm-13 0a.5.5 0 0 1-.5-.5V8a.5.5 0 0 1 1 0v.5a.5.5 0 0 1-.5.5zm10.364-5.364a.5.5 0 0 1 .707 0l1.06 1.06a.5.5 0 1 1-.707.707l-1.06-1.06a.5.5 0 0 1 0-.707zm-8.485 8.485a.5.5 0 0 1 0-.707l1.06-1.06a.5.5 0 1 1 .707.707l-1.06 1.06a.5.5 0 0 1-.707 0zm8.485 0a.5.5 0 0 1 0 .707l-1.06 1.06a.5.5 0 1 1-.707-.707l1.06-1.06a.5.5 0 0 1 .707 0zm-8.485-8.485a.5.5 0 0 1 .707-.707l1.06 1.06a.5.5 0 1 1-.707.707l-1.06-1.06a.5.5 0 0 1 0-.707z"/></svg>';
    }
    // Met l'accordéon en mode sombre si présent
    if (accordion) {
      accordion.classList.add('accordion-dark');
    }
  } else {
    // Désactive le mode sombre sur le body
    document.body.classList.remove('dark-mode');
    if (button) button.checked = false;
    // Change la navbar en mode clair
    if (nav) {
      nav.classList.remove('bg-dark');
      nav.classList.add('bg-light');
      nav.setAttribute('data-bs-theme', 'light');
    }
    // Change le logo pour la version claire
    if (logo) logo.src = '../src/esigeleclogo_light.png';
    // Change l'icône du bouton pour la version claire
    if (darkModeIcon) {
      darkModeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg>';
    }
    // Met l'accordéon en mode clair si présent
    if (accordion) {
      accordion.classList.remove('accordion-dark');
    }
  }
  // Ajoute l'écouteur sur le bouton pour changer de mode
  if (button) {
    button.addEventListener('click', function() {
      // On bascule le mode sombre selon l'état actuel
      setDarkMode(!document.body.classList.contains('dark-mode'));
    });
  }
});
</script>