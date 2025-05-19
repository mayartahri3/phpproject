<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Vérification du jeton CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $requestId = $_POST['request_id'] ?? 0;
        
        if ($_POST['action'] === 'accept') {
            // Accepter la demande
            $stmt = $pdo->prepare("UPDATE demandes_formations SET statut = 'acceptée' WHERE id = ?");
            
            if ($stmt->execute([$requestId])) {
                // Récupérer les informations de la demande
                $stmt = $pdo->prepare("SELECT * FROM demandes_formations WHERE id = ?");
                $stmt->execute([$requestId]);
                $request = $stmt->fetch();
                
                if ($request) {
                    // Créer une nouvelle formation
                    $stmt = $pdo->prepare("INSERT INTO formations (id_certification, formateur, duree, statut, date_debut) 
                                          VALUES (?, 'À déterminer', '4 semaines', 'à venir', DATE_ADD(CURRENT_DATE, INTERVAL 2 WEEK))");
                    
                    if ($stmt->execute([$request['id_certification']])) {
                        $success = 'La demande a été acceptée et une nouvelle formation a été créée.';
                    } else {
                        $error = 'La demande a été acceptée mais une erreur est survenue lors de la création de la formation.';
                    }
                }
            } else {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        } elseif ($_POST['action'] === 'reject') {
            // Rejeter la demande
            $stmt = $pdo->prepare("UPDATE demandes_formations SET statut = 'rejetée' WHERE id = ?");
            
            if ($stmt->execute([$requestId])) {
                $success = 'La demande a été rejetée.';
            } else {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}

// Récupérer les demandes de formation
$requests = getTrainingRequests($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes de Formation - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="admin-content">
            <h1>Gestion des Demandes de Formation</h1>
            
            <?php if ($error): ?>
                <?= showError($error) ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <?= showSuccess($success) ?>
            <?php endif; ?>
            
            <div class="data-section">
                <h2>Liste des Demandes</h2>
                
                <?php if (empty($requests)): ?>
                    <p>Aucune demande de formation trouvée.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Email</th>
                                <th>Certification</th>
                                <th>Date de Demande</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= $request['prenom'] . ' ' . $request['nom'] ?></td>
                                    <td><?= $request['email'] ?></td>
                                    <td><?= $request['nom_certification'] ?> (<?= $request['domaine'] ?>)</td>
                                    <td><?= date('d/m/Y', strtotime($request['date_demande'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $request['statut'] ?>">
                                            <?= ucfirst($request['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($request['statut'] === 'en attente'): ?>
                                            <form method="POST" action="" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <button type="submit" name="action" value="accept" class="btn btn-small btn-success">Accepter</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-small btn-danger">Rejeter</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="action-disabled">Traité</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>