<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Vérifier si l'utilisateur est un administrateur pour certaines opérations
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && !isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

// Traitement des requêtes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Les utilisateurs normaux ne peuvent voir que leur propre profil
    if (!isAdmin() && (!isset($_GET['id']) || $_GET['id'] != $_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }
    
    // Récupérer un utilisateur spécifique ou tous les utilisateurs (admin uniquement)
    if (isset($_GET['id'])) {
        $userId = $_GET['id'];
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role, date_inscription FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            header('Content-Type: application/json');
            echo json_encode($user);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Utilisateur non trouvé']);
        }
    } else if (isAdmin()) {
        // Seul l'admin peut lister tous les utilisateurs
        $stmt = $pdo->query("SELECT id, nom, prenom, email, role, date_inscription FROM users ORDER BY date_inscription DESC");
        $users = $stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode($users);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    // Ajouter un nouvel utilisateur (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['nom']) || !isset($data['prenom']) || !isset($data['email']) || !isset($data['mot_de_passe'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $nom = sanitize($data['nom']);
    $prenom = sanitize($data['prenom']);
    $email = sanitize($data['email']);
    $password = $data['mot_de_passe'];
    $role = sanitize($data['role'] ?? 'user');
    
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Cet email est déjà utilisé']);
        exit;
    }
    
    // Hachage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$nom, $prenom, $email, $hashedPassword, $role])) {
        $newId = $pdo->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $newId,
            'message' => 'Utilisateur ajouté avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de l\'ajout de l\'utilisateur']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && isAdmin()) {
    // Mettre à jour un utilisateur existant (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    $nom = isset($data['nom']) ? sanitize($data['nom']) : null;
    $prenom = isset($data['prenom']) ? sanitize($data['prenom']) : null;
    $email = isset($data['email']) ? sanitize($data['email']) : null;
    $role = isset($data['role']) ? sanitize($data['role']) : null;
    $password = $data['mot_de_passe'] ?? null;
    
    $updates = [];
    $params = [];
    
    if ($nom !== null) {
        $updates[] = "nom = ?";
        $params[] = $nom;
    }
    
    if ($prenom !== null) {
        $updates[] = "prenom = ?";
        $params[] = $prenom;
    }
    
    if ($email !== null) {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        
        if ($stmt->rowCount() > 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cet email est déjà utilisé par un autre utilisateur']);
            exit;
        }
        
        $updates[] = "email = ?";
        $params[] = $email;
    }
    
    if ($role !== null) {
        $updates[] = "role = ?";
        $params[] = $role;
    }
    
    if ($password !== null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updates[] = "mot_de_passe = ?";
        $params[] = $hashedPassword;
    }
    
    if (empty($updates)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
        exit;
    }
    
    $params[] = $id;
    
    $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?");
    
    if ($stmt->execute($params)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la mise à jour de l\'utilisateur']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isAdmin()) {
    // Supprimer un utilisateur (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    
    // Empêcher la suppression de son propre compte
    if ($id == $_SESSION['user_id']) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la suppression de l\'utilisateur']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>