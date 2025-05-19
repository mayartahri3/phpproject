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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            // Création d'un nouveau post
            $titre = sanitize($_POST['titre'] ?? '');
            $contenu = sanitize($_POST['contenu'] ?? '');
            $type = $_POST['type'] ?? '';
            
            if (empty($titre) || empty($contenu) || empty($type)) {
                $error = 'Tous les champs sont obligatoires.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO posts (titre, contenu, type) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$titre, $contenu, $type])) {
                    $success = 'Le post a été créé avec succès.';
                } else {
                    $error = 'Une erreur est survenue. Veuillez réessayer.';
                }
            }
        } elseif ($action === 'update') {
            // Mise à jour d'un post existant
            $postId = $_POST['post_id'] ?? 0;
            $titre = sanitize($_POST['titre'] ?? '');
            $contenu = sanitize($_POST['contenu'] ?? '');
            $type = $_POST['type'] ?? '';
            
            if (empty($titre) || empty($contenu) || empty($type)) {
                $error = 'Tous les champs sont obligatoires.';
            } else {
                $stmt = $pdo->prepare("UPDATE posts SET titre = ?, contenu = ?, type = ? WHERE id = ?");
                
                if ($stmt->execute([$titre, $contenu, $type, $postId])) {
                    $success = 'Le post a été mis à jour avec succès.';
                } else {
                    $error = 'Une erreur est survenue. Veuillez réessayer.';
                }
            }
        } elseif ($action === 'delete') {
            // Suppression d'un post
            $postId = $_POST['post_id'] ?? 0;
            
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            
            if ($stmt->execute([$postId])) {
                $success = 'Le post a été supprimé avec succès.';
            } else {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
    }
}

// Récupérer tous les posts
$posts = getPosts($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Posts - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="admin-content">
            <h1>Gestion des Posts</h1>
            
            <?php if ($error): ?>
                <?= showError($error) ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <?= showSuccess($success) ?>
            <?php endif; ?>
            
            <div class="form-section">
                <h2>Créer un Nouveau Post</h2>
                
                <form method="POST" action="" class="form">
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
            
            <div class="data-section">
                <h2>Liste des Posts</h2>
                
                <?php if (empty($posts)): ?>
                    <p>Aucun post trouvé.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Date de Publication</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= $post['titre'] ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $post['type'] ?>">
                                            <?= $post['type'] === 'en cours' ? 'En cours' : 'À venir' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($post['date_publication'])) ?></td>
                                    <td>
                                        <button class="btn btn-small btn-edit" data-id="<?= $post['id'] ?>" data-titre="<?= htmlspecialchars($post['titre']) ?>" data-contenu="<?= htmlspecialchars($post['contenu']) ?>" data-type="<?= $post['type'] ?>">Modifier</button>
                                        
                                        <form method="POST" action="" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Modal pour modifier un post -->
            <div id="edit-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    
                    <h2>Modifier le Post</h2>
                    
                    <form method="POST" action="" class="form">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="post_id" id="edit-post-id">
                        
                        <div class="form-group">
                            <label for="edit-titre">Titre</label>
                            <input type="text" id="edit-titre" name="titre" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-contenu">Contenu</label>
                            <textarea id="edit-contenu" name="contenu" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-type">Type</label>
                            <select id="edit-type" name="type" required>
                                <option value="en cours">Formation en cours</option>
                                <option value="à venir">Formation à venir</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="action" value="update">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>