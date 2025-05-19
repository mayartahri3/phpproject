-- Création de la base de données
CREATE DATABASE IF NOT EXISTS tekup_certifications;
USE tekup_certifications;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des certifications
CREATE TABLE IF NOT EXISTS certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_certification VARCHAR(100) NOT NULL,
    domaine VARCHAR(100) NOT NULL,
    description TEXT
);

-- Table des formations
CREATE TABLE IF NOT EXISTS formations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_certification INT,
    formateur VARCHAR(100) NOT NULL,
    duree VARCHAR(50) NOT NULL,
    statut ENUM('en cours', 'à venir', 'terminée') DEFAULT 'à venir',
    date_debut DATE NOT NULL,
    FOREIGN KEY (id_certification) REFERENCES certifications(id) ON DELETE CASCADE
);

-- Table des demandes de formations
CREATE TABLE IF NOT EXISTS demandes_formations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_certification INT,
    statut ENUM('en attente', 'acceptée', 'rejetée') DEFAULT 'en attente',
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_certification) REFERENCES certifications(id) ON DELETE CASCADE
);

-- Table des feedbacks
CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_formation INT,
    commentaire TEXT,
    note INT CHECK (note BETWEEN 1 AND 5),
    date_feedback DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_formation) REFERENCES formations(id) ON DELETE CASCADE
);

-- Table des posts
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    type ENUM('en cours', 'à venir') NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des étudiants certifiés
CREATE TABLE IF NOT EXISTS etudiants_certifies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_certification INT,
    date_certification DATE NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_certification) REFERENCES certifications(id) ON DELETE CASCADE
);

-- Table des inscriptions aux formations
CREATE TABLE IF NOT EXISTS inscriptions_formations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_formation INT,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_formation) REFERENCES formations(id) ON DELETE CASCADE
);

-- Insertion des certifications
INSERT INTO certifications (nom_certification, domaine, description) VALUES
('CCNA', 'Cisco', 'Cisco Certified Network Associate'),
('CCNP', 'Cisco', 'Cisco Certified Network Professional'),
('CCIE', 'Cisco', 'Cisco Certified Internetwork Expert'),
('AWS Certified Cloud Practitioner', 'Amazon (AWS)', 'Fondamentaux du cloud AWS'),
('AWS Certified Solutions Architect', 'Amazon (AWS)', 'Architecture de solutions sur AWS'),
('AWS Certified Developer', 'Amazon (AWS)', 'Développement sur AWS'),
('AWS Certified SysOps Administrator', 'Amazon (AWS)', 'Administration système sur AWS'),
('AWS Certified DevOps Engineer', 'Amazon (AWS)', 'DevOps sur AWS'),
('CEH', 'EC-Council', 'Certified Ethical Hacker'),
('CHFI', 'EC-Council', 'Computer Hacking Forensic Investigator'),
('CND', 'EC-Council', 'Certified Network Defender'),
('OSCP', 'Offensive Security', 'Offensive Security Certified Professional'),
('OSWP', 'Offensive Security', 'Offensive Security Wireless Professional'),
('OSCE', 'Offensive Security', 'Offensive Security Certified Expert'),
('LPIC-1', 'Linux Professional Institute', 'Linux Administrator'),
('LPIC-2', 'Linux Professional Institute', 'Linux Engineer'),
('LPIC-3', 'Linux Professional Institute', 'Linux Enterprise Professional'),
('RHCSA EX-200', 'Red Hat', 'Red Hat Certified System Administrator'),
('RHCE EX-294', 'Red Hat', 'Red Hat Certified Engineer'),
('RHCS EX-180', 'Red Hat', 'Red Hat Certified Specialist'),
('RHCSA EX-447', 'Red Hat', 'Red Hat Certified Specialist in Ansible Automation'),
('RHCS EX-358', 'Red Hat', 'Red Hat Certified Specialist in Services Management and Automation'),
('HCIA', 'Huawei', 'Huawei Certified ICT Associate'),
('HCIP', 'Huawei', 'Huawei Certified ICT Professional'),
('HCIE', 'Huawei', 'Huawei Certified ICT Expert'),
('Java Associate', 'Oracle', 'Oracle Certified Associate Java Programmer'),
('Java Professional', 'Oracle', 'Oracle Certified Professional Java Programmer'),
('Database Associate', 'Oracle', 'Oracle Database Certified Associate'),
('Database Professional', 'Oracle', 'Oracle Database Certified Professional'),
('Database Expert', 'Oracle', 'Oracle Database Certified Expert'),
('Certified Kubernetes Administrator', 'The Linux Foundation', 'Administration Kubernetes'),
('Certified Kubernetes Application Developer', 'The Linux Foundation', 'Développement d''applications Kubernetes'),
('Microsoft Fundamentals', 'Microsoft', 'Azure Fundamentals'),
('Microsoft Associate', 'Microsoft', 'Azure Data Analyst Associate'),
('Microsoft Expert', 'Microsoft', 'Azure Data Scientist Expert'),
('PCEP', 'Python Institute', 'Certified Entry-Level Python Programmer'),
('PCAP', 'Python Institute', 'Certified Associate in Python Programming');

-- Insertion d'un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'Tekup', 'admin@tekup.tn', '$2y$10$8MNE.3/QzVtcQQOh1zXcnuMCGrVGtj7QxQ5TlIGxUQjnvQnlG7wVG', 'admin');