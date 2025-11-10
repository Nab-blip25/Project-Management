<?php

session_start();

$titre = "Gestion des rôles";

// SUPPRIMER les includes AVANT le traitement PHP
// NE PAS inclure header/menu AVANT les redirections

require_once("../includes/param.inc.php");

// Verification si utilisateur est bien Responsable PING
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 2 && $_SESSION['role'] != 3)) { // CORRECTION : rôle 3 pour admin
    $_SESSION['erreur'] = "Accès interdit ! Vous devez être Responsable PING ou Admin.";
    header("Location: index.php");
    exit;
}

$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    // Stocker l'erreur en session au lieu de l'afficher directement
    $_SESSION['erreur'] = "Erreur de connexion à la base de données.";
    header("Location: index.php");
    exit;
}

// == Si action accept/reject reçue ==
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $userId = intval($_GET['id']);

    if (!in_array($action, ['accept', 'reject'], true)) {
        $_SESSION['erreur'] = "Action invalide.";
        header("Location: gestionDesRoles.php");
        exit;
    }

    // Verifier que l'utilisateur existe et est encore en attente
    $checkStmt = $mysqli->prepare("SELECT user_role FROM users WHERE user_id = ?");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $res = $checkStmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $checkStmt->close();

    if (!$row) {
        $_SESSION['erreur'] = "Utilisateur introuvable.";
    } elseif (intval($row['user_role']) !== 0) {
        $_SESSION['erreur'] = "Impossible : le compte n'est plus en attente.";
    } else {
        $newRole = ($action === 'accept') ? 1 : -1;
        $updateStmt = $mysqli->prepare("UPDATE users SET user_role = ? WHERE user_id = ?");
        $updateStmt->bind_param("ii", $newRole, $userId);
        if ($updateStmt->execute()) {
            $_SESSION['message'] = ($action === 'accept')
            ? "Le compte a été promu au rôle Tuteur."
            : "La demande a été refusée (rôle = -1).";
        } else {
            $_SESSION['erreur'] = "Impossible de modifier le rôle.";
        }
        $updateStmt->close();
    }

    header("Location: gestionDesRoles.php");
    exit;
}

// --- Récupération des comptes en attente ---
$result = $mysqli->query("SELECT user_id, user_last_name, user_first_name, user_email FROM users WHERE user_role = 0 ORDER BY user_last_name");

// MAINTENANT on inclut les fichiers HTML APRÈS tous les traitements PHP
include('../includes/header.inc.php');
include('../includes/menu.inc.php');
include('../includes/message.inc.php');
?>

<div class="container my-5">
  <h1 class="mb-4">Gestion des rôles — Comptes en attente</h1>

  <!-- Liste des comptes en attente -->
  <?php if ($result && $result->num_rows > 0): ?>
    <table class="table table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Email</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['user_last_name']); ?></td>
            <td><?= htmlspecialchars($row['user_first_name']); ?></td>
            <td><?= htmlspecialchars($row['user_email']); ?></td>
            <td>
              <!-- Bouton Accepter -->
              <a href="gestionDesRoles.php?action=accept&id=<?= $row['user_id']; ?>" 
                 class="btn btn-sm btn-outline-success"
                 onclick="return confirm('Accepter ce compte comme Tuteur ?');">
                 Accepter
              </a>
              <!-- Bouton Refuser -->
              <a href="gestionDesRoles.php?action=reject&id=<?= $row['user_id']; ?>" 
                 class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Refuser cette demande ? ');">
                 Refuser
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <!-- Aucune demande en attente -->
    <p>Aucun compte en attente de rôle.</p>
  <?php endif; ?>
</div>

<?php 
$mysqli->close();
include('../includes/footer.inc.php'); 
?>
