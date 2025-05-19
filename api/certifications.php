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
    // Récupérer toutes les certifications ou une certification spécifique
    if (isset($_GET['id'])) {
        $certificationId = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM certifications WHERE id = ?");
        $stmt->execute([$certificationId]);
        $certification = $stmt->fetch();
        
        if ($certification) {
            header('Content-Type: application/json');
            echo json_encode($certification);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Certification non trouvée']);
        }
    } else {
        // Récupérer toutes les certifications, éventuellement filtrées par domaine
        $domain = $_GET['domain'] ?? null;
        
        if ($domain) {
            $stmt = $pdo->prepare("SELECT * FROM certifications WHERE domaine = ? ORDER BY nom_certification");
            $stmt->execute([$domain]);
        } else {
            $stmt = $pdo->query("SELECT * FROM certifications ORDER BY domaine, nom_certification");
        }
        
        $certifications = $stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode($certifications);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    // Ajouter une nouvelle certification (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['nom_certification']) || !isset($data['domaine'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $nom = sanitize($data['nom_certification']);
    $domaine = sanitize($data['domaine']);
    $description = sanitize($data['description'] ?? '');
    
    $stmt = $pdo->prepare("INSERT INTO certifications (nom_certification, domaine, description) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$nom, $domaine, $description])) {
        $newId = $pdo->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $newId,
            'message' => 'Certification ajoutée avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de l\'ajout de la certification']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && isAdmin()) {
    // Mettre à jour une certification existante (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    $nom = sanitize($data['nom_certification'] ?? null);
    $domaine = sanitize($data['domaine'] ?? null);
    $description = sanitize($data['description'] ?? null);
    
    $updates = [];
    $params = [];
    
    if ($nom !== null) {
        $updates[] = "nom_certification = ?";
        $params[] = $nom;
    }
    
    if ($domaine !== null) {
        $updates[] = "domaine = ?";
        $params[] = $domaine;
    }
    
    if ($description !== null) {
        $updates[] = "description = ?";
        $params[] = $description;
    }
    
    if (empty($updates)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
        exit;
    }
    
    $params[] = $id;
    
    $stmt = $pdo->prepare("UPDATE certifications SET " . implode(", ", $updates) . " WHERE id = ?");
    
    if ($stmt->execute($params)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Certification mise à jour avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la mise à jour de la certification']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isAdmin()) {
    // Supprimer une certification (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    
    $stmt = $pdo->prepare("DELETE FROM certifications WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Certification supprimée avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la suppression de la certification']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>