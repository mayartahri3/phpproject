<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $nom = sanitize($_POST['nom'] ?? '');
        $prenom = sanitize($_POST['prenom'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
        if ($email !== $user['email'] && emailExists($pdo, $email, $userId)) {
            $error = 'Cet email est déjà utilisé par un autre compte.';
        } else {
            // Préparer les données à mettre à jour
            $updateData = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email
            ];
            
            // Si l'utilisateur souhaite changer son mot de passe
            if (!empty($currentPassword) && !empty($newPassword)) {
                // Vérifier que le mot de passe actuel est correct
                if (!verifyPassword($currentPassword, $user['password'])) {
                    $error = 'Le mot de passe actuel est incorrect.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Les nouveaux mots de passe ne correspondent pas.';
                } elseif (strlen($newPassword) < 8) {
                    $error = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
                } else {
                    $updateData['password'] = hashPassword($newPassword);
                }
            }
            
            // Si pas d'erreur, mettre à jour le profil
            if (empty($error)) {
                $fields = [];
                $values = [];
                
                foreach ($updateData as $field => $value) {
                    $fields[] = "$field = ?";
                    $values[] = $value;
                }
                
                $values[] = $userId;
                
                $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
                
                if ($stmt->execute($values)) {
                    $success = 'Votre profil a été mis à jour avec succès.';
                    
                    // Mettre à jour les informations de session
                    $_SESSION['user_nom'] = $nom;
                    $_SESSION['user_prenom'] = $prenom;
                    $_SESSION['user_email'] = $email;
                    
                    // Récupérer les informations mises à jour
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
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
    <title>Mon Profil - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #0066cc;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin-right: 20px;
        }
        
        .profile-info h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .profile-info p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .password-section {
            border-top: 1px solid #eee;
            margin-top: 20px;
            padding-top: 20px;
        }
        
        .password-section h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <div class="profile-container">
                <h1>Mon Profil</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <div class="profile-section">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?>
                        </div>
                        <div class="profile-info">
                            <h2><?= $user['prenom'] . ' ' . $user['nom'] ?></h2>
                            <p><?= $user['email'] ?></p>
                        </div>
                    </div>
                    
                    <form method="POST" action="" class="form">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prenom">Prénom</label>
                                <input type="text" id="prenom" name="prenom" value="<?= $user['prenom'] ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" value="<?= $user['nom'] ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= $user['email'] ?>" required>
                        </div>
                        
                        <div class="password-section">
                            <h3>Changer le mot de passe</h3>
                            <p>Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe.</p>
                            
                            <div class="form-group">
                                <label for="current_password">Mot de passe actuel</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">Nouveau mot de passe</label>
                                    <input type="password" id="new_password" name="new_password">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
</body>
</html>