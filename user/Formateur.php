<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../FormateursController.php';

// Verify if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Initialize FormateursController
$formateursController = new FormateursController($pdo);

// Get all certifications for dropdown
$certificationsStmt = $pdo->query("SELECT id, nom_certification, domaine FROM certifications ORDER BY domaine, nom_certification");
$certifications = $certificationsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
$successMessage = '';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        if (isset($_POST['action']) && $_POST['action'] === 'addFormateur') {
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $selectedCertifications = isset($_POST['certifications']) ? $_POST['certifications'] : [];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'Invalid email address.';
            } elseif (empty($nom)) {
                $errorMessage = 'All fields are required.';
            } elseif ($formateursController->emailExists($email)) {
                $errorMessage = 'Email already exists.';
            } else {
                if ($formateursController->addFormateur($nom, $email, $selectedCertifications)) {
                    $successMessage = 'Formateur added successfully.';
                } else {
                    $errorMessage = 'Failed to add formateur. Email may already exist.';
                }
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'updateFormateur') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $selectedCertifications = isset($_POST['certifications']) ? $_POST['certifications'] : [];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'Invalid email address.';
            } elseif (empty($nom)) {
                $errorMessage = 'Name is required.';
            } else {
                if ($formateursController->updateFormateur($id, $nom, $email, $selectedCertifications)) {
                    $successMessage = 'Formateur updated successfully.';
                } else {
                    $errorMessage = 'Failed to update formateur. Email may already exist or formateur not found.';
                }
            }
        }
    }
}

// Handle delete action (via GET)
if (isset($_GET['action']) && $_GET['action'] === 'deleteFormateur' && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    if ($formateursController->deleteFormateur($id)) {
        $successMessage = 'Formateur deleted successfully.';
    } else {
        $errorMessage = 'Failed to delete formateur.';
    }
}

// Get all formateurs
$formateurs = $formateursController->getAllFormateurs();

// Get current admin info from session
$currentAdminId = $_SESSION['user_id'];
$userController = new UserController($pdo); // Assuming you have this class
$adminInfo = $userController->getAdminInfo($currentAdminId);
$adminName = isset($adminInfo['prenom']) ? $adminInfo['prenom'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Formateurs - Tekup Certifications</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        /* Select2 custom styling */
        .select2-container--default .select2-selection--multiple {
            border-color: #e2e8f0;
            border-radius: 0.375rem;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6;
            outline: 0;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
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
                    <!-- Admin Profile -->
                    <div class="relative">
                        <button id="adminProfileButton" class="flex items-center focus:outline-none">
                            <img src="/api/placeholder/40/40" class="w-10 h-10 rounded-full" alt="Admin Avatar">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-700">Hi, <?= htmlspecialchars($adminName) ?></p>
                            </div>
                            <i class="fas fa-chevron-down ml-2 text-gray-500 text-xs"></i>
                        </button>
                        <div id="adminDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Mon profil
                            </a>
                            <a href="admin-management.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-users-cog mr-2"></i> Gérer les admins
                            </a>
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Paramètres
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

            <!-- Formateurs Management -->
            <div class="bg-white p-6 card">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800">Gestion des Formateurs</h2>
                    <button id="addFormateurBtn" class="btn btn-primary flex items-center">
                        <i class="fas fa-plus mr-2"></i> Ajouter un formateur
                    </button>
                </div>

                <!-- Formateurs Table -->
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Certifications</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($formateurs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-gray-500">Aucun formateur trouvé.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($formateurs as $formateur): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($formateur['nom']) ?></td>
                                        <td><?= htmlspecialchars($formateur['email']) ?></td>
                                        <td><?= htmlspecialchars($formateur['certifications'] ?? 'Aucune') ?></td>
                                        <td class="flex space-x-2">
                                            <button 
                                                class="update-formateur-btn btn btn-primary btn-small"
                                                data-id="<?= $formateur['id'] ?>"
                                                data-nom="<?= htmlspecialchars($formateur['nom']) ?>"
                                                data-email="<?= htmlspecialchars($formateur['email']) ?>"
                                                data-certifications="<?= htmlspecialchars($formateur['certification_ids'] ?? '') ?>"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?action=deleteFormateur&id=<?= $formateur['id'] ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Voulez-vous vraiment supprimer ce formateur ?');"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
                    <form id="addFormateurForm" action="formateurs.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="action" value="addFormateur">
                        
                        <div class="mb-4">
                            <label for="add_nom" class="block text-sm font-medium text-gray-700 mb-1">Nom Complet</label>
                            <input type="text" id="add_nom" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="add_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="add_email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="add_certifications" class="block text-sm font-medium text-gray-700 mb-1">Certifications à enseigner</label>
                            <select id="add_certifications" name="certifications[]" class="certifications-select w-full" multiple="multiple">
                                <?php foreach ($certifications as $cert): ?>
                                    <option value="<?= $cert['id'] ?>">
                                        <?= htmlspecialchars($cert['nom_certification']) ?> (<?= htmlspecialchars($cert['domaine']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                    <form id="updateFormateurForm" action="formateurs.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="action" value="updateFormateur">
                        <input type="hidden" id="update_id" name="id">
                        
                        <div class="mb-4">
                            <label for="update_nom" class="block text-sm font-medium text-gray-700 mb-1">Nom Complet</label>
                            <input type="text" id="update_nom" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="update_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="update_email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="update_certifications" class="block text-sm font-medium text-gray-700 mb-1">Certifications à enseigner</label>
                            <select id="update_certifications" name="certifications[]" class="certifications-select w-full" multiple="multiple">
                                <?php foreach ($certifications as $cert): ?>
                                    <option value="<?= $cert['id'] ?>">
                                        <?= htmlspecialchars($cert['nom_certification']) ?> (<?= htmlspecialchars($cert['domaine']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Modifier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('.certifications-select').select2({
            placeholder: 'Sélectionnez les certifications',
            allowClear: true,
            width: '100%'
        });

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
                
                // Reset and set the selected certifications
                const certSelect = $('#update_certifications');
                certSelect.val(null).trigger('change');
                
                if (certifications) {
                    const certIds = certifications.split(',');
                    certSelect.val(certIds).trigger('change');
                }

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
    });
    </script>
</body>
</html>