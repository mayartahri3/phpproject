<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

class FormateursController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllFormateurs() {
        try {
            $stmt = $this->pdo->query("
                SELECT f.id, f.nom, f.email, 
                       GROUP_CONCAT(c.nom_certification SEPARATOR ', ') AS certifications,
                       GROUP_CONCAT(c.id SEPARATOR ',') AS certification_ids
                FROM formateurs f
                LEFT JOIN formateurs_certifications fc ON f.id = fc.id_formateur
                LEFT JOIN certifications c ON fc.id_certification = c.id
                GROUP BY f.id
                ORDER BY f.nom ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching formateurs: " . $e->getMessage());
            return [];
        }
    }

    public function addFormateur($nom, $email, $certifications = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Insert formateur
            $stmt = $this->pdo->prepare("INSERT INTO formateurs (nom, email) VALUES (?, ?)");
            $success = $stmt->execute([$nom, $email]);
            
            if (!$success) {
                $this->pdo->rollBack();
                return false;
            }
            
            $formateurId = $this->pdo->lastInsertId();
            
            // Associate with certifications if any
            if (!empty($certifications) && is_array($certifications)) {
                $insertCerts = $this->pdo->prepare("INSERT INTO formateurs_certifications (id_formateur, id_certification) VALUES (?, ?)");
                
                foreach ($certifications as $certificationId) {
                    if (!$insertCerts->execute([$formateurId, $certificationId])) {
                        $this->pdo->rollBack();
                        return false;
                    }
                }
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error adding formateur: " . $e->getMessage());
            return false;
        }
    }

    public function updateFormateur($id, $nom, $email, $certifications = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Update formateur info
            $stmt = $this->pdo->prepare("UPDATE formateurs SET nom = ?, email = ? WHERE id = ?");
            $success = $stmt->execute([$nom, $email, $id]);
            
            if (!$success) {
                $this->pdo->rollBack();
                return false;
            }
            
            // Remove all existing certification associations
            $deleteStmt = $this->pdo->prepare("DELETE FROM formateurs_certifications WHERE id_formateur = ?");
            if (!$deleteStmt->execute([$id])) {
                $this->pdo->rollBack();
                return false;
            }
            
            // Add new certification associations
            if (!empty($certifications) && is_array($certifications)) {
                $insertCerts = $this->pdo->prepare("INSERT INTO formateurs_certifications (id_formateur, id_certification) VALUES (?, ?)");
                
                foreach ($certifications as $certificationId) {
                    if (!$insertCerts->execute([$id, $certificationId])) {
                        $this->pdo->rollBack();
                        return false;
                    }
                }
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error updating formateur: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFormateur($id) {
        try {
            $this->pdo->beginTransaction();
            
            // Delete certification associations first
            $deleteAssocsStmt = $this->pdo->prepare("DELETE FROM formateurs_certifications WHERE id_formateur = ?");
            $deleteAssocsStmt->execute([$id]);
            
            // Delete formateur
            $deleteFormateurStmt = $this->pdo->prepare("DELETE FROM formateurs WHERE id = ?");
            $result = $deleteFormateurStmt->execute([$id]);
            
            if ($result) {
                $this->pdo->commit();
                return true;
            } else {
                $this->pdo->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting formateur: " . $e->getMessage());
            return false;
        }
    }

    public function emailExists($email, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM formateurs WHERE email = ? AND id != ?");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM formateurs WHERE email = ?");
                $stmt->execute([$email]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking formateur email: " . $e->getMessage());
            return false;
        }
    }

    public function getFormateurCertifications($formateurId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.id
                FROM formateurs_certifications fc
                JOIN certifications c ON fc.id_certification = c.id
                WHERE fc.id_formateur = ?
            ");
            $stmt->execute([$formateurId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            error_log("Error fetching formateur certifications: " . $e->getMessage());
            return [];
        }
    }
}
?>