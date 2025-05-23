<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../user/FormateurController.php';
require_once '../userController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Initialize FormateursController
$formateursController = new FormateursController($pdo);

// Fetch certifications for the dropdowns
try {
    $stmt = $pdo->query("SELECT id, nom_certification FROM certifications ORDER BY nom_certification ASC");
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching certifications: " . $e->getMessage());
    $certifications = [];
}

// Handle form submissions
$successMessage = '';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'addFormateur') {
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $certifications = isset($_POST['certifications']) ? $_POST['certifications'] : [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email address.';
        } elseif (empty($nom)) {
            $errorMessage = 'All fields are required.';
        } elseif ($formateursController->emailExists($email)) {
            $errorMessage = 'Email already exists.';
        } else {
            if ($formateursController->addFormateur($nom, $email, $certifications)) {
                $successMessage = 'Formateur added successfully.';
            } else {
                $errorMessage = 'Failed to add formateur. Email may already exist.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'updateFormateur') {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $certifications = isset($_POST['certifications']) ? $_POST['certifications'] : [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email address.';
        } elseif (empty($nom)) {
            $errorMessage = 'Name is required.';
        } elseif ($formateursController->emailExists($email, $id)) {
            $errorMessage = 'Email already exists.';
        } else {
            if ($formateursController->updateFormateur($id, $nom, $email, $certifications)) {
                $successMessage = 'Formateur updated successfully.';
            } else {
                $errorMessage = 'Failed to update formateur. Email may already exist or formateur not found.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'deleteFormateur') {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        if ($id === false || $id === null) {
            $errorMessage = 'Invalid formateur ID.';
        } elseif ($formateursController->deleteFormateur($id)) {
            $successMessage = 'Formateur deleted successfully.';
        } else {
            $errorMessage = 'Failed to delete formateur. Please check if the formateur exists or is referenced elsewhere.';
        }
    }
}

// Get formateurs
$formateurs = $formateursController->getAllFormateurs();

// Get current admin info from session (assuming a separate UserController for admin info)
$currentAdminId = $_SESSION['user_id'];
$userController = new userController($pdo); // Assuming this exists for admin info
$adminInfo = $userController->getAdminInfo($currentAdminId);
$adminName = isset($adminInfo['prenom']) ? $adminInfo['prenom'] : 'Admin';

// Get statistics (assuming these are handled by UserController or another controller)
$stats = [
    'total_users' => $userController->getTotalUsers(),
    'total_certifications' => $userController->getTotalCertifications(),
    'total_formations' => $userController->getTotalFormations(),
    'total_certified' => $userController->getTotalCertified(),
    'pending_requests' => $userController->getPendingRequests()
];

// Get recent training requests and feedbacks
$recentRequests = $userController->getTrainingRequests('en attente');
$recentFeedbacks = $userController->getFeedbacks();

// Get certification statistics
$certificationStats = $userController->getCertificationStats();

// Get data for user statistics chart
$monthlyStats = $userController->getMonthlyCertificationStats();
$chartLabels = [];
$chartData = [];
foreach ($monthlyStats as $stat) {
    $chartLabels[] = $stat['month'];
    $chartData[] = $stat['count'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - Tekup Certifications</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
        }
        .stat-icon {
            height: 60px;
            width: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .data-table th {
            padding: 12px 15px;
            text-align: left;
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
        }
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background-color: #dc2626;
        }
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }
        .action-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-completed {
            background-color: #10b981;
            color: white;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            padding: 24px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #10b981;
            color: white;
        }
        .alert-error {
            background-color: #ef4444;
            color: white;
        }
        select[multiple] {
            height: 150px;
        }
    </style>
</head>
<body>
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">Tekup Certifications</h1>
                <div class="flex items-center">
                    <!-- Search -->
                    <div class="relative mr-4">
                        <input type="text" id="searchInput" placeholder="Rechercher des sections..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <!-- Notifications -->
                    <div class="relative mr-4">
                        <button class="relative p-2 rounded-full hover:bg-gray-100">
                            <i class="fas fa-bell text-gray-600"></i>
                            <span class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-4 h-4 text-xs flex items-center justify-center"><?= $stats['pending_requests'] ?></span>
                        </button>
                        <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-10">
                            <div class="p-4 border-b">
                                <h3 class="font-semibold">Notifications</h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <?php foreach (array_slice($recentRequests, 0, 3) as $request): ?>
                                <div class="p-3 border-b hover:bg-gray-50">
                                    <p class="font-medium"><?= htmlspecialchars($request['prenom'] . ' ' . $request['nom']) ?></p>
                                    <p class="text-sm text-gray-600">Nouvelle demande: <?= htmlspecialchars($request['nom_certification']) ?></p>
                                    <p class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($request['date_demande'])) ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="p-3 text-center">
                                <a href="training-requests.php" class="text-blue-600 hover:text-blue-800 font-medium">Voir tout</a>
                            </div>
                        </div>
                    </div>
                    <!-- Admin Profile -->
                    <div class="relative">
                        <button id="adminProfileButton" class="flex items-center focus:outline-none">
                            <img src="../assets/useravatar.jpg" class="w-10 h-10 rounded-full" alt="Admin Avatar">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-700">Hi, <?= htmlspecialchars($adminName) ?></p>
                            </div>
                            <i class="fas fa-chevron-down ml-2 text-gray-500 text-xs"></i>
                        </button>
                        <div id="adminDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10">
                            <a href="profil.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Mon profil
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="../auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Se déconnecter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Success/Error Messages -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="users.php" class="bg-white p-6 card flex items-center hover:bg-blue-50">
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Utilisateurs</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $stats['total_users'] ?></h3>
                    </div>
                </a>
                <a href="certifications.php" class="bg-white p-6 card flex items-center hover:bg-blue-50">
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <i class="fas fa-user-check text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Certifications</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $stats['total_certifications'] ?></h3>
                    </div>
                </a>
                <a href="formations.php" class="bg-white p-6 card flex items-center hover:bg-green-50">
                    <div class="stat-icon bg-green-100 text-green-600">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Formations</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $stats['total_formations'] ?></h3>
                    </div>
                </a>
                <a href="certified-students.php" class="bg-white p-6 card flex items-center hover:bg-purple-50">
                    <div class="stat-icon bg-purple-100 text-purple-600">
                        <i class="fas fa-certificate text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Étudiants Certifiés</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= $stats['total_certified'] ?></h3>
                    </div>
                </a>
            </div>

            <!-- Charts and Data Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- User Statistics Chart -->
                <div class="bg-white p-6 card col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-gray-800">Statistiques des Certifications Par Mois</h2>
                        <div class="flex">
                            <button class="btn btn-small bg-gray-100 text-gray-600 mr-2" id="exportChart">Export</button>
                            <button class="btn btn-small bg-gray-100 text-gray-600" id="printChart">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </div>
                    </div>
                    <canvas id="userStatsChart" height="300"></canvas>
                </div>

                <!-- Formateurs List -->
                <div class="bg-white p-6 card">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-800">Liste des Formateurs</h2>
                        <div class="flex space-x-2">
                            <button id="addFormateurBtn" class="btn btn-primary flex items-center">
                                <i class="fas fa-plus mr-2"></i> Ajouter
                            </button>
                        </div>
                    </div>
                    <?php if (empty($formateurs)): ?>
                        <p class="text-gray-500">Aucun formateur trouvé.</p>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($formateurs as $formateur): ?>
                                <li class="py-3">
                                    <div class="flex items-center">
                                        <button 
                                            class="mr-3 text-blue-600 hover:text-blue-800 focus:outline-none email-btn" 
                                            data-email="<?= htmlspecialchars($formateur['email']) ?>"
                                            title="Afficher l'email"
                                            type="button"
                                        >
                                            <i class="fas fa-envelope text-lg"></i>
                                        </button>
                                        <span class="font-medium text-gray-700 flex-grow"><?= htmlspecialchars($formateur['nom']) ?></span>
                                        <button 
                                            class="update-formateur-btn btn btn-primary btn-small mr-2"
                                            data-id="<?= $formateur['id'] ?>"
                                            data-nom="<?= htmlspecialchars($formateur['nom']) ?>"
                                            data-email="<?= htmlspecialchars($formateur['email']) ?>"
                                            data-certifications="<?= htmlspecialchars($formateur['certification_ids'] ?? '') ?>"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="index.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="deleteFormateur">
                                            <input type="hidden" name="id" value="<?= $formateur['id'] ?>">
                                            <button type="submit" 
                                                    class="btn btn-danger btn-small"
                                                    onclick="return confirm('Voulez-vous vraiment supprimer ce formateur ?');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php if (!empty($formateur['certifications'])): ?>
                                        <div class="mt-2 ml-8">
                                            <span class="text-sm text-gray-500">Certifications: </span>
                                            <span class="text-sm font-medium text-blue-600"><?= htmlspecialchars($formateur['certifications']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2 ml-8">
                                            <span class="text-sm text-gray-400 italic">Aucune certification</span>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Formateur Modal -->
            <div id="addFormateurModal" class="modal">
                <div class="modal-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Ajouter un Formateur</h3>
                        <button id="closeAddModal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="addFormateurForm" action="index.php" method="POST">
                        <input type="hidden" name="action" value="addFormateur">
                        <div class="mb-4">
                            <label for="add_nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                            <input type="text" id="add_nom" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="add_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="add_email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="add_certifications" class="block text-sm font-medium text-gray-700 mb-1">Certifications</label>
                            <select id="add_certifications" name="certifications[]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" multiple>
                                <?php foreach ($certifications as $certification): ?>
                                    <option value="<?= htmlspecialchars($certification['id']) ?>"><?= htmlspecialchars($certification['nom_certification']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs certifications</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Formateur Modal -->
            <div id="updateFormateurModal" class="modal">
                <div class="modal-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Modifier un Formateur</h3>
                        <button id="closeUpdateModal" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="updateFormateurForm" action="index.php" method="POST">
                        <input type="hidden" name="action" value="updateFormateur">
                        <input type="hidden" id="update_id" name="id">
                        <div class="mb-4">
                            <label for="update_nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                            <input type="text" id="update_nom" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="update_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="update_email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="update_certifications" class="block text-sm font-medium text-gray-700 mb-1">Certifications</label>
                            <select id="update_certifications" name="certifications[]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" multiple>
                                <?php foreach ($certifications as $certification): ?>
                                    <option value="<?= htmlspecialchars($certification['id']) ?>"><?= htmlspecialchars($certification['nom_certification']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs certifications</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Modifier
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Certifications -->
            <div class="bg-white p-6 card mb-8">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Certifications disponibles</h2>
                        <p class="text-sm text-gray-500">Nombre des utilisateurs par certification</p>
                    </div>
                    <div class="flex">
                        <button class="action-btn text-gray-500" id="refreshCertStats">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="action-btn text-gray-500" id="closeCertStats">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Certification</th>
                                        <th class="text-right">Utilisateurs</th>
                                        <th class="text-right">Pourcentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificationStats as $stat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stat['certification_name']) ?></td>
                                        <td class="text-right"><?= $stat['user_count'] ?></td>
                                        <td class="text-right"><?= number_format($stat['percentage'], 1) ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <canvas id="certificationChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Training Requests -->
                <div class="bg-white p-6 card">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-gray-800">Nouvelles Demandes</h2>
                        <button class="action-btn text-gray-500">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                    <?php if (empty($recentRequests)): ?>
                        <p class="text-gray-500">Aucune demande en attente.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach (array_slice($recentRequests, 0, 3) as $request): ?>
                                <div class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                                    <img src="../assets/useravatar.jpg" class="w-10 h-10 rounded-full" alt="Student Avatar">
                                    <div class="ml-3 flex-grow">
                                        <h5 class="text-sm font-medium"><?= htmlspecialchars($request['prenom'] . ' ' . $request['nom']) ?></h5>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($request['nom_certification']) ?></p>
                                    </div>
                                    <div class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($request['date_demande'])) ?></div>
                                    <a href="training-requests.php?action=view&id=<?= $request['id'] ?>" class="ml-4 text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="training-requests.php" class="text-sm text-blue-500 hover:text-blue-700 font-medium">Voir toutes les demandes →</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Feedback -->
                <div class="bg-white p-6 card">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-gray-800">Derniers Feedbacks</h2>
                        <button class="action-btn text-gray-500">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                    <?php if (empty($recentFeedbacks)): ?>
                        <p class="text-gray-500">Aucun feedback pour le moment.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Étudiant</th>
                                        <th>Formation</th>
                                        <th>Note</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($recentFeedbacks, 0, 3) as $feedback): ?>
                                        <tr>
                                            <td class="flex items-center">
                                                <img src="../assets/useravatar.jpg" class="w-8 h-8 rounded-full mr-2" alt="Student Avatar">
                                                <span><?= htmlspecialchars($feedback['prenom'] . ' ' . $feedback['nom']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($feedback['nom_certification']) ?></td>
                                            <td><?= $feedback['note'] ?>/5</td>
                                            <td><?= date('d/m/Y', strtotime($feedback['date_feedback'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            <a href="feedbacks.php" class="text-sm text-blue-500 hover:text-blue-700 font-medium">Voir tous les feedbacks →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Create New Post Section -->
            <div class="flex justify-center items-center mb-6">
                <a href="posts.php" id="createPostCard" class="bg-white p-6 card mt-8 clickable-card">
                    <div class="flex items-center">
                        <i class="fas fa-paper-plane text-blue-600 text-2xl mr-3"></i>
                        <h2 class="text-lg font-bold text-gray-800">Créer un Nouveau Post</h2>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Annoncer une nouvelle formation ou partager des mises à jour.</p>
                </a>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle admin dropdown
        const adminProfileButton = document.getElementById('adminProfileButton');
        const adminDropdown = document.getElementById('adminDropdown');
        if (adminProfileButton && adminDropdown) {
            adminProfileButton.addEventListener('click', function() {
                adminDropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', function(event) {
                if (!adminProfileButton.contains(event.target) && !adminDropdown.contains(event.target)) {
                    adminDropdown.classList.add('hidden');
                }
            });
        }

        // Toggle notifications dropdown
        const notificationButton = document.querySelector('.fa-bell').parentElement;
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        if (notificationButton && notificationsDropdown) {
            notificationButton.addEventListener('click', function() {
                notificationsDropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', function(event) {
                if (!notificationButton.contains(event.target) && !notificationsDropdown.contains(event.target)) {
                    notificationsDropdown.classList.add('hidden');
                }
            });
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = this.value.toLowerCase();
                document.querySelectorAll('main > div').forEach(section => {
                    const title = section.querySelector('h2');
                    if (title && title.textContent.toLowerCase().includes(query)) {
                        section.style.display = '';
                    } else if (query.length > 2) {
                        section.style.display = 'none';
                    } else {
                        section.style.display = '';
                    }
                });
            });
        }

        // Modal handling
        const addFormateurBtn = document.getElementById('addFormateurBtn');
        const addFormateurModal = document.getElementById('addFormateurModal');
        const closeAddModal = document.getElementById('closeAddModal');
        const updateFormateurModal = document.getElementById('updateFormateurModal');
        const closeUpdateModal = document.getElementById('closeUpdateModal');

        if (addFormateurBtn && addFormateurModal) {
            addFormateurBtn.addEventListener('click', function() {
                addFormateurModal.style.display = 'flex';
            });
        }

        if (closeAddModal && addFormateurModal) {
            closeAddModal.addEventListener('click', function() {
                addFormateurModal.style.display = 'none';
            });
        }

        if (closeUpdateModal && updateFormateurModal) {
            closeUpdateModal.addEventListener('click', function() {
                updateFormateurModal.style.display = 'none';
            });
        }

        // Update formateur buttons
        document.querySelectorAll('.update-formateur-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nom = this.getAttribute('data-nom');
                const email = this.getAttribute('data-email');
                const certifications = this.getAttribute('data-certifications');

                document.getElementById('update_id').value = id;
                document.getElementById('update_nom').value = nom;
                document.getElementById('update_email').value = email;

                // Reset all options
                const certSelect = document.getElementById('update_certifications');
                for (let i = 0; i < certSelect.options.length; i++) {
                    certSelect.options[i].selected = false;
                }

                // Select the appropriate certifications
                if (certifications && certifications.length > 0) {
                    const certIds = certifications.split(',').map(id => id.trim());
                    for (let i = 0; i < certSelect.options.length; i++) {
                        if (certIds.includes(certSelect.options[i].value)) {
                            certSelect.options[i].selected = true;
                        }
                    }
                }

                // Show the modal
                updateFormateurModal.style.display = 'flex';
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === addFormateurModal) {
                addFormateurModal.style.display = 'none';
            }
            if (event.target === updateFormateurModal) {
                updateFormateurModal.style.display = 'none';
            }
        });

        // Form validation
        const addForm = document.getElementById('addFormateurForm');
        const updateForm = document.getElementById('updateFormateurForm');
        if (addForm) {
            addForm.addEventListener('submit', function(event) {
                const email = document.getElementById('add_email').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    event.preventDefault();
                    alert('Veuillez entrer une adresse email valide.');
                }
            });
        }
        if (updateForm) {
            updateForm.addEventListener('submit', function(event) {
                const email = document.getElementById('update_email').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    event.preventDefault();
                    alert('Veuillez entrer une adresse email valide.');
                }
            });
        }

        // Email button functionality
        document.querySelectorAll('.email-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                const email = this.getAttribute('data-email');
                if (email) {
                    window.alert("Email du formateur : " + email);
                }
            });
        });

        // Charts
        const userStatsCtx = document.getElementById('userStatsChart');
        if (userStatsCtx) {
            new Chart(userStatsCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [{
                        label: 'Utilisateurs certifiés',
                        data: <?= json_encode($chartData) ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { drawBorder: false, color: 'rgba(0, 0, 0, 0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        const certificationCtx = document.getElementById('certificationChart');
        if (certificationCtx) {
            const certLabels = <?= json_encode(array_column($certificationStats, 'certification_name')) ?>;
            const certData = <?= json_encode(array_column($certificationStats, 'user_count')) ?>;
            const certPercentages = <?= json_encode(array_column($certificationStats, 'percentage')) ?>;
            new Chart(certificationCtx, {
                type: 'doughnut',
                data: {
                    labels: certLabels,
                    datasets: [{
                        data: certPercentages,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const users = certData[context.dataIndex];
                                    return `${label}: ${value.toFixed(1)}% (${users} utilisateurs)`;
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // Export chart
        document.getElementById('exportChart').addEventListener('click', function() {
            const canvas = document.getElementById('userStatsChart');
            const image = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = 'certification_stats.png';
            link.href = image;
            link.click();
        });

        // Print chart
        document.getElementById('printChart').addEventListener('click', function() {
            const canvas = document.getElementById('userStatsChart');
            const dataUrl = canvas.toDataURL('image/png');
            const windowContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Statistiques de Certification</title>
                    <style>
                        body { font-family: 'Segoe UI', sans-serif; margin: 20px; }
                        h1 { color: #3b82f6; font-size: 24px; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <h1>Statistiques de Certification</h1>
                    <img src="${dataUrl}" style="max-width: 100%;">
                    <p>Généré le ${new Date().toLocaleDateString()}</p>
                </body>
                </html>
            `;
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write(windowContent);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() { printWindow.print(); printWindow.close(); }, 500);
        });

        // Refresh certification stats
        document.getElementById('refreshCertStats').addEventListener('click', function() {
            const button = this;
            button.classList.add('animate-spin');
            setTimeout(function() {
                button.classList.remove('animate-spin');
                const message = document.createElement('div');
                message.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg';
                message.textContent = 'Statistiques mises à jour avec succès';
                document.body.appendChild(message);
                setTimeout(function() { message.remove(); }, 3000);
            }, 1000);
        });

        // Close certification stats section
        document.getElementById('closeCertStats').addEventListener('click', function() {
            const certStatsSection = this.closest('.card');
            certStatsSection.style.display = 'none';
            const restoreButton = document.createElement('button');
            restoreButton.className = 'fixed bottom-4 left-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg';
            restoreButton.innerHTML = '<i class="fas fa-undo mr-2"></i> Restaurer les statistiques';
            document.body.appendChild(restoreButton);
            restoreButton.addEventListener('click', function() {
                certStatsSection.style.display = '';
                this.remove();
            });
        });
    });
    </script>
</body>
</html>