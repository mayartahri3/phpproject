<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est un administrateur
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirige vers une page spécifique
 * @param string $page
 */
function redirect($page) {
    header("Location: $page");
    exit;
}

/**
 * Nettoie les données d'entrée
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Affiche un message d'erreur
 * @param string $message
 */
function showError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

/**
 * Affiche un message de succès
 * @param string $message
 */
function showSuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

/**
 * Génère un jeton CSRF
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si le jeton CSRF est valide
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

/**
 * Récupère les informations d'un utilisateur par son ID
 * @param PDO $pdo
 * @param int $userId
 * @return array|false
 */
function getUserById($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Récupère toutes les certifications
 * @param PDO $pdo
 * @return array
 */
function getAllCertifications($pdo) {
    $stmt = $pdo->query("SELECT * FROM certifications ORDER BY domaine, nom_certification");
    return $stmt->fetchAll();
}

/**
 * Récupère les certifications par domaine
 * @param PDO $pdo
 * @return array
 */
function getCertificationsByDomain($pdo) {
    $stmt = $pdo->query("SELECT domaine, GROUP_CONCAT(nom_certification SEPARATOR ', ') as certifications 
                         FROM certifications 
                         GROUP BY domaine 
                         ORDER BY domaine");
    return $stmt->fetchAll();
}

/**
 * Récupère une certification par son ID
 * @param PDO $pdo
 * @param int $certificationId
 * @return array|false
 */
function getCertificationById($pdo, $certificationId) {
    $stmt = $pdo->prepare("SELECT * FROM certifications WHERE id = ?");
    $stmt->execute([$certificationId]);
    return $stmt->fetch();
}

/**
 * Récupère toutes les formations
 * @param PDO $pdo
 * @param string $statut (optionnel)
 * @return array
 */
function getFormations($pdo, $statut = null) {
    $sql = "SELECT f.*, c.nom_certification, c.domaine 
            FROM formations f 
            JOIN certifications c ON f.id_certification = c.id";
    
    if ($statut) {
        $sql .= " WHERE f.statut = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$statut]);
    } else {
        $sql .= " ORDER BY f.date_debut DESC";
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll();
}

/**
 * Récupère les posts (annonces)
 * @param PDO $pdo
 * @param string $type (optionnel)
 * @return array
 */
function getPosts($pdo, $type = null) {
    $sql = "SELECT * FROM posts";
    
    if ($type) {
        $sql .= " WHERE type = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$type]);
    } else {
        $sql .= " ORDER BY date_publication DESC";
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll();
}

/**
 * Récupère les étudiants certifiés
 * @param PDO $pdo
 * @param array $filters (optionnel)
 * @return array
 */
function getCertifiedStudents($pdo, $filters = []) {
    $sql = "SELECT ec.*, u.nom, u.prenom, u.email, c.nom_certification, c.domaine 
            FROM etudiants_certifies ec 
            JOIN users u ON ec.id_user = u.id 
            JOIN certifications c ON ec.id_certification = c.id";
    
    $conditions = [];
    $params = [];
    
    if (!empty($filters['certification'])) {
        $conditions[] = "c.id = ?";
        $params[] = $filters['certification'];
    }
    
    if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
        $conditions[] = "ec.date_certification BETWEEN ? AND ?";
        $params[] = $filters['date_debut'];
        $params[] = $filters['date_fin'];
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY ec.date_certification DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * Récupère les demandes de formation
 * @param PDO $pdo
 * @param string $statut (optionnel)
 * @return array
 */
function getTrainingRequests($pdo, $statut = null) {
    $sql = "SELECT df.*, u.nom, u.prenom, u.email, c.nom_certification, c.domaine 
            FROM demandes_formations df 
            JOIN users u ON df.id_user = u.id 
            JOIN certifications c ON df.id_certification = c.id";
    
    if ($statut) {
        $sql .= " WHERE df.statut = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$statut]);
    } else {
        $sql .= " ORDER BY df.date_demande DESC";
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll();
}

/**
 * Récupère les feedbacks
 * @param PDO $pdo
 * @param int $formationId (optionnel)
 * @return array
 */
function getFeedbacks($pdo, $formationId = null) {
    $sql = "SELECT f.*, u.nom, u.prenom, fo.formateur, c.nom_certification 
            FROM feedbacks f 
            JOIN users u ON f.id_user = u.id 
            JOIN formations fo ON f.id_formation = fo.id 
            JOIN certifications c ON fo.id_certification = c.id";
    
    if ($formationId) {
        $sql .= " WHERE f.id_formation = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$formationId]);
    } else {
        $sql .= " ORDER BY f.date_feedback DESC";
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll();
}
?>