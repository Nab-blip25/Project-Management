<?php
session_start();
$titre = "Modifier un sujet";

require_once("../includes/param.inc.php");

// Vérification : accès interdit si pas connecté ou pas Tuteur
if (!isset($_SESSION['user_id']) || (intval($_SESSION['role']) !== 1 && intval($_SESSION['role']) !== 3)) {
    $_SESSION['erreur'] = "Accès interdit !";
    header("Location: index.php");
    exit;
}

// Vérification de l'ID du sujet
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['erreur'] = "Sujet invalide.";
    header("Location: sujetsEnvoyes.php");
    exit;
}
$project_id = intval($_GET['id']);

// Connexion à la base
$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    error_log("Erreur MySQL: " . $mysqli->connect_error);
    $_SESSION['erreur'] = "Erreur de connexion à la base de données.";
    header("Location: sujetsEnvoyes.php");
    exit;
}

// Vérifier si la table pdf_files existe
$checkPdfTable = $mysqli->query("SHOW TABLES LIKE 'pdf_files'");
$hasPdfTable = $checkPdfTable && $checkPdfTable->num_rows > 0;

// Récupérer les informations du sujet
$sql = "SELECT project_name, project_description, project_image, project_image_type 
        FROM projects 
        WHERE project_id = ? AND project_owner = ?";

$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    error_log("MySQL prepare error (SELECT sujet): " . $mysqli->error . " -- SQL: " . $sql);
    $_SESSION['erreur'] = "Erreur interne lors de la récupération du sujet.";
    header("Location: sujetsEnvoyes.php");
    exit;
}

$stmt->bind_param("ii", $project_id, $_SESSION['user_id']);
$stmt->execute();

$result = null;
if (method_exists($stmt, 'get_result')) {
    $result = $stmt->get_result();
} else {
    // fallback pour anciennes versions
    $metaCols = array();
    $meta = $stmt->result_metadata();
    if ($meta) {
        while ($field = $meta->fetch_field()) {
            $metaCols[] = $field->name;
        }
    }
    $row = array();
    $bindParams = array();
    foreach ($metaCols as $col) {
        $bindParams[] = &$row[$col];
    }
    if (!empty($bindParams)) {
        call_user_func_array(array($stmt, 'bind_result'), $bindParams);
        $stmt->fetch();
        $resultRow = $row;
        $result = new ArrayObject();
        $result->rows = array($resultRow);
    }
}

// Récupérer la ligne
$sujet = false;
if ($result instanceof mysqli_result) {
    if ($result->num_rows === 0) {
        $_SESSION['erreur'] = "Sujet introuvable ou non autorisé.";
        header("Location: sujetsEnvoyes.php");
        exit;
    }
    $sujet = $result->fetch_assoc();
} elseif (is_object($result) && isset($result->rows) && count($result->rows) > 0) {
    $sujet = $result->rows[0];
}

if (!$sujet) {
    $_SESSION['erreur'] = "Sujet introuvable ou non autorisé.";
    header("Location: sujetsEnvoyes.php");
    exit;
}

// Récupérer les PDFs existants pour ce sujet
$current_pdfs = array();
if ($hasPdfTable) {
    $pdfSql = "SELECT pdf_id, pdf_name FROM pdf_files WHERE pdf_project = ?";
    $pdfStmt = $mysqli->prepare($pdfSql);
    if ($pdfStmt) {
        $pdfStmt->bind_param("i", $project_id);
        $pdfStmt->execute();
        $pdfResult = $pdfStmt->get_result();
        while ($pdf = $pdfResult->fetch_assoc()) {
            $current_pdfs[] = $pdf;
        }
        $pdfStmt->close();
    }
}

// Valeurs actuelles
$current_image_blob = isset($sujet['project_image']) ? $sujet['project_image'] : '';
$current_image_type = isset($sujet['project_image_type']) ? $sujet['project_image_type'] : null;

// === Traitement du formulaire ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $nom = trim(isset($_POST['nom']) ? $_POST['nom'] : '');
    $desc = trim(isset($_POST['description']) ? $_POST['description'] : '');
    
    // Par défaut on garde le blob existant
    $image_blob = $current_image_blob;
    $image_mime = $current_image_type;

    // --- Upload image (si fourni) ---
    if (isset($_FILES['image']) && !empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $tmpPath = $_FILES['image']['tmp_name'];
        $contents = file_get_contents($tmpPath);
        if ($contents === false) {
            $_SESSION['erreur'] = "Impossible de lire le fichier image uploadé.";
        } else {
            $image_blob = $contents;
            // Déterminer le MIME
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected = finfo_file($finfo, $tmpPath);
                finfo_close($finfo);
                $image_mime = $detected ? $detected : 'image/jpeg';
            } elseif (!empty($_FILES['image']['type'])) {
                $image_mime = $_FILES['image']['type'];
            } else {
                $image_mime = 'image/jpeg';
            }
        }
    }

    // --- Gestion des PDFs ---
    $pdfs_to_delete = isset($_POST['delete_pdfs']) ? $_POST['delete_pdfs'] : array();
    
    // Supprimer les PDFs sélectionnés
    if (!empty($pdfs_to_delete) && $hasPdfTable) {
        foreach ($pdfs_to_delete as $pdf_id) {
            $pdf_id = intval($pdf_id);
            $deleteStmt = $mysqli->prepare("DELETE FROM pdf_files WHERE pdf_id = ? AND pdf_project = ?");
            $deleteStmt->bind_param("ii", $pdf_id, $project_id);
            $deleteStmt->execute();
            $deleteStmt->close();
        }
    }

    // --- Ajouter de nouveaux PDFs ---
    if ($hasPdfTable && isset($_FILES['new_pdfs']) && !empty($_FILES['new_pdfs']['name'][0])) {
        $new_pdfs = $_FILES['new_pdfs'];
        
        for ($i = 0; $i < count($new_pdfs['name']); $i++) {
            if ($new_pdfs['error'][$i] === UPLOAD_ERR_OK && $new_pdfs['type'][$i] === 'application/pdf') {
                $pdf_name = $new_pdfs['name'][$i];
                $pdf_tmp_name = $new_pdfs['tmp_name'][$i];
                $pdf_data = file_get_contents($pdf_tmp_name);
                
                if ($pdf_data !== false) {
                    $insertPdfStmt = $mysqli->prepare("INSERT INTO pdf_files (pdf_name, pdf_project, pdf_data) VALUES (?, ?, ?)");
                    $insertPdfStmt->bind_param("sis", $pdf_name, $project_id, $pdf_data);
                    
                    if (!$insertPdfStmt->execute()) {
                        $_SESSION['erreur'] = "Erreur lors de l'ajout d'un PDF.";
                    }
                    $insertPdfStmt->close();
                }
            }
        }
    }

    // --- Mise à jour du sujet ---
    $updateSql = "UPDATE projects 
                  SET project_name = ?, project_description = ?, project_image = ?, project_image_type = ?, 
                      project_validated = 0, project_refused = 0, modifications_requested = 0
                  WHERE project_id = ? AND project_owner = ?";
    
    $update = $mysqli->prepare($updateSql);
    if ($update === false) {
        error_log("MySQL prepare error (UPDATE): " . $mysqli->error);
        $_SESSION['erreur'] = "Erreur interne lors de la sauvegarde.";
        header("Location: modifierSujet.php?id=" . $project_id);
        exit;
    }

    $dummy = '';
    $update->bind_param("ssbsii", $nom, $desc, $dummy, $image_mime, $project_id, $_SESSION['user_id']);
    
    if ($image_blob !== null && $image_blob !== '') {
        $update->send_long_data(2, $image_blob);
    }

    // Exécuter la mise à jour
    if ($update->execute()) {
        $_SESSION['message'] = "Sujet modifié avec succès. Il sera réévalué par les responsables.";
        header("Location: sujetsEnvoyes.php");
        exit;
    } else {
        error_log("MySQL execute error (UPDATE): " . $update->error);
        $_SESSION['erreur'] = "Erreur lors de la mise à jour du sujet.";
    }
    
    $update->close();
}

// === Inclure le header/menu/message ===
include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');
?>

<div class="container my-5">
  <h1 class="mb-4">Modifier le sujet</h1>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="nom" class="form-label">Nom du sujet</label>
      <input type="text" name="nom" id="nom" class="form-control" value="<?php echo htmlspecialchars($sujet['project_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea name="description" id="description" rows="6" class="form-control" required><?php echo htmlspecialchars($sujet['project_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Image actuelle</label><br>
      <?php
        // Construire une data URI depuis le BLOB stocké
        $imageSrc = '../src/default.jpg';
        if (isset($sujet['project_image']) && $sujet['project_image'] !== null && $sujet['project_image'] !== '') {
            $imgType = !empty($sujet['project_image_type']) ? $sujet['project_image_type'] : 'image/jpeg';
            $base64 = base64_encode($sujet['project_image']);
            if ($base64 !== false && $base64 !== '') {
                $imageSrc = 'data:' . htmlspecialchars($imgType, ENT_QUOTES, 'UTF-8') . ';base64,' . $base64;
            }
        }
        $altText = htmlspecialchars(isset($sujet['project_name']) ? $sujet['project_name'] : 'Image du projet', ENT_QUOTES, 'UTF-8');
      ?>
      <img src="<?php echo $imageSrc; ?>" alt="<?php echo $altText; ?>" class="img-fluid rounded mb-2" style="max-width:400px; max-height: 400px;">
      <input type="file" name="image" accept="image/*" class="form-control mt-2">
      <small class="form-text text-muted">Uploader une nouvelle image remplace l'image stockée en BLOB.</small>
    </div>

    <!-- Section PDFs -->
    <?php if ($hasPdfTable): ?>
    <div class="mb-3">
      <label class="form-label">PDFs actuels</label><br>
      <?php if (!empty($current_pdfs)): ?>
        <div class="mb-3">
          <?php foreach ($current_pdfs as $pdf): ?>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="delete_pdfs[]" value="<?= $pdf['pdf_id'] ?>" id="pdf_<?= $pdf['pdf_id'] ?>">
              <label class="form-check-label" for="pdf_<?= $pdf['pdf_id'] ?>">
                <a href="../scripts/downloadPdf.php?id=<?= $pdf['pdf_id'] ?>" target="_blank" class="text-decoration-none">
                  📄 <?= htmlspecialchars($pdf['pdf_name']) ?>
                </a>
              </label>
            </div>
          <?php endforeach; ?>
          <small class="text-muted">Cocher pour supprimer le PDF sélectionné</small>
        </div>
      <?php else: ?>
        <p class="text-muted">Aucun PDF associé à ce sujet</p>
      <?php endif; ?>
      
      <label class="form-label">Ajouter de nouveaux PDFs</label>
      <input type="file" name="new_pdfs[]" class="form-control" accept="application/pdf" multiple>
      <small class="form-text text-muted">Vous pouvez sélectionner 1 fichier PDF</small>
    </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    <a href="sujetsEnvoyes.php" class="btn btn-secondary">Annuler</a>
  </form>
</div>

<?php
// cleanup
if (isset($stmt) && $stmt) { $stmt->close(); }
$mysqli->close();
include('../includes/footer.inc.php');
?>