<?php
session_start();

// Verifier que les champs existent
if (!isset($_POST['email'], $_POST['password'])) {
    $_SESSION['erreur'] = "Veuillez remplir tous les champs.";
    header("Location: ../pages/authentification.php");
    exit;
}

$email = htmlentities($_POST['email']);
$password_userConnexion = $_POST['password'];

// Connexion à la BDD
require_once("../includes/param.inc.php");
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Verifier la connexion
if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Erreur de connexion à la base de données.";
    header("Location: ../pages/authentification.php");
    exit;
}

// Rechercher l'utilisateur
if ($stmt = $mysqli->prepare("SELECT user_id, user_first_name, user_last_name, user_email, user_password, user_role FROM users WHERE user_email = ?")) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Vérifier si le compte est en attente (rôle = 0)
        if ($user['user_role'] == 0) {
            $_SESSION['erreur'] = "Votre compte est en attente de validation par un responsable. Vous serez notifié par email lorsque votre compte sera activé.";
            header("Location: ../pages/authentification.php");
            exit;
        }
        
        // Vérifier si le compte est refusé (rôle = -1)
        if ($user['user_role'] == -1) {
            $_SESSION['erreur'] = "Votre demande de compte a été refusée. Veuillez contacter l'administration pour plus d'informations.";
            header("Location: ../pages/authentification.php");
            exit;
        }
        
        if (password_verify($password_userConnexion, $user['user_password'])) {
            // Stocker les infos en session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nom'] = $user['user_last_name'];
            $_SESSION['prenom'] = $user['user_first_name'];
            $_SESSION['email'] = $user['user_email'];
            $_SESSION['role'] = $user['user_role'];
            $_SESSION['message'] = "Connexion réussie ! Bienvenue " . $user['user_first_name'];
            header("Location: ../pages/index.php");
            exit;
        } else {
            $_SESSION['erreur'] = "Mot de passe incorrect.";
            header("Location: ../pages/authentification.php");
            exit;
        }
    } else {
        $_SESSION['erreur'] = "Adresse email inconnue.";
        header("Location: ../pages/authentification.php");
        exit;
    }
}

$stmt->close();
$mysqli->close();
?>