<?php
session_start();
require_once("../includes/param.inc.php");

// Vérification d'accès
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 3)) {
    $_SESSION['erreur'] = "Accès interdit !";
    header("Location: ../pages/index.php");
    exit;
}

// Vérification des champs requis
if (!isset($_POST['nom'], $_POST['description'], $_POST['nbTeams'], $_POST['confidentiality'])) {
    $_SESSION['erreur'] = "Formulaire incomplet.";
    header("Location: ../pages/creationSujet.php");
    exit;
}

$nom = trim($_POST['nom']);
$description = trim($_POST['description']);
$nbTeams = intval($_POST['nbTeams']);
$confidentiality = intval($_POST['confidentiality']);
$idTuteur = intval($_SESSION['user_id']);

// Connexion
$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Erreur de connexion à la base de données.";
    header("Location: ../pages/creationSujet.php");
    exit;
}

// Log pour voir ce qui se passe
error_log(" DÉBUT CRÉATION SUJET ");
error_log("Nom: $nom, Tuteur: $idTuteur");

// Gestion de l'image
$imageData = null;
$imageType = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 2 * 1024 * 1024;
        if ($_FILES['image']['size'] > $maxSize) {
            $_SESSION['erreur'] = "Image trop volumineuse (max 2 Mo).";
            header("Location: ../pages/creationSujet.php");
            exit;
        }
        
        // Vérification du type MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mime, $allowed, true)) {
            $_SESSION['erreur'] = "Type d'image non autorisé.";
            header("Location: ../pages/creationSujet.php");
            exit;
        }
        
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $imageType = $mime;
        error_log("Image chargée: " . strlen($imageData) . " bytes, type: $imageType");
    } else {
        error_log("Erreur upload image: " . $_FILES['image']['error']);
    }
} else {
    error_log("Aucune image fournie");
}

// Insertion du projet
if ($imageData === null) {
    $stmt = $mysqli->prepare("INSERT INTO projects (project_name, project_description, project_image, project_image_type, project_nb_teams, project_confidentiality, project_owner) VALUES (?, ?, NULL, NULL, ?, ?, ?)");
    $stmt->bind_param("ssiii", $nom, $description, $nbTeams, $confidentiality, $idTuteur);
} else {
    $stmt = $mysqli->prepare("INSERT INTO projects (project_name, project_description, project_image, project_image_type, project_nb_teams, project_confidentiality, project_owner) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    //  Utilisation de bind_param  pour les BLOB
    $null = NULL;
    $stmt->bind_param("ssbsiii", $nom, $description, $null, $imageType, $nbTeams, $confidentiality, $idTuteur);
    $stmt->send_long_data(2, $imageData); // Envoi des données BLOB
}

if ($stmt->execute()) {
    $projectId = $mysqli->insert_id;
    error_log("Project créé avec ID: $projectId");
    $stmt->close();

    //  Gestion du PDF 
    if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
        error_log("PDF détecté, traitement en cours...");
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['pdfFile']['tmp_name']);
        error_log("Type MIME détecté: $mime");
        
        if ($mime === 'application/pdf') {
            $pdfName = $_FILES['pdfFile']['name'];
            $pdfData = file_get_contents($_FILES['pdfFile']['tmp_name']);
            
            if ($pdfData !== false) {
                error_log("PDF lu: " . strlen($pdfData) . " bytes");
                
                // Insertion du PDF
                $pdfStmt = $mysqli->prepare("INSERT INTO pdf_files (pdf_name, pdf_project, pdf_data) VALUES (?, ?, ?)");
                
                if ($pdfStmt) {
                    $pdfNull = NULL;
                    $pdfStmt->bind_param("sib", $pdfName, $projectId, $pdfNull);
                    $pdfStmt->send_long_data(2, $pdfData);
                    
                    if ($pdfStmt->execute()) {
                        $pdfId = $pdfStmt->insert_id;
                        error_log("PDF inséré avec succès! ID: $pdfId");
                        $_SESSION['message'] = "Sujet créé avec succès ! PDF enregistré.";
                    } else {
                        $error = $pdfStmt->error;
                        error_log("Erreur insertion PDF: $error");
                        $_SESSION['erreur'] = "Erreur lors de l'enregistrement du PDF: $error";
                    }
                    $pdfStmt->close();
                } else {
                    $error = $mysqli->error;
                    error_log("Erreur préparation PDF: $error");
                    $_SESSION['erreur'] = "Erreur préparation PDF: $error";
                }
            } else {
                error_log("Impossible de lire le fichier PDF");
                $_SESSION['erreur'] = "Impossible de lire le fichier PDF.";
            }
        } else {
            error_log("Type de fichier non autorisé: $mime");
            $_SESSION['erreur'] = "Seuls les fichiers PDF sont autorisés. Type détecté: $mime";
        }
    } else {
        $pdfError = isset($_FILES['pdfFile']) ? $_FILES['pdfFile']['error'] : 'NO_FILE';
        error_log("Aucun PDF ou erreur upload: $pdfError");
        
        if (!isset($_SESSION['erreur'])) {
            $_SESSION['message'] = "Sujet créé avec succès ! En attente de validation.";
        }
    }
// Succès
} else {
    $error = $stmt->error;
    error_log("Erreur création projet: $error");
    $_SESSION['erreur'] = "Impossible de créer le sujet : $error";
    $stmt->close();
}

// DEBUG: Fin du processus
error_log("=== FIN CRÉATION SUJET ===");
$mysqli->close();

// Redirection
if (isset($_SESSION['erreur'])) {
    header("Location: ../pages/creationSujet.php");
} else {
    header("Location: ../pages/sujetsEnvoyes.php");
}
exit;
?>