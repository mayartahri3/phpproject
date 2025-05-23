<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Function to get posts with trainer information
function getPostsWithTrainers($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, f.nom as formateur_nom 
            FROM posts p 
            LEFT JOIN formateurs f ON p.formateur_id = f.id 
            ORDER BY p.date_publication DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching posts with trainers: " . $e->getMessage());
        return [];
    }
}

// Récupérer les posts (annonces) avec les informations des formateurs
$posts = getPostsWithTrainers($pdo);

// Récupérer les formations en cours et à venir
$upcomingTrainings = getFormations($pdo, 'à venir');
$currentTrainings = getFormations($pdo, 'en cours');

// Récupérer les certifications par domaine
$certificationsByDomain = getCertificationsByDomain($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Tekup Certifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-blue-light: #3b82f6;
            --primary-blue-dark: #1d4ed8;
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --blue-800: #1e40af;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --success-color: #059669;
            --success-bg: #ecfdf5;
            --warning-color: #d97706;
            --warning-bg: #fff7ed;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--blue-50) 0%, var(--blue-100) 50%, var(--white) 100%);
            min-height: 100vh;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .user-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .user-content {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 3rem;
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--blue-100);
            position: relative;
            overflow: hidden;
        }

        .welcome-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--blue-700), var(--primary-blue-light));
        }

        .welcome-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .dashboard-sections {
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        .dashboard-section {
            background: var(--white);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--blue-100);
            position: relative;
            overflow: hidden;
        }

        .dashboard-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--blue-700));
        }

        .dashboard-section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.25rem;
        }

        /* Posts/Announcements Styles */
        .posts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .post-card {
            background: linear-gradient(135deg, var(--gray-50), var(--white));
            border: 2px solid var(--gray-100);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .post-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-blue), var(--blue-700));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--blue-200);
        }

        .post-card:hover::before {
            transform: scaleX(1);
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .post-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
            line-height: 1.3;
            flex: 1;
            margin-right: 1rem;
        }

        .post-date {
            color: var(--gray-500);
            font-size: 0.9rem;
            white-space: nowrap;
            background: var(--gray-100);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
        }

        .post-content {
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .post-content p {
            margin: 0;
            line-height: 1.7;
            color: var(--gray-600);
        }

        .post-meta {
            margin: 1rem 0;
            padding: 1rem 0;
            border-top: 1px solid var(--gray-200);
        }

        .post-trainer {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
        }

        .trainer-label {
            font-weight: 600;
            color: var(--gray-600);
        }

        .trainer-name {
            color: var(--primary-blue);
            font-weight: 600;
            background-color: var(--blue-50);
            padding: 0.5rem 1rem;
            border-radius: 16px;
            font-size: 0.85em;
        }

        .post-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: auto;
        }

        .post-type {
            padding: 0.5rem 1rem;
            border-radius: 16px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .post-type-en-cours {
            background-color: var(--success-bg);
            color: var(--success-color);
        }

        .post-type-à-venir {
            background-color: var(--warning-bg);
            color: var(--warning-color);
        }

        /* Training Cards Styles */
        .trainings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .training-card {
            background: linear-gradient(135deg, var(--gray-50), var(--white));
            border: 2px solid var(--gray-100);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .training-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--success-color), #10b981);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .training-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--blue-200);
        }

        .training-card:hover::before {
            transform: scaleX(1);
        }

        .training-header {
            margin-bottom: 1.5rem;
        }

        .training-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .training-domain {
            background: var(--blue-100);
            color: var(--primary-blue);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .training-details {
            flex-grow: 1;
            margin-bottom: 2rem;
        }

        .training-details p {
            margin-bottom: 0.75rem;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .training-details strong {
            color: var(--gray-700);
            min-width: 80px;
        }

        .training-footer {
            margin-top: auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            color: var(--white);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            width: 100%;
            text-align: center;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--blue-700), var(--blue-800));
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: var(--white);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Certifications Styles */
        .certifications-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .certification-domain {
            background: linear-gradient(135deg, var(--gray-50), var(--white));
            border: 2px solid var(--gray-100);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .certification-domain::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #8b5cf6, #a855f7);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .certification-domain:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--blue-200);
        }

        .certification-domain:hover::before {
            transform: scaleX(1);
        }

        .certification-domain h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .certification-domain p {
            color: var(--gray-600);
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 2rem;
        }

        .actions .btn {
            width: auto;
            min-width: 200px;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--gray-600);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-section {
            animation: fadeInUp 0.6s ease-out;
        }

        .post-card,
        .training-card,
        .certification-domain {
            animation: fadeInUp 0.6s ease-out;
        }

        @media (max-width: 768px) {
            .user-content {
                padding: 1rem;
            }

            .welcome-header {
                margin-bottom: 2rem;
                padding: 2rem 1.5rem;
            }

            .welcome-header h1 {
                font-size: 2rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .dashboard-section {
                padding: 1.5rem;
            }

            .dashboard-section h2 {
                font-size: 1.5rem;
            }

            .posts-container,
            .trainings-container,
            .certifications-container {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: center;
            }

            .actions .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <div class="welcome-header">
                <h1>
                    <i class="fas fa-tachometer-alt"></i>
                    Bienvenue, <?= htmlspecialchars($_SESSION['user_name']) ?>
                </h1>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <h2>
                        <div class="section-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        Annonces
                    </h2>
                    
                    <?php if (empty($posts)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <h3>Aucune annonce</h3>
                            <p>Aucune annonce pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="posts-container">
                            <?php foreach ($posts as $post): ?>
                                <div class="post-card">
                                    <div class="post-header">
                                        <h3><?= htmlspecialchars($post['titre']) ?></h3>
                                        <span class="post-date">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= date('d/m/Y', strtotime($post['date_publication'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="post-content">
                                        <p><?= htmlspecialchars($post['contenu']) ?></p>
                                    </div>
                                    
                                    <?php if (!empty($post['formateur_nom'])): ?>
                                        <div class="post-meta">
                                            <div class="post-trainer">
                                                <span class="trainer-label">
                                                    <i class="fas fa-user-tie"></i>
                                                    Formateur:
                                                </span>
                                                <span class="trainer-name"><?= htmlspecialchars($post['formateur_nom']) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="post-footer">
                                        <span class="post-type post-type-<?= str_replace(' ', '-', $post['type']) ?>">
                                            <i class="fas fa-<?= $post['type'] === 'en cours' ? 'play-circle' : 'clock' ?>"></i>
                                            <?= $post['type'] === 'en cours' ? 'Formation en cours' : 'Formation à venir' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <h2>
                        <div class="section-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        Formations à Venir
                    </h2>
                    
                    <?php if (empty($upcomingTrainings)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-plus"></i>
                            <h3>Aucune formation à venir</h3>
                            <p>Aucune formation à venir pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="trainings-container">
                            <?php foreach ($upcomingTrainings as $training): ?>
                                <div class="training-card">
                                    <div class="training-header">
                                        <h3><?= htmlspecialchars($training['nom_certification']) ?></h3>
                                        <span class="training-domain"><?= htmlspecialchars($training['domaine']) ?></span>
                                    </div>
                                    
                                    <div class="training-details">
                                        <p>
                                            <strong><i class="fas fa-user-tie"></i> Formateur:</strong>
                                            <?= htmlspecialchars($training['formateur']) ?>
                                        </p>
                                        <p>
                                            <strong><i class="fas fa-clock"></i> Durée:</strong>
                                            <?= htmlspecialchars($training['duree']) ?>
                                        </p>
                                        <p>
                                            <strong><i class="fas fa-calendar-alt"></i> Date de début:</strong>
                                            <?= date('d/m/Y', strtotime($training['date_debut'])) ?>
                                        </p>
                                    </div>
                                    
                                    <div class="training-footer">
                                        <a href="trainings.php?action=register&id=<?= $training['id'] ?>" class="btn">
                                            <i class="fas fa-user-plus"></i>
                                            S'inscrire
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                
                
                <div class="dashboard-section">
                    <h2>
                        <div class="section-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        Certifications Disponibles
                    </h2>
                    
                    <div class="certifications-container">
                        <?php foreach ($certificationsByDomain as $domain): ?>
                            <div class="certification-domain">
                                <h3>
                                    <i class="fas fa-award"></i>
                                    <?= htmlspecialchars($domain['domaine']) ?>
                                </h3>
                                <p><?= htmlspecialchars($domain['certifications']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="actions">
                        <a href="certifications.php" class="btn">
                            <i class="fas fa-list"></i>
                            Voir toutes les certifications
                        </a>
                        <a href="request-training.php" class="btn">
                            <i class="fas fa-graduation-cap"></i>
                            Demander une formation
                        </a>
                        <a href="register-certification.php" class="btn">
                            <i class="fas fa-plus-circle"></i>
                            Enregistrer une certification
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/user.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced card interactions
            const cards = document.querySelectorAll('.post-card, .training-card, .certification-domain');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Add smooth scroll behavior for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>