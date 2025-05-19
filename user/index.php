<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Récupérer les posts (annonces)
$posts = getPosts($pdo);

// Récupérer les formations en cours et à venir
$upcomingTrainings = getFormations($pdo, 'à venir');
$currentTrainings = getFormations($pdo, 'en cours');

// Récupérer les certifications par domaine
$certificationsByDomain = getCertificationsByDomain($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <h1>Bienvenue, <?= $_SESSION['user_name'] ?></h1>
            
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <h2>Annonces</h2>
                    
                    <?php if (empty($posts)): ?>
                        <p>Aucune annonce pour le moment.</p>
                    <?php else: ?>
                        <div class="posts-container">
                            <?php foreach ($posts as $post): ?>
                                <div class="post-card">
                                    <div class="post-header">
                                        <h3><?= $post['titre'] ?></h3>
                                        <span class="post-date"><?= date('d/m/Y', strtotime($post['date_publication'])) ?></span>
                                    </div>
                                    
                                    <div class="post-content">
                                        <p><?= $post['contenu'] ?></p>
                                    </div>
                                    
                                    <div class="post-footer">
                                        <span class="post-type post-type-<?= $post['type'] ?>">
                                            <?= $post['type'] === 'en cours' ? 'Formation en cours' : 'Formation à venir' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <h2>Formations à Venir</h2>
                    
                    <?php if (empty($upcomingTrainings)): ?>
                        <p>Aucune formation à venir pour le moment.</p>
                    <?php else: ?>
                        <div class="trainings-container">
                            <?php foreach ($upcomingTrainings as $training): ?>
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
                
                <div class="dashboard-section">
                    <h2>Formations en Cours</h2>
                    
                    <?php if (empty($currentTrainings)): ?>
                        <p>Aucune formation en cours pour le moment.</p>
                    <?php else: ?>
                        <div class="trainings-container">
                            <?php foreach ($currentTrainings as $training): ?>
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
                                        <a href="feedback.php?formation_id=<?= $training['id'] ?>" class="btn">Donner un Feedback</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <h2>Certifications Disponibles</h2>
                    
                    <div class="certifications-container">
                        <?php foreach ($certificationsByDomain as $domain): ?>
                            <div class="certification-domain">
                                <h3><?= $domain['domaine'] ?></h3>
                                <p><?= $domain['certifications'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="actions">
                        <a href="certifications.php" class="btn">Voir toutes les certifications</a>
                        <a href="request-training.php" class="btn">Demander une formation</a>
                        <a href="register-certification.php" class="btn">Enregistrer une certification</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
</body>
</html>