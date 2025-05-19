<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Inclure les fichiers nécessaires
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Test des fonctions d'authentification</h1>";

// Afficher les informations de session
echo "<h2>Informations de session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Tester les fonctions d'authentification
echo "<h2>Test des fonctions</h2>";
echo "isLoggedIn(): " . (isLoggedIn() ? "Vrai" : "Faux") . "<br>";
echo "isAdmin(): " . (isAdmin() ? "Vrai" : "Faux") . "<br>";

// Tester la connexion à la base de données
echo "<h2>Test de la base de données</h2>";
try {
    // Vérifier la connexion
    echo "Connexion à la base de données: Réussie<br>";
    
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "Nombre total d'utilisateurs: " . $result['total'] . "<br>";
    
    // Compter les administrateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $result = $stmt->fetch();
    echo "Nombre d'administrateurs: " . $result['total'] . "<br>";
    
    // Lister les administrateurs
    echo "<h3>Liste des administrateurs</h3>";
    $stmt = $pdo->query("SELECT id, nom, prenom, email, role FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    
    if (count($admins) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th></tr>";
        
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['id'] . "</td>";
            echo "<td>" . $admin['nom'] . "</td>";
            echo "<td>" . $admin['prenom'] . "</td>";
            echo "<td>" . $admin['email'] . "</td>";
            echo "<td>" . $admin['role'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "Aucun administrateur trouvé.";
    }
    
} catch (PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage();
}

// Formulaire de connexion rapide
echo "<h2>Connexion rapide</h2>";
echo "<form method='post' action='auth/login.php'>";
echo "<select name='email'>";

$stmt = $pdo->query("SELECT id, nom, prenom, email, role FROM users ORDER BY role DESC, nom");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    echo "<option value='" . $user['email'] . "'>" . $user['email'] . " (" . $user['role'] . ")</option>";
}

echo "</select>";
echo "<input type='hidden' name='password' value='admin123'>";
echo "<button type='submit'>Se connecter</button>";
echo "</form>";

// Lien pour réinitialiser le mot de passe
echo "<h2>Actions</h2>";
echo "<a href='reset_admin.php' style='padding: 10px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 10px;'>Réinitialiser le mot de passe admin</a>";
echo "<a href='create_admin.php' style='padding: 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>Créer un nouvel admin</a>";
?>