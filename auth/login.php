<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fichiers nécessaires
require_once '../config/database.php';
require_once '../includes/functions.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/index.php');
        exit;
    } else {
        header('Location: ../user/index.php');
        exit;
    }
}

$error = '';
$success = '';
$debug_info = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation des champs
    if (empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        // Vérification des identifiants
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Informations de débogage
        $debug_info .= "Email saisi: " . htmlspecialchars($email) . "<br>";
        $debug_info .= "Utilisateur trouvé dans la base: " . ($user ? "Oui" : "Non") . "<br>";
        
        if ($user) {
            $debug_info .= "ID utilisateur: " . $user['id'] . "<br>";
            $debug_info .= "Rôle utilisateur: " . $user['role'] . "<br>";
            
            // Vérification du mot de passe
            $password_match = password_verify($password, $user['mot_de_passe']);
            $debug_info .= "Mot de passe correct: " . ($password_match ? "Oui" : "Non") . "<br>";
            
            if ($password_match) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nom'] . ' ' . $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                $debug_info .= "Session créée avec succès<br>";
                $debug_info .= "Session user_id: " . $_SESSION['user_id'] . "<br>";
                $debug_info .= "Session user_role: " . $_SESSION['user_role'] . "<br>";
                
                // Redirection selon le rôle
                if ($user['role'] === 'admin') {
                    $debug_info .= "Redirection vers l'interface admin...";
                    header('Location: ../admin/index.php');
                    exit;
                } else {
                    $debug_info .= "Redirection vers l'interface utilisateur...";
                    header('Location: ../user/index.php');
                    exit;
                }
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .debug-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .debug-info h3 {
            margin-top: 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>Connexion</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit">Se connecter</button>
                </div>
                
                <div class="form-footer">
                    <p>Vous n'avez pas de compte ? <a href="register.php">S'inscrire</a></p>
                </div>
            </form>
            
            <?php if (!empty($debug_info) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="debug-info">
                    <h3>Informations de débogage</h3>
                    <?= $debug_info ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>