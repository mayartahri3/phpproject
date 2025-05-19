<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';

// Récupérer toutes les certifications
$certifications = getAllCertifications($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $certificationId = $_POST['certification_id'] ?? '';
        $dateCertification = $_POST['date_certification'] ?? '';
        
        if (empty($certificationId) || empty($dateCertification)) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            // Vérifier si l'utilisateur a déjà enregistré cette certification
            $stmt = $pdo->prepare("SELECT * FROM etudiants_certifies WHERE id_user = ? AND id_certification = ?");
            $stmt->execute([$_SESSION['user_id'], $certificationId]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Vous avez déjà enregistré cette certification.';
            } else {
                // Enregistrer la certification
                $stmt = $pdo->prepare("INSERT INTO etudiants_certifies (id_user, id_certification, date_certification) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $certificationId, $dateCertification])) {
                    $success = 'Votre certification a été enregistrée avec succès.';
                } else {
                    $error = 'Une erreur est survenue. Veuillez réessayer.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrer une Certification - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <h1>Enregistrer une Certification</h1>
            
            <?php if ($error): ?>
                <?= showError($error) ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <?= showSuccess($success) ?>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="" class="form">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label for="certification_id">Certification</label>
                        <select id="certification_id" name="certification_id" required>
                            <option value="">Sélectionnez une certification</option>
                            <?php foreach ($certifications as $cert): ?>
                                <option value="<?= $cert['id'] ?>">
                                    <?= $cert['nom_certification'] ?> (<?= $cert['domaine'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_certification">Date d'obtention</label>
                        <input type="date" id="date_certification" name="date_certification" required max="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
</body>
</html>