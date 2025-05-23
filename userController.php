<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getTotalUsers() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
        return $stmt->fetch()['total'];
    }

    public function getTotalCertifications() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM certifications");
        return $stmt->fetch()['total'];
    }

    public function getTotalFormations() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM formations");
        return $stmt->fetch()['total'];
    }

    public function getTotalCertified() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM etudiants_certifies");
        return $stmt->fetch()['total'];
    }

    public function getPendingRequests() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM demandes_formations WHERE statut = 'en attente'");
        return $stmt->fetch()['total'];
    }

    public function getTrainingRequests($status = 'en attente') {
        $stmt = $this->pdo->prepare("
            SELECT df.id, df.date_demande, u.prenom, u.nom, c.nom_certification
            FROM demandes_formations df
            JOIN users u ON df.id_user = u.id
            JOIN certifications c ON df.id_certification = c.id
            WHERE df.statut = ?
            ORDER BY df.date_demande DESC
            LIMIT 10
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeedbacks() {
        $stmt = $this->pdo->query("
 SELECT f.*, u.nom, u.prenom, fo.formateur, c.nom_certification 
        FROM feedbacks f 
        JOIN users u ON f.id_user = u.id 
        JOIN formations fo ON f.id_formation = fo.id 
        JOIN certifications c ON fo.id_certification = c.id
        WHERE 1=1"
);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCertificationStats() {
        $stmt = $this->pdo->query("
            SELECT c.nom_certification AS certification_name, 
                   COUNT(ec.id) AS user_count,
                   (COUNT(ec.id) * 100.0 / (SELECT COUNT(*) FROM etudiants_certifies)) AS percentage
            FROM certifications c
            LEFT JOIN etudiants_certifies ec ON c.id = ec.id_certification
            GROUP BY c.id
            ORDER BY user_count DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdminInfo($adminId) {
        $stmt = $this->pdo->prepare("SELECT nom, prenom FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMonthlyCertificationStats() {
        $stmt = $this->pdo->query("
            SELECT 
                DATE_FORMAT(date_certification, '%m-%Y') as month,
                COUNT(*) as count
            FROM 
                etudiants_certifies
            WHERE
                date_certification >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            GROUP BY 
                DATE_FORMAT(date_certification, '%m-%Y')
            ORDER BY
                date_certification ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSettings() {
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function updateSettings($settings) {
        try {
            $this->pdo->beginTransaction();
            foreach ($settings as $key => $value) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$key, $value, $value]);
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error updating settings: " . $e->getMessage());
            return false;
        }
    }

    public function getTrainers() {
        try {
            $stmt = $this->pdo->query("SELECT id, nom, prenom, email FROM users WHERE role = 'formateur'");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching trainers: " . $e->getMessage());
            return [];
        }
    }

    public function addTrainer($nom, $prenom, $email) {
       
            $stmt = $this->pdo->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (?, ?, ?,0, 'formateur')");
            return $stmt->execute([$nom, $prenom, $email, $hashedPassword]);
        
        
    }

    public function updateTrainer($id, $nom, $prenom, $email, $password = null) {
        try {
            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, password = ? WHERE id = ? AND role = 'formateur'");
                return $stmt->execute([$nom, $prenom, $email, $hashedPassword, $id]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE users SET nom = ?, prenom = ?, email = ? WHERE id = ? AND role = 'formateur'");
                return $stmt->execute([$nom, $prenom, $email, $id]);
            }
        } catch (PDOException $e) {
            error_log("Error updating trainer: " . $e->getMessage());
            return false;
        }
    }

    public function deleteTrainer($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'formateur'");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting trainer: " . $e->getMessage());
            return false;
        }
    }
}
?>