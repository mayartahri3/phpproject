<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';
$formationId = $_GET['formation_id'] ?? '';

// Récupérer les formations
$formations = getFormations($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $formationId = $_POST['formation_id'] ?? '';
        $note = $_POST['note'] ?? '';
        $commentaire = sanitize($_POST['commentaire'] ?? '');
        
        if (empty($formationId) || empty($note) || empty($commentaire)) {
            $error = 'Tous les champs sont obligatoires.';
        } elseif (!is_numeric($note) || $note < 1 || $note > 5) {
            $error = 'La note doit être comprise entre 1 et 5.';
        } else {
            // Vérifier si l'utilisateur a déjà donné un feedback pour cette formation
            $stmt = $pdo->prepare("SELECT * FROM feedbacks WHERE id_user = ? AND id_formation = ?");
            $stmt->execute([$_SESSION['user_id'], $formationId]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Vous avez déjà donné un feedback pour cette formation.';
            } else {
                // Enregistrer le feedback
                $stmt = $pdo->prepare("INSERT INTO feedbacks (id_user, id_formation, commentaire, note) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $formationId, $commentaire, $note])) {
                    $success = 'Votre feedback a été enregistré avec succès.';
                } else {
                    $error = 'Une erreur est survenue. Veuillez réessayer.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donner un Feedback - Tekup Certifications</title>
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
            --error-color: #dc2626;
            --error-bg: #fef2f2;
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
            color: var(--text-color);
        }

        .user-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .user-content {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            font-size: 1.1rem;
            text-color: #374151;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .feedback-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 600px;
            position: relative;
            overflow: hidden;
        }

        .feedback-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        .alert-error {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: white;
        }

        .form-control:hover {
            border-color: #9ca3af;
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .rating-group {
            display: grid;
            gap: 0.5rem;
        }

        .rating-option {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        .rating-option:hover {
            border-color: var(--primary-color);
            background-color: #eff6ff;
        }

        .rating-option input[type="radio"] {
            margin-right: 0.75rem;
            accent-color: var(--primary-color);
        }

        .rating-option.selected {
            border-color: var(--primary-color);
            background-color: #eff6ff;
        }

        .stars {
            display: flex;
            gap: 0.25rem;
            margin-left: auto;
        }

        .star {
            color: #fbbf24;
            font-size: 1.1rem;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .form-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }

        .input-group {
            position: relative;
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

        @media (max-width: 768px) {
            .user-content {
                padding: 1rem;
            }

            .feedback-card {
                padding: 2rem 1.5rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <div class="page-header">
                <h1><i class="fas fa-star"></i> Donner un Feedback</h1>
                <p>Partagez votre expérience et aidez-nous à améliorer nos formations</p>
            </div>
            
            <div class="feedback-card">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="feedback-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label for="formation_id" class="form-label">
                            <i class="fas fa-graduation-cap"></i> Formation
                        </label>
                        <div class="input-group">
                            <select id="formation_id" name="formation_id" class="form-control" required>
                                <option value="">Sélectionnez une formation</option>
                                <?php foreach ($formations as $formation): ?>
                                    <option value="<?= $formation['id'] ?>" <?= ($formationId == $formation['id']) ? 'selected' : '' ?>>
                                        <?= $formation['nom_certification'] ?> (Formateur: <?= $formation['formateur'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-star"></i> Évaluation
                        </label>
                        <div class="rating-group">
                            <label class="rating-option">
                                <input type="radio" name="note" value="5" required>
                                <span>5 - Excellent</span>
                                <div class="stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                </div>
                            </label>
                            <label class="rating-option">
                                <input type="radio" name="note" value="4" required>
                                <span>4 - Très bien</span>
                                <div class="stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                </div>
                            </label>
                            <label class="rating-option">
                                <input type="radio" name="note" value="3" required>
                                <span>3 - Bien</span>
                                <div class="stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                </div>
                            </label>
                            <label class="rating-option">
                                <input type="radio" name="note" value="2" required>
                                <span>2 - Moyen</span>
                                <div class="stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="fas fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                </div>
                            </label>
                            <label class="rating-option">
                                <input type="radio" name="note" value="1" required>
                                <span>1 - À améliorer</span>
                                <div class="stars">
                                    <i class="fas fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                    <i class="far fa-star star"></i>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="commentaire" class="form-label">
                            <i class="fas fa-comment"></i> Commentaire
                        </label>
                        <textarea 
                            id="commentaire" 
                            name="commentaire" 
                            class="form-control" 
                            placeholder="Partagez votre expérience, ce qui vous a plu, vos suggestions d'amélioration..."
                            required
                        ></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer mon feedback
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Enhanced JavaScript for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Rating selection enhancement
            const ratingOptions = document.querySelectorAll('.rating-option');
            const radioButtons = document.querySelectorAll('input[name="note"]');
            
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    ratingOptions.forEach(option => {
                        option.classList.remove('selected');
                    });
                    this.closest('.rating-option').classList.add('selected');
                });
            });
            
            // Form validation enhancement
            const form = document.querySelector('.feedback-form');
            const submitBtn = document.querySelector('.btn-submit');
            
            form.addEventListener('submit', function(e) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                submitBtn.disabled = true;
            });
            
            // Auto-resize textarea
            const textarea = document.querySelector('#commentaire');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            
            // Form field focus effects
            const formControls = document.querySelectorAll('.form-control');
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                control.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
        });
    </script>
    <script src="../assets/js/user.js"></script>
</body>
</html>