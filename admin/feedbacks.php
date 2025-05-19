<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Récupérer tous les feedbacks
$feedbacks = getFeedbacks($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedbacks - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="admin-content">
            <h1>Gestion des Feedbacks</h1>
            
            <div class="data-section">
                <h2>Liste des Feedbacks</h2>
                
                <?php if (empty($feedbacks)): ?>
                    <p>Aucun feedback trouvé.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Formation</th>
                                <th>Formateur</th>
                                <th>Note</th>
                                <th>Date</th>
                                <th>Commentaire</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?= $feedback['prenom'] . ' ' . $feedback['nom'] ?></td>
                                    <td><?= $feedback['nom_certification'] ?></td>
                                    <td><?= $feedback['formateur'] ?></td>
                                    <td><?= $feedback['note'] ?>/5</td>
                                    <td><?= date('d/m/Y', strtotime($feedback['date_feedback'])) ?></td>
                                    <td><?= $feedback['commentaire'] ?></td>
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