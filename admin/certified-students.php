<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';
$filters = [];

// Traitement des filtres
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filter'])) {
    if (!empty($_GET['certification'])) {
        $filters['certification'] = $_GET['certification'];
    }
    
    if (!empty($_GET['date_debut']) && !empty($_GET['date_fin'])) {
        $filters['date_debut'] = $_GET['date_debut'];
        $filters['date_fin'] = $_GET['date_fin'];
    }
}

// Récupérer les étudiants certifiés avec les filtres
$certifiedStudents = getCertifiedStudents($pdo, $filters);

// Récupérer toutes les certifications pour le filtre
$certifications = getAllCertifications($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudiants Certifiés - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="admin-content">
            <h1>Gestion des Étudiants Certifiés</h1>
            
            <?php if ($error): ?>
                <?= showError($error) ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <?= showSuccess($success) ?>
            <?php endif; ?>
            
            <div class="filter-section">
                <h2>Filtrer les Résultats</h2>
                
                <form method="GET" action="" class="filter-form">
                    <input type="hidden" name="filter" value="1">
                    
                    <div class="form-group">
                        <label for="certification">Certification</label>
                        <select id="certification" name="certification">
                            <option value="">Toutes les certifications</option>
                            <?php foreach ($certifications as $cert): ?>
                                <option value="<?= $cert['id'] ?>" <?= (isset($filters['certification']) && $filters['certification'] == $cert['id']) ? 'selected' : '' ?>>
                                    <?= $cert['nom_certification'] ?> (<?= $cert['domaine'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_debut">Date de début</label>
                            <input type="date" id="date_debut" name="date_debut" value="<?= $filters['date_debut'] ?? '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin">Date de fin</label>
                            <input type="date" id="date_fin" name="date_fin" value="<?= $filters['date_fin'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">Filtrer</button>
                        <a href="certified-students.php" class="btn btn-secondary">Réinitialiser</a>
                    </div>
                </form>
            </div>
            
            <div class="data-section">
                <h2>Liste des Étudiants Certifiés</h2>
                
                <?php if (empty($certifiedStudents)): ?>
                    <p>Aucun étudiant certifié trouvé.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Certification</th>
                                <th>Domaine</th>
                                <th>Date de Certification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certifiedStudents as $student): ?>
                                <tr>
                                    <td><?= $student['prenom'] . ' ' . $student['nom'] ?></td>
                                    <td><?= $student['email'] ?></td>
                                    <td><?= $student['nom_certification'] ?></td>
                                    <td><?= $student['domaine'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($student['date_certification'])) ?></td>
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