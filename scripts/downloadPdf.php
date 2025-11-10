<?php
session_start();
require_once("../includes/param.inc.php");

// Verifier l'ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("ID PDF invalide.");
}

$pdfId = intval($_GET['id']);

// Connexion à la base
$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données.");
}

// Initialiser les variables de session si l'utilisateur n'est pas connecté
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 0;

// Récupérer le PDF avec vérification des droits
$sql = "SELECT pdf.pdf_name, pdf.pdf_data, p.project_owner, p.project_validated, p.project_confidentiality
    FROM pdf_files pdf
    JOIN projects p ON pdf.pdf_project = p.project_id
    WHERE pdf.pdf_id = ? 
    AND (
        p.project_owner = ? 
        OR ? = 3 
        OR ? = 2 
        OR (p.project_validated = 1 AND p.project_confidentiality = 0)
    )";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("iiii", $pdfId, $user_id, $user_role, $user_role);
$stmt->execute();
$result = $stmt->get_result();
$pdf = $result->fetch_assoc();

if (!$pdf) {
    die("PDF non trouvé ou accès non autorisé.");
}

// Verifier que les données PDF ne sont pas vides
if (empty($pdf['pdf_data'])) {
    die("Le PDF est vide.");
}

// Envoyer le PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'. htmlspecialchars($pdf['pdf_name']). '"');
header('Content-Length: ' . strlen($pdf['pdf_data']));
echo $pdf['pdf_data'];

$stmt->close();
$mysqli->close();
exit;
?>