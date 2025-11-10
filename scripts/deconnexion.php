<?php
session_start(); // Démarre la session

// Supprime uniquement les variables liées à l'utilisateur
unset($_SESSION['user_id']);
unset($_SESSION['nom']);
unset($_SESSION['prenom']);
unset($_SESSION['email']);
unset($_SESSION['role']);

// Optionnel : message de succès pour l'utilisateur
$_SESSION['message'] = "Vous êtes déconnecté avec succès ";

// Redirection vers la page d'accueil ou de connexion
header("Location: ../pages/index.php");
exit;
?>
