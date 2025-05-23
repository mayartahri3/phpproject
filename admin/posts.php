<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';

// Récupérer tous les formateurs pour la liste déroulante
function getFormateurs($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nom FROM formateurs ORDER BY nom ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching formateurs: " . $e->getMessage());
        return [];
    }
}

// Fonction modifiée pour récupérer les posts avec les noms des formateurs
function getPostsWithFormateurs($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, f.nom as formateur_nom 
            FROM posts p 
            LEFT JOIN formateurs f ON p.formateur_id = f.id 
            ORDER BY p.date_publication DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching posts: " . $e->getMessage());
        return [];
    }
}

$formateurs = getFormateurs($pdo);

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            // Création d'un nouveau post
            $titre = sanitize($_POST['titre'] ?? '');
            $contenu = sanitize($_POST['contenu'] ?? '');
            $type = $_POST['type'] ?? '';
            $formateurId = $_POST['formateur_id'] ?? null;
            
            if (empty($titre) || empty($contenu) || empty($type) || empty($formateurId)) {
                $error = 'Tous les champs sont obligatoires.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO posts (titre, contenu, type, formateur_id) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$titre, $contenu, $type, $formateurId])) {
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
            $formateurId = $_POST['formateur_id'] ?? null;
            
            if (empty($titre) || empty($contenu) || empty($type) || empty($formateurId)) {
                $error = 'Tous les champs sont obligatoires.';
            } else {
                $stmt = $pdo->prepare("UPDATE posts SET titre = ?, contenu = ?, type = ?, formateur_id = ? WHERE id = ?");
                
                if ($stmt->execute([$titre, $contenu, $type, $formateurId, $postId])) {
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

// Récupérer tous les posts avec les formateurs
$posts = getPostsWithFormateurs($pdo);
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
                            <option value="">Sélectionnez un type</option>
                            <option value="en cours">Formation en cours</option>
                            <option value="à venir">Formation à venir</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="formateur_id">Formateur</label>
                        <select id="formateur_id" name="formateur_id" required>
                            <option value="">Sélectionnez un formateur</option>
                            <?php foreach ($formateurs as $formateur): ?>
                                <option value="<?= $formateur['id'] ?>"><?= htmlspecialchars($formateur['nom']) ?></option>
                            <?php endforeach; ?>
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
                                <th>Formateur</th>
                                <th>Date de Publication</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= htmlspecialchars($post['titre']) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= str_replace(' ', '-', $post['type']) ?>">
                                            <?= $post['type'] === 'en cours' ? 'En cours' : 'À venir' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($post['formateur_nom'] ?? 'Non assigné') ?></td>
                                    <td><?= date('d/m/Y', strtotime($post['date_publication'])) ?></td>
                                    <td>
                                        <button class="btn btn-small btn-edit" 
                                                data-id="<?= $post['id'] ?>" 
                                                data-titre="<?= htmlspecialchars($post['titre']) ?>" 
                                                data-contenu="<?= htmlspecialchars($post['contenu']) ?>" 
                                                data-type="<?= $post['type'] ?>"
                                                data-formateur="<?= $post['formateur_id'] ?>">
                                            Modifier
                                        </button>
                                        
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
                            <label for="edit-formateur">Formateur</label>
                            <select id="edit-formateur" name="formateur_id" required>
                                <option value="">Sélectionnez un formateur</option>
                                <?php foreach ($formateurs as $formateur): ?>
                                    <option value="<?= $formateur['id'] ?>"><?= htmlspecialchars($formateur['nom']) ?></option>
                                <?php endforeach; ?>
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
    
    <script>
        // Enhanced admin.js functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('edit-modal');
            const closeBtn = document.querySelector('.close');
            const editButtons = document.querySelectorAll('.btn-edit');
            
            // Open modal when edit button is clicked
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const titre = this.getAttribute('data-titre');
                    const contenu = this.getAttribute('data-contenu');
                    const type = this.getAttribute('data-type');
                    const formateurId = this.getAttribute('data-formateur');
                    
                    document.getElementById('edit-post-id').value = id;
                    document.getElementById('edit-titre').value = titre;
                    document.getElementById('edit-contenu').value = contenu;
                    document.getElementById('edit-type').value = type;
                    document.getElementById('edit-formateur').value = formateurId;
                    
                    modal.style.display = 'block';
                });
            });
            
            // Close modal when close button is clicked
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Close modal when clicking outside of it
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>