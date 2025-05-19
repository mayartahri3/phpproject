<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'tekup_certifications';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Réinitialisation de l'accès administrateur</h1>";
    
    // 1. Vérifier si l'administrateur existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute(['nouvel.admin@tekup.tn']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>✅ Compte administrateur trouvé (ID: " . $admin['id'] . ")</p>";
        
        // 2. Réinitialiser le mot de passe
        $new_password = 'admin123';
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
        $result = $stmt->execute([$hashed_password, $admin['id']]);
        
        if ($result) {
            echo "<p>✅ Mot de passe réinitialisé avec succès</p>";
        } else {
            echo "<p>❌ Erreur lors de la réinitialisation du mot de passe</p>";
        }
    } else {
        echo "<p>❌ Compte administrateur non trouvé</p>";
        
        // 3. Créer un nouvel administrateur
        echo "<p>Création d'un nouvel administrateur...</p>";
        
        $nom = 'Admin';
        $prenom = 'Tekup';
        $email = 'nouvel.admin@tekup.tn';
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'admin')");
        $result = $stmt->execute([$nom, $prenom, $email, $hashed_password]);
        
        if ($result) {
            echo "<p>✅ Nouvel administrateur créé avec succès</p>";
        } else {
            echo "<p>❌ Erreur lors de la création de l'administrateur</p>";
        }
    }
    
    // 4. Vérifier les sessions
    echo "<h2>Vérification des sessions</h2>";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Session path: " . session_save_path() . "</p>";
    
    // Tester l'écriture de session
    $_SESSION['test'] = 'Test de session';
    echo "<p>Test d'écriture de session: " . ($_SESSION['test'] === 'Test de session' ? "Réussi" : "Échoué") . "</p>";
    
    // 5. Vérifier les fonctions d'authentification
    echo "<h2>Vérification des fonctions d'authentification</h2>";
    
    // Inclure les fonctions
    if (file_exists('includes/functions.php')) {
        require_once 'includes/functions.php';
        echo "<p>✅ Fichier functions.php trouvé et inclus</p>";
        
        // Tester les fonctions
        if (function_exists('isLoggedIn')) {
            echo "<p>✅ Fonction isLoggedIn() existe</p>";
        } else {
            echo "<p>❌ Fonction isLoggedIn() n'existe pas</p>";
        }
        
        if (function_exists('isAdmin')) {
            echo "<p>✅ Fonction isAdmin() existe</p>";
        } else {
            echo "<p>❌ Fonction isAdmin() n'existe pas</p>";
        }
    } else {
        echo "<p>❌ Fichier functions.php non trouvé</p>";
    }
    
    // 6. Résumé
    echo "<h2>Résumé</h2>";
    echo "<p><strong>Email administrateur:</strong> nouvel.admin@tekup.tn</p>";
    echo "<p><strong>Mot de passe:</strong> admin123</p>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='auth/login.php' style='padding: 10px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 4px;'>Aller à la page de connexion</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2>Erreur de connexion</h2>";
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
}
?>