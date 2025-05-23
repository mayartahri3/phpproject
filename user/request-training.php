<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';
$selectedCertification = $_GET['certification_id'] ?? '';

// Récupérer toutes les certifications
$certifications = getAllCertifications($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['csrf_token'])) {
        // Remplacer validateCSRFToken par verifyCSRFToken
        if (verifyCSRFToken($_POST['csrf_token'])) {
            $certificationId = isset($_POST['certification']) ? intval($_POST['certification']) : 0;
            
            if ($certificationId <= 0) {
                $error = 'Veuillez sélectionner une certification valide.';
            } else {
                // Vérifier si une demande existe déjà
                $stmt = $pdo->prepare("SELECT * FROM demandes_formations WHERE id_user = ? AND id_certification = ? AND statut = 'en attente'");
                $stmt->execute([$_SESSION['user_id'], $certificationId]);
                
                if ($stmt->rowCount() > 0) {
                    $error = 'Vous avez déjà fait une demande pour cette certification.';
                } else {
                    // Créer la demande
                    $stmt = $pdo->prepare("INSERT INTO demandes_formations (id_user, id_certification) VALUES (?, ?)");
                    
                    if ($stmt->execute([$_SESSION['user_id'], $certificationId])) {
                        $success = 'Votre demande a été envoyée avec succès. Vous serez notifié lorsqu\'elle sera traitée.';
                    } else {
                        $error = 'Une erreur est survenue. Veuillez réessayer.';
                    }
                }
            }
        } else {
            $error = 'Token de sécurité invalide. Veuillez rafraîchir la page et réessayer.';
        }
    } else {
        $error = 'Token CSRF manquant.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Formation - Tekup Certifications</title>
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
            padding: 2.5rem;
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--blue-100);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--blue-700), var(--primary-blue-light));
        }

        .page-header h1 {
            font-size: 2.75rem;
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

        .page-header p {
            font-size: 1.2rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
            line-height: 1.7;
        }

        .training-request-card {
            background: var(--white);
            border-radius: 24px;
            padding: 3.5rem;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 650px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--blue-100);
        }

        .training-request-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-blue), var(--blue-700), var(--primary-blue-light));
        }

        .card-icon {
            position: absolute;
            top: -25px;
            right: 2rem;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.75rem;
            box-shadow: var(--shadow-lg);
            border: 4px solid var(--white);
        }

        .alert {
            padding: 1.5rem 1.75rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            font-weight: 500;
            animation: slideIn 0.4s ease-out;
            border: 1px solid;
            line-height: 1.6;
        }

        .alert-error {
            background-color: var(--error-bg);
            color: var(--error-color);
            border-color: #fecaca;
        }

        .alert-success {
            background-color: var(--success-bg);
            color: var(--success-color);
            border-color: #bbf7d0;
        }

        .alert i {
            font-size: 1.25rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .form-info {
            background: linear-gradient(135deg, var(--blue-50), var(--blue-100));
            border: 1px solid var(--blue-200);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            color: var(--blue-800);
        }

        .form-info i {
            color: var(--primary-blue);
            margin-top: 2px;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .form-info-content h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--blue-800);
        }

        .form-info-content p {
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        .form-group {
            margin-bottom: 2.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--gray-700);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-label i {
            color: var(--primary-blue);
            font-size: 1.2rem;
        }

        .form-control {
            width: 100%;
            padding: 1.25rem 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background-color: var(--gray-50);
            color: var(--gray-700);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px var(--blue-100);
            background-color: var(--white);
        }

        .form-control:hover {
            border-color: var(--gray-300);
            background-color: var(--white);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23374151' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 1.25rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 3.5rem;
        }

        select.form-control:focus {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%232563eb' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        .btn-submit {
            width: 100%;
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            color: var(--white);
            border: none;
            border-radius: 16px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            box-shadow: var(--shadow);
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, var(--blue-700), var(--blue-800));
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .certification-option {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-700);
        }

        .certification-option:last-child {
            border-bottom: none;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        .loading {
            animation: pulse 2s infinite;
        }

        @media (max-width: 768px) {
            .user-content {
                padding: 1rem;
            }

            .training-request-card {
                padding: 2.5rem 1.5rem;
            }

            .page-header {
                margin-bottom: 2rem;
                padding: 2rem 1.5rem;
            }

            .page-header h1 {
                font-size: 2.25rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .card-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                right: 1rem;
                top: -20px;
            }

            .form-control {
                padding: 1rem 1.25rem;
                font-size: 1rem;
            }

            .btn-submit {
                padding: 1.25rem 1.5rem;
                font-size: 1.1rem;
            }
        }

        .form-control:valid:not(:placeholder-shown) {
            border-color: var(--success-color);
        }

        .steps-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 0.5rem;
        }

        .step {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--gray-300);
        }

        .step.active {
            background-color: var(--primary-blue);
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <div class="page-header">
                <h1>
                    <i class="fas fa-graduation-cap"></i>
                    Demande de Formation
                </h1>
                <p>Demandez une formation pour obtenir la certification qui vous intéresse.<br>
                   Notre équipe traitera votre demande dans les plus brefs délais.</p>
            </div>
            
            <div class="training-request-card">
                <div class="card-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?= $error ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= $success ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="form-info">
                    <i class="fas fa-lightbulb"></i>
                    <div class="form-info-content">
                        <h3>Comment ça marche ?</h3>
                        <p>Sélectionnez la certification qui vous intéresse ci-dessous. Une fois votre demande soumise, notre équipe pédagogique l'examinera et vous contactera pour organiser la formation correspondante.</p>
                    </div>
                </div>
                
                <form method="post" action="" class="training-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="certification" class="form-label">
                            <i class="fas fa-certificate"></i>
                            Certification souhaitée
                        </label>
                        <select class="form-control" id="certification" name="certification" required>
                            <option value="">Choisissez la certification qui vous intéresse</option>
                            <?php
                            foreach ($certifications as $cert) {
                                $selected = ($selectedCertification == $cert['id']) ? 'selected' : '';
                                echo "<option value='" . $cert['id'] . "' class='certification-option' " . $selected . ">";
                                echo htmlspecialchars($cert['nom_certification']) . " (" . htmlspecialchars($cert['domaine']) . ")";
                                echo "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer ma demande de formation
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.training-form');
            const submitBtn = document.getElementById('submitBtn');
            const certificationSelect = document.getElementById('certification');
            
            // Form submission handling
            form.addEventListener('submit', function(e) {
                if (certificationSelect.value === '') {
                    e.preventDefault();
                    
                    // Show validation message
                    certificationSelect.style.borderColor = 'var(--error-color)';
                    certificationSelect.focus();
                    
                    // Reset border color after a few seconds
                    setTimeout(() => {
                        certificationSelect.style.borderColor = '';
                    }, 3000);
                    
                    return false;
                }
                
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
            });
            
            // Enhanced form validation
            function validateForm() {
                const isValid = certificationSelect.value !== '';
                
                if (isValid) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                }
            }
            
            certificationSelect.addEventListener('change', function() {
                validateForm();
                // Reset any error styling
                this.style.borderColor = '';
            });
            
            // Initial validation
            validateForm();
            
            // Enhanced focus effects
            const formControls = document.querySelectorAll('.form-control');
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    this.closest('.form-group').classList.add('focused');
                });
                
                control.addEventListener('blur', function() {
                    this.closest('.form-group').classList.remove('focused');
                });
            });
            
            // Success message auto-hide
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        successAlert.remove();
                    }, 300);
                }, 6000);
            }
            
            // URL parameter handling for pre-selected certification
            const urlParams = new URLSearchParams(window.location.search);
            const certificationId = urlParams.get('certification_id');
            if (certificationId) {
                certificationSelect.value = certificationId;
                validateForm();
            }
        });
    </script>
    <script src="../assets/js/user.js"></script>
</body>
</html>