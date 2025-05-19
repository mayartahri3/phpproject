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
    // Récupérer toutes les formations ou une formation spécifique
    if (isset($_GET['id'])) {
        $formationId = $_GET['id'];
        
        $stmt = $pdo->prepare("
            SELECT f.*, c.nom_certification, c.domaine 
            FROM formations f 
            JOIN certifications c ON f.id_certification = c.id 
            WHERE f.id = ?
        ");
        $stmt->execute([$formationId]);
        $formation = $stmt->fetch();
        
        if ($formation) {
            // Récupérer les inscriptions pour cette formation
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_inscriptions 
                FROM inscriptions_formations 
                WHERE id_formation = ?
            ");
            $stmt->execute([$formationId]);
            $inscriptions = $stmt->fetch();
            
            $formation['total_inscriptions'] = $inscriptions['total_inscriptions'];
            
            header('Content-Type: application/json');
            echo json_encode($formation);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Formation non trouvée']);
        }
    } else {
        // Récupérer toutes les formations, éventuellement filtrées par statut
        $statut = $_GET['statut'] ?? null;
        
        $sql = "
            SELECT f.*, c.nom_certification, c.domaine 
            FROM formations f 
            JOIN certifications c ON f.id_certification = c.id
        ";
        
        if ($statut) {
            $sql .= " WHERE f.statut = ?";
            $stmt = $pdo->prepare($sql . " ORDER BY f.date_debut DESC");
            $stmt->execute([$statut]);
        } else {
            $stmt = $pdo->query($sql . " ORDER BY f.date_debut DESC");
        }
        
        $formations = $stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode($formations);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    // Ajouter une nouvelle formation (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id_certification']) || !isset($data['formateur']) || !isset($data['duree']) || !isset($data['date_debut'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $certificationId = $data['id_certification'];
    $formateur = sanitize($data['formateur']);
    $duree = sanitize($data['duree']);
    $statut = sanitize($data['statut'] ?? 'à venir');
    $dateDebut = $data['date_debut'];
    
    $stmt = $pdo->prepare("INSERT INTO formations (id_certification, formateur, duree, statut, date_debut) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$certificationId, $formateur, $duree, $statut, $dateDebut])) {
        $newId = $pdo->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $newId,
            'message' => 'Formation ajoutée avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de l\'ajout de la formation']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' && isAdmin()) {
    // Mettre à jour une formation existante (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    $certificationId = $data['id_certification'] ?? null;
    $formateur = isset($data['formateur']) ? sanitize($data['formateur']) : null;
    $duree = isset($data['duree']) ? sanitize($data['duree']) : null;
    $statut = isset($data['statut']) ? sanitize($data['statut']) : null;
    $dateDebut = $data['date_debut'] ?? null;
    
    $updates = [];
    $params = [];
    
    if ($certificationId !== null) {
        $updates[] = "id_certification = ?";
        $params[] = $certificationId;
    }
    
    if ($formateur !== null) {
        $updates[] = "formateur = ?";
        $params[] = $formateur;
    }
    
    if ($duree !== null) {
        $updates[] = "duree = ?";
        $params[] = $duree;
    }
    
    if ($statut !== null) {
        $updates[] = "statut = ?";
        $params[] = $statut;
    }
    
    if ($dateDebut !== null) {
        $updates[] = "date_debut = ?";
        $params[] = $dateDebut;
    }
    
    if (empty($updates)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Aucune donnée à mettre à jour']);
        exit;
    }
    
    $params[] = $id;
    
    $stmt = $pdo->prepare("UPDATE formations SET " . implode(", ", $updates) . " WHERE id = ?");
    
    if ($stmt->execute($params)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Formation mise à jour avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la mise à jour de la formation']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isAdmin()) {
    // Supprimer une formation (admin uniquement)
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Données invalides']);
        exit;
    }
    
    $id = $data['id'];
    
    $stmt = $pdo->prepare("DELETE FROM formations WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Formation supprimée avec succès'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de la suppression de la formation']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>