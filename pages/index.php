<?php
session_start();
$titre = "Accueil";

include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');
require_once("../includes/param.inc.php");
?>



<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil</title>
</head>
<body>

<div class="container my-5">
  <div class="row">
    <!-- Partie texte -->
    <div class="col-lg-6">
      <h2>Présentation de l'ESIGELEC</h2>
      <p>
        L'ESIGELEC est une école d'ingénieurs reconnue, formant des étudiants dans de nombreux domaines de l'ingénierie et de la recherche.
      </p>

      <h2>Présentation du PING</h2>
      <p>
        Le Projet d'Ingénierie de Niveau Graduation (PING) est un projet pluridisciplinaire permettant aux étudiants de travailler en équipe sur des problématiques concrètes proposées par des tuteurs.
      </p>
    </div>

    <!-- Partie vidéo -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center">
      <video width="100%" style="max-width: 100%; height: auto;" playsinline loop muted autoplay>
        <source src="../src/VideoPromo2025.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la lecture de vidéos HTML5.
      </video>
    </div>
  </div>
</div>

<hr>

<!-- Section liste des sujets publics -->
<div class="container my-5">
    <h2 class="mb-4">Sujets PING disponibles</h2>

    <?php
    // Connexion à la BDD
    $mysqli = new mysqli($servername, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        echo "<div class='alert alert-danger'>Problème de connexion à la base de données.</div>";
    } else {
        // SELECT incluant project_image_type et jointure pour récupérer les PDFs
        $sql = "
            SELECT 
                p.project_id,
                p.project_name,
                p.project_description,
                p.project_image,
                p.project_image_type,
                p.project_nb_teams,
                p.project_confidentiality,
                p.project_owner,
                u.user_first_name,
                u.user_last_name
            FROM projects p
            LEFT JOIN users u ON p.project_owner = u.user_id
            WHERE p.project_validated = 1
            AND p.project_confidentiality = 0
        ";

        $result = $mysqli->query($sql);

        if ($result && $result->num_rows > 0) {
            echo '<div class="accordion" id="accordionSujets">';
            while ($row = $result->fetch_assoc()) {
                $sujetId = $row['project_id'];
                $nom = $row['project_name'];
                $desc = nl2br(htmlspecialchars($row['project_description']));
                $nbEquipes = (int)$row['project_nb_teams'];
                $confidentialite = (int)$row['project_confidentiality'];
                $proprietaire = (int)$row['project_owner'];
                $tuteurNom = htmlspecialchars($row['user_first_name'] . ' ' . $row['user_last_name']);
                
                // Récupérer les PDFs associés à ce sujet
                $pdfSql = "SELECT pdf_id, pdf_name FROM pdf_files WHERE pdf_project = ?";
                $pdfStmt = $mysqli->prepare($pdfSql);
                $pdfStmt->bind_param("i", $sujetId);
                $pdfStmt->execute();
                $pdfResult = $pdfStmt->get_result();

                // Conversion BLOB -> data URI : vérification explicite
                if ($row['project_image'] !== null) {
                    // fallback MIME si absent
                    $imgType = !empty($row['project_image_type']) ? $row['project_image_type'] : 'image/jpeg';
                    // Assurer que $row['project_image'] content bien des données binaires
                    $imgData = $row['project_image'];
                    // base64 encode (attention à la taille)
                    $base64 = base64_encode($imgData);
                    $imageSrc = 'data:' . htmlspecialchars($imgType, ENT_QUOTES, 'UTF-8') . ';base64,' . $base64;
                } else {
                    $imageSrc = '../src/default.jpg';
                }
                // alt sécurisé
                $altText = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $sujetId; ?>">
                        <button class="accordion-button collapsed d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse<?php echo $sujetId; ?>" aria-expanded="false" aria-controls="collapse<?php echo $sujetId; ?>">
                            <span><?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span style="flex:1"></span>
                            <span class="badge rounded-pill badgeNbEquipes">Nombre d'équipes : <?php echo $nbEquipes; ?></span>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $sujetId; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $sujetId; ?>" data-bs-parent="#accordionSujets">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo $altText; ?>" class="img-fluid rounded">
                                </div>
                                <div class="col-md-8">
                                    <p><?php echo $desc; ?></p>
                                    
                                    <!-- Section PDFs sur la page d'accueil -->
                                    <div class="mb-3">
                                        <strong>Documents associés :</strong>
                                        <?php if ($pdfResult && $pdfResult->num_rows > 0): ?>
                                            <div class="mt-2">
                                                <?php while ($pdf = $pdfResult->fetch_assoc()): ?>
                                                    <div class="mb-2">
                                                        <a href="../scripts/downloadPdf.php?id=<?= $pdf['pdf_id']; ?>" 
                                                           target="_blank"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <?= htmlspecialchars($pdf['pdf_name']); ?>
                                                        </a>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted mt-2">Aucun document associé</p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Information du tuteur -->
                                    <div class="mt-3">
                                        <p class="mb-1"><strong>Tuteur :</strong> <?php echo $tuteurNom; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                // Fermer la requête PDF
                $pdfStmt->close();
            } // end while
            echo '</div>'; // accordion
        } else {
            echo "<p>Aucun sujet disponible pour le moment.</p>";
        }

        $mysqli->close();
    }
    ?>
</div>

<!-- Footer -->
<?php include('../includes/footer.inc.php'); ?>

</body>
</html>
