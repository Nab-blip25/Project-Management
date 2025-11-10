<?php
session_start();
$titre = "Gestion des sujets";

require_once("../includes/param.inc.php");

// Verification : accès interdit si pas Responsable (role = 2)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 2 && $_SESSION['role'] != 3)) {
    $_SESSION['erreur'] = "Accès interdit !";
    header("Location: index.php");
    exit;
}

// Connexion DB
$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("<div class='alert alert-danger'>Erreur de connexion à la base de données.</div>");
}

// --- Gestion des actions (valider / dévalider / refuser / demander modif / supprimer) ==
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Préparer et exécuter la requête selon l'action
    // Action valider
    if ($action == "valider") {
        $stmt = $mysqli->prepare("UPDATE projects 
            SET project_validated = 1, 
            project_refused = 0, modifications_requested = 0 
            WHERE project_id = ?");
    // Action dévalider
    } elseif ($action == "devalider") {
        $stmt = $mysqli->prepare("UPDATE projects 
            SET project_validated = 0, 
            project_refused = 0, modifications_requested = 0 
            WHERE project_id = ?");
    // Action refuser
    } elseif ($action == "refuser") {
        $stmt = $mysqli->prepare("UPDATE projects 
            SET project_validated = 0, 
            project_refused = 1, modifications_requested = 0 
            WHERE project_id = ?");
    // Action demander modification
    } elseif ($action == "demander_modif") {
        $stmt = $mysqli->prepare("UPDATE projects 
            SET project_validated = 0, 
            project_refused = 0, modifications_requested = 1 
            WHERE project_id = ?");
    // Action supprimer
    } elseif ($action == "supprimer") {
        $stmt = $mysqli->prepare("DELETE FROM projects WHERE project_id = ?");
    }
    
    // Exécution de la requête
    if (isset($stmt)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Action '$action' effectuée avec succès.";
        } else {
            $_SESSION['erreur'] = "Impossible d'appliquer l'action.";
        }
        $stmt->close();
    }
    header("Location: gestionSujet.php");
    exit;
}

// --- Récupération des sujets avec leurs PDFs ==
$sql = "SELECT p.*, u.user_last_name AS nomTuteur, u.user_first_name AS prenomTuteur 
        FROM projects p 
        LEFT JOIN users u ON p.project_owner = u.user_id";

$result = $mysqli->query($sql);

// --- Ici seulement on inclut les fichiers qui produisent du HTML --- 
include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');
?>

<div class="container my-5">
    <h1 class="mb-4">Gestion des sujets</h1>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="accordion" id="accordionGestionSujets">
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $sujetId = $row['project_id'];
        $nom = htmlspecialchars($row['project_name']);
        $desc = nl2br(htmlspecialchars($row['project_description']));

        // Récupérer les PDFs associés à ce sujet
        $pdfSql = "SELECT pdf_id, pdf_name FROM pdf_files WHERE pdf_project = ?";
        $pdfStmt = $mysqli->prepare($pdfSql);
        $pdfStmt->bind_param("i", $sujetId);
        $pdfStmt->execute();
        $pdfResult = $pdfStmt->get_result();

        // Statut
        if ($row['project_validated'] == 1) {
            $statut = "Validé";
            $badge = "success";
        } elseif ($row['project_refused'] == 1) {
            $statut = "Refusé";
            $badge = "danger";
        } elseif ($row['modifications_requested'] == 1) {
            $statut = "Modifications demandées";
            $badge = "warning";
        } else {
            $statut = "En attente";
            $badge = "secondary";
        }
        ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $sujetId; ?>">
                <button class="accordion-button collapsed" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#collapse<?= $sujetId; ?>">
                    <?= $nom; ?>
                    <span class="badge bg-<?= $badge; ?> ms-2"><?= $statut; ?></span>
                </button>
            </h2>
            <div id="collapse<?= $sujetId; ?>" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="row">
                        <!-- Image -->
                        <div class="col-md-4">
                            <?php
                            if ($row['project_image'] != null && $row['project_image'] !== '') {
                                $imgType = !empty($row['project_image_type']) ? $row['project_image_type'] : 'image/jpeg';
                                $imageSrc = 'data:' . htmlspecialchars($imgType, ENT_QUOTES, 'UTF-8') . ';base64,' . base64_encode($row['project_image']);
                            } else {
                                $imageSrc = '../src/default.jpg';
                            }
                            $altText = htmlspecialchars(isset($row['project_name']) ? $row['project_name'] : 'Image du projet', ENT_QUOTES, 'UTF-8');
                            ?>
                            <img src="<?php echo $imageSrc; ?>" alt="<?php echo $altText; ?>" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <p><?= $desc; ?></p>
                            
                            <!-- Section PDFs -->
                            <div class="mb-3">
                                <strong>Documents PDF :</strong>
                                <?php if ($pdfResult && $pdfResult->num_rows > 0): ?>
                                    <div class="mt-2">
                                        <?php while ($pdf = $pdfResult->fetch_assoc()): ?>
                                            <div class="mb-2">
                                                <a href="../scripts/downloadPdf.php?id=<?= $pdf['pdf_id']; ?>" 
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    📄 <?= htmlspecialchars($pdf['pdf_name']); ?>
                                                </a>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mt-2">Aucun document PDF associé</p>
                                <?php endif; ?>
                            </div>

                            <div class="mt-3">
                                <?php if ($row['project_validated'] == 0): ?>
                                    <!-- Actions pour sujets non validés -->
                                    <a href="gestionSujet.php?action=valider&id=<?= $sujetId; ?>" 
                                       class="btn btn-sm btn-success"
                                       onclick="return confirm('Valider ce sujet ?');">
                                        Valider
                                    </a>
                                    <a href="gestionSujet.php?action=demander_modif&id=<?= $sujetId; ?>" 
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Demander des modifications pour ce sujet ?');">
                                        Demander une modification
                                    </a>
                                    <a href="gestionSujet.php?action=refuser&id=<?= $sujetId; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Refuser ce sujet ?');">
                                        Refuser
                                    </a>
                                <?php else: ?>
                                    <!-- Actions pour sujets validés -->
                                    <a href="gestionSujet.php?action=devalider&id=<?= $sujetId; ?>" 
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Dé-valider ce sujet ? Il repassera en statut \\'En attente\\'.');">
                                        Dé-valider
                                    </a>
                                    <a href="gestionSujet.php?action=supprimer&id=<?= $sujetId; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Supprimer définitivement ce sujet ?');">
                                        Supprimer
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <p class="mt-3"><strong>Tuteur :</strong> <?= htmlspecialchars($row['prenomTuteur'] . " " . $row['nomTuteur']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        // Fermer la requête PDF
        $pdfStmt->close();
        ?>
    <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>Aucun sujet trouvé.</p>
<?php endif; ?>
</div>

<?php 
$mysqli->close(); 
include('../includes/footer.inc.php');
?>