<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Traitement des requêtes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Récupérer tous les posts ou un post spécifique
    if (isset($_GET['id'])) {
        $postId = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch();
        
        if ($post) {
            header('Content-Type: application/json');
            echo json_encode($post);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Post non trouvé']);
        }
    } else {
        // Récupérer tous les posts, éventuellement filtrés par type
        $type = $_GET['type'] ?? null;
        
        if ($type) {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE type = ? ORDER BY date_publication DESC");
            $stmt->execute([$type]);
        } else {
            $stmt = $pdo->query("SELECT * FROM posts ORDER BY date_publication DESC");
        }
        
        $posts = $stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode($posts);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    // Ajouter un nouveau post (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['titre']) || !isset($data['contenu']) || !isset($data['type'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $titre = sanitize($data['titre']);
    $contenu = sanitize($data['contenu']);
    $type = sanitize($data['type']);
    
    $stmt = $pdo->prepare("INSERT INTO posts (titre, contenu, type) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$titre, $contenu, $type])) {
        $newId = $pdo->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $newId,
            'message' => 'Post ajouté avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de l\'ajout du post']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && isAdmin()) {
    // Mettre à jour un post existant (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    $titre = isset($data['titre']) ? sanitize($data['titre']) : null;
    $contenu = isset($data['contenu']) ? sanitize($data['contenu']) : null;
    $type = isset($data['type']) ? sanitize($data['type']) : null;
    
    $updates = [];
    $params = [];
    
    if ($titre !== null) {
        $updates[] = "titre = ?";
        $params[] = $titre;
    }
    
    if ($contenu !== null) {
        $updates[] = "contenu = ?";
        $params[] = $contenu;
    }
    
    if ($type !== null) {
        $updates[] = "type = ?";
        $params[] = $type;
    }
    
    if (empty($updates)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
        exit;
    }
    
    $params[] = $id;
    
    $stmt = $pdo->prepare("UPDATE posts SET " . implode(", ", $updates) . " WHERE id = ?");
    
    if ($stmt->execute($params)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Post mis à jour avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la mise à jour du post']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isAdmin()) {
    // Supprimer un post (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Post supprimé avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la suppression du post']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>