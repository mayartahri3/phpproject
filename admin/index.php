<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Récupérer les statistiques
$stats = [];

// Nombre total d'utilisateurs
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $stmt->fetch()['total'];

// Nombre total de certifications
$stmt = $pdo->query("SELECT COUNT(*) as total FROM certifications");
$stats['total_certifications'] = $stmt->fetch()['total'];

// Nombre total de formations
$stmt = $pdo->query("SELECT COUNT(*) as total FROM formations");
$stats['total_formations'] = $stmt->fetch()['total'];

// Nombre total d'étudiants certifiés
$stmt = $pdo->query("SELECT COUNT(*) as total FROM etudiants_certifies");
$stats['total_certified'] = $stmt->fetch()['total'];

// Nombre de demandes de formation en attente
$stmt = $pdo->query("SELECT COUNT(*) as total FROM demandes_formations WHERE statut = 'en attente'");
$stats['pending_requests'] = $stmt->fetch()['total'];

// Récupérer les dernières demandes de formation
$recentRequests = getTrainingRequests($pdo, 'en attente');

// Récupérer les derniers feedbacks
$recentFeedbacks = getFeedbacks($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="admin-content">
            <h1>Tableau de Bord Administrateur</h1>
            
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Utilisateurs</h3>
                    <p class="stat-number"><?= $stats['total_users'] ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Certifications</h3>
                    <p class="stat-number"><?= $stats['total_certifications'] ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Formations</h3>
                    <p class="stat-number"><?= $stats['total_formations'] ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Étudiants Certifiés</h3>
                    <p class="stat-number"><?= $stats['total_certified'] ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Demandes en Attente</h3>
                    <p class="stat-number"><?= $stats['pending_requests'] ?></p>
                </div>
            </div>
            
            <div class="admin-sections">
                <div class="admin-section">
                    <h2>Dernières Demandes de Formation</h2>
                    
                    <?php if (empty($recentRequests)): ?>
                        <p>Aucune demande en attente.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Certification</th>
                                    <th>Date de Demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentRequests, 0, 5) as $request): ?>
                                    <tr>
                                        <td><?= $request['prenom'] . ' ' . $request['nom'] ?></td>
                                        <td><?= $request['nom_certification'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($request['date_demande'])) ?></td>
                                        <td>
                                            <a href="training-requests.php?action=view&id=<?= $request['id'] ?>" class="btn btn-small">Voir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="view-all">
                            <a href="training-requests.php" class="btn">Voir toutes les demandes</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="admin-section">
                    <h2>Derniers Feedbacks</h2>
                    
                    <?php if (empty($recentFeedbacks)): ?>
                        <p>Aucun feedback pour le moment.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Formation</th>
                                    <th>Note</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentFeedbacks, 0, 5) as $feedback): ?>
                                    <tr>
                                        <td><?= $feedback['prenom'] . ' ' . $feedback['nom'] ?></td>
                                        <td><?= $feedback['nom_certification'] ?></td>
                                        <td><?= $feedback['note'] ?>/5</td>
                                        <td><?= date('d/m/Y', strtotime($feedback['date_feedback'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="view-all">
                            <a href="feedbacks.php" class="btn">Voir tous les feedbacks</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="admin-section">
                <h2>Créer un Nouveau Post</h2>
                
                <form action="posts.php" method="POST" class="form">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label for="titre">Titre</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contenu">Contenu</label>
                        <textarea id="contenu" name="contenu" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="en cours">Formation en cours</option>
                            <option value="à venir">Formation à venir</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="action" value="create">Publier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>