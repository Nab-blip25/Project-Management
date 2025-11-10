<?php
session_start(); // Pour stocker les messages

// Récupération du formulaire
$nom = htmlentities($_POST['nom']);
$prenom = htmlentities($_POST['prenom']);
$email = htmlentities($_POST['email']);
$passwordRaw = $_POST['password'];
$role = 0; // 0 : compte non activé

$passwordHash = password_hash($passwordRaw, PASSWORD_DEFAULT);

// Connexion à la BDD
require_once("../includes/param.inc.php");
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données ! ";
    header('Location: ../pages/inscription.php');
    exit;
}

// Vérifier si l'email existe déjà
$stmt_check = $mysqli->prepare("SELECT user_id FROM users WHERE user_email = ?");
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Email déjà utilisé
    $_SESSION['erreur'] = "Cette adresse email est déjà utilisée.";
    header('Location: ../pages/authentification.php');
    exit;
}
$stmt_check->close();

// Insertion du nouvel utilisateur
$stmt = $mysqli->prepare("INSERT INTO users(user_last_name, user_first_name, user_email, user_password, user_role) VALUES (?, ?, ?, ?, ?)"); 
$stmt->bind_param("ssssi", $nom, $prenom, $email, $passwordHash, $role);

if ($stmt->execute()) {
    $_SESSION['message'] = "Enregistrement réussi ! Vous pouvez maintenant vous connecter.";
} else {
    $_SESSION['erreur'] = "Impossible d'enregistrer. Veuillez réessayer.";
}

$stmt->close();
$mysqli->close();

// Redirection vers la page de connexion ou accueil
header('Location: ../pages/authentification.php');
exit;
?>