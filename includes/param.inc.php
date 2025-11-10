<?php
$servername = "localhost";  // ou l'adresse IP du serveur de base de données
$username = "root";
$password = "root";
$dbname = "projet_ping";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}