<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';
$selectedCertification = $_GET['certification_id'] ?? '';

// Récupérer toutes les certifications
$certifications = getAllCertifications($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $certificationId = $_POST['certification_id'] ?? '';
        
        if (empty($certificationId)) {
            $error = 'Veuillez sélectionner une certification.';
        } else {
            // Vérifier si l'utilisateur a déjà fait une demande pour cette certification
            $stmt = $pdo->prepare("SELECT * FROM demandes_formations WHERE id_user = ? AND id_certification = ? AND statut = 'en attente'");
            $stmt->execute([$_SESSION['user_id'], $certificationId]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Vous avez déjà fait une demande pour cette certification.';
            } else {
                // Créer la demande
                $stmt = $pdo->prepare("INSERT INTO demandes_formations (id_user, id_certification) VALUES (?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $certificationId])) {
                    $success = 'Votre demande a été envoyée avec succès. Vous serez notifié lorsqu\'elle sera traitée.';
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
    <title>Demande de Formation - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <h1>Demande de Formation</h1>
            
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
                                <option value="<?= $cert['id'] ?>" <?= ($selectedCertification == $cert['id']) ? 'selected' : '' ?>>
                                    <?= $cert['nom_certification'] ?> (<?= $cert['domaine'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">Envoyer la demande</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
</body>
</html>