<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Récupérer toutes les certifications
$certifications = getAllCertifications($pdo);

// Regrouper les certifications par domaine
$certificationsByDomain = [];
foreach ($certifications as $cert) {
    $certificationsByDomain[$cert['domaine']][] = $cert;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certifications - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <h1>Certifications Disponibles</h1>
            
            <div class="certifications-list">
                <?php foreach ($certificationsByDomain as $domain => $certs): ?>
                    <div class="certification-domain-section">
                        <h2><?= $domain ?></h2>
                        
                        <div class="certification-cards">
                            <?php foreach ($certs as $cert): ?>
                                <div class="certification-card">
                                    <h3><?= $cert['nom_certification'] ?></h3>
                                    <p><?= $cert['description'] ?></p>
                                    
                                    <div class="certification-actions">
                                        <a href="request-training.php?certification_id=<?= $cert['id'] ?>" class="btn">Demander une formation</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
</body>
</html>