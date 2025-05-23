<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Récupérer tous les feedbacks
$feedbacks = getFeedbacks($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedbacks - Tekup Certifications</title>
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

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .admin-content {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            margin-top: 1rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }

        .data-section {
            margin-top: 2rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .data-table th, .data-table td {
            padding: 1rem;
            text-align: left;
            font-size: 0.95rem;
        }

        .data-table th {
            background: var(--blue-50);
            color: var(--gray-800);
            font-weight: 600;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table td {
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-700);
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background: var(--blue-50);
        }

        .no-results {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }

            .admin-content {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.25rem;
            }

            .data-table {
                display: block;
                overflow-x: auto;
            }

            .data-table th, .data-table td {
                font-size: 0.9rem;
                padding: 0.75rem;
            }
        }

        @media (max-width: 640px) {
            .admin-content {
                padding: 1rem;
            }

            h1 {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="admin-content">
            <h1><i class="fas fa-comments"></i> Gestion des Feedbacks</h1>
            
            <div class="data-section">
                <h2><i class="fas fa-list"></i> Liste des Feedbacks</h2>
                
                <?php if (empty($feedbacks)): ?>
                    <p class="no-results"><i class="fas fa-info-circle"></i> Aucun feedback trouvé.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Étudiant</th>
                                <th><i class="fas fa-certificate"></i> Formation</th>
                                <th><i class="fas fa-chalkboard-teacher"></i> Formateur</th>
                                <th><i class="fas fa-star"></i> Note</th>
                                <th><i class="fas fa-calendar-alt"></i> Date</th>
                                <th><i class="fas fa-comment"></i> Commentaire</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?= htmlspecialchars($feedback['prenom'] . ' ' . $feedback['nom']) ?></td>
                                    <td><?= htmlspecialchars($feedback['nom_certification']) ?></td>
                                    <td><?= htmlspecialchars($feedback['formateur']) ?></td>
                                    <td><?= htmlspecialchars($feedback['note']) ?>/5</td>
                                    <td><?= date('d/m/Y', strtotime($feedback['date_feedback'])) ?></td>
                                    <td><?= htmlspecialchars($feedback['commentaire']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>