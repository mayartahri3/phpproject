<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';
$filters = [];

// Traitement des filtres
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['filter'])) {
    if (!empty($_GET['certification'])) {
        $filters['certification'] = $_GET['certification'];
    }
    
    if (!empty($_GET['date_debut']) && !empty($_GET['date_fin'])) {
        $filters['date_debut'] = $_GET['date_debut'];
        $filters['date_fin'] = $_GET['date_fin'];
    }
}

// Récupérer les étudiants certifiés avec les filtres
$certifiedStudents = getCertifiedStudents($pdo, $filters);

// Récupérer toutes les certifications pour le filtre
$certifications = getAllCertifications($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudiants Certifiés - Tekup Certifications</title>
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
            --green-50: #ecfdf5;
            --green-500: #10b981;
            --red-50: #fef2f2;
            --red-500: #ef4444;
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
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(145deg, var(--blue-50) 0%, var(--white) 100%);
            min-height: 100vh;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .admin-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        .admin-content {
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            margin-top: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .admin-content:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-800));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
        }

        .alert-error {
            background: var(--red-50);
            color: var(--red-500);
            border: 1px solid var(--red-500);
        }

        .alert-success {
            background: var(--green-50);
            color: var(--green-500);
            border: 1px solid var(--green-500);
        }

        .filter-section {
            margin-bottom: 2.5rem;
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .filter-form {
            display: grid;
            gap: 1.25rem;
            max-width: 900px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        label {
            font-weight: 600;
            color: var(--gray-600);
            font-size: 0.95rem;
        }

        select, input[type="date"] {
            padding: 0.875rem;
            border: 1px solid var(--gray-300);
            border-radius: 10px;
            font-size: 1rem;
            color: var(--gray-700);
            background: var(--white);
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
        }

        select:hover, input[type="date"]:hover {
            border-color: var(--primary-blue-light);
            transform: translateY(-2px);
        }

        select:focus, input[type="date"]:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        button, .btn {
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        button {
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            color: var(--white);
            box-shadow: var(--shadow);
        }

        button:hover {
            background: linear-gradient(135deg, var(--primary-blue-light), var(--blue-600));
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
            text-decoration: none;
            box-shadow: var(--shadow);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .data-section {
            margin-top: 2.5rem;
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
            padding: 1.25rem;
            text-align: left;
            font-size: 1rem;
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

        .data-table tr {
            transition: background 0.2s ease;
        }

        .data-table tr:hover {
            background: var(--blue-50);
        }

        .no-results {
            text-align: center;
            padding: 2.5rem;
            color: var(--gray-500);
            font-size: 1.1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1.5rem 1rem;
            }

            .admin-content {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .data-table {
                display: block;
                overflow-x: auto;
            }

            .data-table th, .data-table td {
                font-size: 0.95rem;
                padding: 1rem;
            }
        }

        @media (max-width: 640px) {
            .admin-content {
                padding: 1rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.25rem;
            }

            button, .btn {
                padding: 0.75rem 1.25rem;
                font-size: 0.95rem;
            }

            .filter-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="admin-content">
            <h1><i class="fas fa-user-graduate"></i> Gestion des Étudiants Certifiés</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <div class="filter-section">
                <h2><i class="fas fa-filter"></i> Filtrer les Résultats</h2>
                
                <form method="GET" action="" class="filter-form">
                    <input type="hidden" name="filter" value="1">
                    
                    <div class="form-group">
                        <label for="certification">Certification</label>
                        <select id="certification" name="certification">
                            <option value="">Toutes les certifications</option>
                            <?php foreach ($certifications as $cert): ?>
                                <option value="<?= htmlspecialchars($cert['id']) ?>" <?= (isset($filters['certification']) && $filters['certification'] == $cert['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cert['nom_certification']) ?> (<?= htmlspecialchars($cert['domaine']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_debut">Date de début</label>
                            <input type="date" id="date_debut" name="date_debut" value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin">Date de fin</label>
                            <input type="date" id="date_fin" name="date_fin" value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit"><i class="fas fa-filter"></i> Appliquer le Filtre</button>
                        <a href="certified-students.php" class="btn btn-secondary"><i class="fas fa-undo"></i> Réinitialiser</a>
                    </div>
                </form>
            </div>
            
            <div class="data-section">
                <h2><i class="fas fa-list-ul"></i> Liste des Étudiants Certifiés</h2>
                
                <?php if (empty($certifiedStudents)): ?>
                    <p class="no-results"><i class="fas fa-info-circle"></i> Aucun étudiant certifié trouvé.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Nom</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-certificate"></i> Certification</th>
                                <th><i class="fas fa-book"></i> Domaine</th>
                                <th><i class="fas fa-calendar-alt"></i> Date de Certification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certifiedStudents as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['prenom'] . ' ' . $student['nom']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td><?= htmlspecialchars($student['nom_certification']) ?></td>
                                    <td><?= htmlspecialchars($student['domaine']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($student['date_certification'])) ?></td>
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