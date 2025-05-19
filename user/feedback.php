<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';
$formationId = $_GET['formation_id'] ?? '';

// Récupérer les formations
$formations = getFormations($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $formationId = $_POST['formation_id'] ?? '';
        $note = $_POST['note'] ?? '';
        $commentaire = sanitize($_POST['commentaire'] ?? '');
        
        if (empty($formationId) || empty($note) || empty($commentaire)) {
            $error = 'Tous les champs sont obligatoires.';
        } elseif (!is_numeric($note) || $note < 1 || $note > 5) {
            $error = 'La note doit être comprise entre 1 et 5.';
        } else {
            // Vérifier si l'utilisateur a déjà donné un feedback pour cette formation
            $stmt = $pdo->prepare("SELECT * FROM feedbacks WHERE id_user = ? AND id_formation = ?");
            $stmt->execute([$_SESSION['user_id'], $formationId]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Vous avez déjà donné un feedback pour cette formation.';
            } else {
                // Enregistrer le feedback
                $stmt = $pdo->prepare("INSERT INTO feedbacks (id_user, id_formation, commentaire, note) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $formationId, $commentaire, $note])) {
                    $success = 'Votre feedback a été enregistré avec succès.';
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
    <title>Donner un Feedback - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <h1>Donner un Feedback</h1>
            
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
                        <label for="formation_id">Formation</label>
                        <select id="formation_id" name="formation_id" required>
                            <option value="">Sélectionnez une formation</option>
                            <?php foreach ($formations as $formation): ?>
                                <option value="<?= $formation['id'] ?>" <?= ($formationId == $formation['id']) ? 'selected' : '' ?>>
                                    <?= $formation['nom_certification'] ?> (Formateur: <?= $formation['formateur'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="note">Note (1-5)</label>
                        <select id="note" name="note" required>
                            <option value="">Sélectionnez une note</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Très bien</option>
                            <option value="3">3 - Bien</option>
                            <option value="2">2 - Moyen</option>
                            <option value="1">1 - À améliorer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="commentaire">Commentaire</label>
                        <textarea id="commentaire" name="commentaire" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
</body>
</html>