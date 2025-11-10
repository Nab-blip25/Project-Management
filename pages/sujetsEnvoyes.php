<?php
session_start();


if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 3)) {
    $_SESSION['erreur'] = "Accès interdit !";
    header("Location: index.php");
    exit;
}


$titre = "Mes sujets envoyés";
require_once("../includes/param.inc.php");


include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');


$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    echo "<div class='alert alert-danger'>Erreur de connexion à la base de données.</div>";
    include('../includes/footer.inc.php');
    exit;
}


// Requête pour récupérer les projets de l'utilisateur
$sql = "SELECT p.* FROM projects p WHERE p.project_owner = ? ORDER BY p.project_id DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>


<div class="container my-5">
<h1 class="mb-4">Mes sujets envoyés</h1>


<?php if ($result && $result->num_rows > 0): ?>
<div class="accordion" id="accordionMesSujets">
<?php
while ($row = $result->fetch_assoc()):
    $sujetId = $row['project_id'];
    $nom = htmlspecialchars($row['project_name']);
    $desc = nl2br(htmlspecialchars($row['project_description']));
   
    // Statut
    if ($row['project_validated'] == 1) {
        $statutTexte = "Validé";
        $statutBadge = "success";
    } elseif ($row['project_refused'] == 1) {
        $statutTexte = "Refusé";
        $statutBadge = "danger";
    } elseif ($row['modifications_requested'] == 1) {
        $statutTexte = "Modifications demandées";
        $statutBadge = "warning";
    } else {
        $statutTexte = "En attente";
        $statutBadge = "secondary";
    }
   
    // Récupérer les PDFs pour ce projet
    $pdfStmt = $mysqli->prepare("SELECT pdf_id, pdf_name FROM pdf_files WHERE pdf_project = ?");
    $pdfStmt->bind_param("i", $sujetId);
    $pdfStmt->execute();
    $pdfResult = $pdfStmt->get_result();
?>
<div class="accordion-item">
    <h2 class="accordion-header" id="heading<?= $sujetId; ?>">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $sujetId; ?>">
    <?= $nom; ?>
    <span class="badge bg-<?= $statutBadge; ?> ms-2"><?= $statutTexte; ?></span>
    </button>
    </h2>
    <div id="collapse<?= $sujetId; ?>" class="accordion-collapse collapse">
    <div class="accordion-body">
    <div class="row">
    <div class="col-md-4">
    <?php
    if ($row['project_image'] != null) {
        $imgType = !empty($row['project_image_type']) ? $row['project_image_type'] : 'image/jpeg';
        $imageSrc = 'data:' . $imgType . ';base64,' . base64_encode($row['project_image']);
    } else {
        $imageSrc = '../src/default.jpg';
    }
    ?>
    <img src="<?php echo $imageSrc; ?>" alt="<?php echo $nom; ?>" class="img-fluid rounded">
    </div>
    <div class="col-md-8">
    <p><?= $desc; ?></p>
   
    <!-- PDFs -->
    <div class="mb-3">
        <strong>Documents PDF :</strong>
        <?php if ($pdfResult && $pdfResult->num_rows > 0): ?>
            <?php while ($pdf = $pdfResult->fetch_assoc()): ?>
                <div class="mt-2">
                    <a href="../scripts/downloadPdf.php?id=<?= $pdf['pdf_id']; ?>"
                       target="_blank"
                       class="btn btn-sm btn-outline-primary">
                         <?= htmlspecialchars($pdf['pdf_name']); ?>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted mt-2">Aucun document PDF</p>
        <?php endif; ?>
    </div>
   
    <!-- Boutons modification -->
    <?php if ($row['modifications_requested'] == 1): ?>
    <div class="alert alert-warning mt-3">
        Des modifications ont été demandées sur ce sujet.
    </div>
    <a href="modifierSujet.php?id=<?= $sujetId; ?>" class="btn btn-sm modifierSujetBtn">Modifier ce sujet</a>
    <?php endif; ?>
    </div>
    </div>
    </div>
    </div>
</div>
<?php
    $pdfStmt->close();
endwhile;
?>
</div>
<?php else: ?>
<p>Vous n'avez encore soumis aucun sujet.</p>
<?php endif; ?>
</div>


<?php
$stmt->close();
$mysqli->close();
include('../includes/footer.inc.php');
?>
