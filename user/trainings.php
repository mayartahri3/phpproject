<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'register') {
    $formationId = $_GET['id'] ?? 0;
    
    if ($formationId) {
        // Vérifier si l'utilisateur est déjà inscrit à cette formation
        $stmt = $pdo->prepare("SELECT * FROM inscriptions_formations WHERE id_user = ? AND id_formation = ?");
        $stmt->execute([$_SESSION['user_id'], $formationId]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Vous êtes déjà inscrit à cette formation.';
        } else {
            // Inscrire l'utilisateur à la formation
            $stmt = $pdo->prepare("INSERT INTO inscriptions_formations (id_user, id_formation) VALUES (?, ?)");
            
            if ($stmt->execute([$_SESSION['user_id'], $formationId])) {
                $success = 'Vous avez été inscrit à la formation avec succès.';
            } else {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}

// Récupérer les formations auxquelles l'utilisateur est inscrit
$stmt = $pdo->prepare("
    SELECT f.*, c.nom_certification, c.domaine 
    FROM inscriptions_formations i 
    JOIN formations f ON i.id_formation = f.id 
    JOIN certifications c ON f.id_certification = c.id 
    WHERE i.id_user = ? 
    ORDER BY f.date_debut DESC
");
$stmt->execute([$_SESSION['user_id']]);
$userTrainings = $stmt->fetchAll();

// Récupérer les formations disponibles (à venir)
$availableTrainings = getFormations($pdo, 'à venir');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Formations - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <h1>Mes Formations</h1>
            
            <?php if ($error): ?>
                <?= showError($error) ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <?= showSuccess($success) ?>
            <?php endif; ?>
            
            <div class="trainings-section">
                <h2>Formations auxquelles je suis inscrit</h2>
                
                <?php if (empty($userTrainings)): ?>
                    <p>Vous n'êtes inscrit à aucune formation pour le moment.</p>
                <?php else: ?>
                    <div class="trainings-container">
                        <?php foreach ($userTrainings as $training): ?>
                            <div class="training-card">
                                <div class="training-header">
                                    <h3><?= $training['nom_certification'] ?></h3>
                                    <span class="training-domain"><?= $training['domaine'] ?></span>
                                </div>
                                
                                <div class="training-details">
                                    <p><strong>Formateur:</strong> <?= $training['formateur'] ?></p>
                                    <p><strong>Durée:</strong> <?= $training['duree'] ?></p>
                                    <p><strong>Date de début:</strong> <?= date('d/m/Y', strtotime($training['date_debut'])) ?></p>
                                    <p><strong>Statut:</strong> <?= ucfirst($training['statut']) ?></p>
                                </div>
                                
                                <div class="training-footer">
                                    <?php if ($training['statut'] === 'en cours'): ?>
                                        <a href="feedback.php?formation_id=<?= $training['id'] ?>" class="btn">Donner un Feedback</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="trainings-section">
                <h2>Formations disponibles</h2>
                
                <?php if (empty($availableTrainings)): ?>
                    <p>Aucune formation disponible pour le moment.</p>
                <?php else: ?>
                    <div class="trainings-container">
                        <?php foreach ($availableTrainings as $training): ?>
                            <div class="training-card">
                                <div class="training-header">
                                    <h3><?= $training['nom_certification'] ?></h3>
                                    <span class="training-domain"><?= $training['domaine'] ?></span>
                                </div>
                                
                                <div class="training-details">
                                    <p><strong>Formateur:</strong> <?= $training['formateur'] ?></p>
                                    <p><strong>Durée:</strong> <?= $training['duree'] ?></p>
                                    <p><strong>Date de début:</strong> <?= date('d/m/Y', strtotime($training['date_debut'])) ?></p>
                                </div>
                                
                                <div class="training-footer">
                                    <a href="trainings.php?action=register&id=<?= $training['id'] ?>" class="btn">S'inscrire</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
</body>
</html>