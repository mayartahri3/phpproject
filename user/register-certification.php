<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';

// Récupérer toutes les certifications
$certifications = getAllCertifications($pdo);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Erreur de sécurité. Veuillez réessayer.';
    } else {
        $certificationId = $_POST['certification_id'] ?? '';
        $dateCertification = $_POST['date_certification'] ?? '';
        
        if (empty($certificationId) || empty($dateCertification)) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            // Vérifier si l'utilisateur a déjà enregistré cette certification
            $stmt = $pdo->prepare("SELECT * FROM etudiants_certifies WHERE id_user = ? AND id_certification = ?");
            $stmt->execute([$_SESSION['user_id'], $certificationId]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Vous avez déjà enregistré cette certification.';
            } else {
                // Enregistrer la certification
                $stmt = $pdo->prepare("INSERT INTO etudiants_certifies (id_user, id_certification, date_certification) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $certificationId, $dateCertification])) {
                    $success = 'Votre certification a été enregistrée avec succès.';
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
    <title>Enregistrer une Certification - Tekup Certifications</title>
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
            padding: 2rem;
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--blue-100);
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header p {
            font-size: 1.1rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
        }

        .certification-card {
            background: var(--white);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 600px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--blue-100);
        }

        .certification-card::before {
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
            top: -20px;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            box-shadow: var(--shadow-lg);
        }

        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            animation: slideIn 0.4s ease-out;
            border: 1px solid;
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
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--gray-700);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary-blue);
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            font-size: 1rem;
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
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 3rem;
        }

        select.form-control:focus {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%232563eb' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            z-index: 1;
        }

        .btn-submit {
            width: 100%;
            padding: 1.25rem 2rem;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            color: var(--white);
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1.5rem;
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

        .form-info {
            background: var(--blue-50);
            border: 1px solid var(--blue-200);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--blue-700);
        }

        .form-info i {
            color: var(--primary-blue);
            margin-top: 2px;
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

            .certification-card {
                padding: 2rem 1.5rem;
            }

            .page-header {
                margin-bottom: 2rem;
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .card-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
                right: 1rem;
            }
        }

        .form-control:valid {
            border-color: var(--success-color);
        }

        .certification-option {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .certification-option:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <div class="page-header">
                <h1><i class="fas fa-certificate"></i> Enregistrer une Certification</h1>
                <p>Ajoutez vos certifications obtenues à votre profil professionnel</p>
            </div>
            
            <div class="certification-card">
                <div class="card-icon">
                    <i class="fas fa-award"></i>
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
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Information importante :</strong> Assurez-vous que la date d'obtention correspond exactement à celle mentionnée sur votre certificat officiel.
                    </div>
                </div>
                
                <form method="POST" action="" class="certification-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label for="certification_id" class="form-label">
                            <i class="fas fa-graduation-cap"></i>
                            Certification
                        </label>
                        <select id="certification_id" name="certification_id" class="form-control" required>
                            <option value="">Sélectionnez une certification</option>
                            <?php foreach ($certifications as $cert): ?>
                                <option value="<?= $cert['id'] ?>" class="certification-option">
                                    <?= $cert['nom_certification'] ?> (<?= $cert['domaine'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_certification" class="form-label">
                            <i class="fas fa-calendar-alt"></i>
                            Date d'obtention
                        </label>
                        <div class="input-group">
                            <input 
                                type="date" 
                                id="date_certification" 
                                name="date_certification" 
                                class="form-control" 
                                required 
                                max="<?= date('Y-m-d') ?>"
                            >
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-save"></i>
                        Enregistrer ma certification
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.certification-form');
            const submitBtn = document.getElementById('submitBtn');
            const certificationSelect = document.getElementById('certification_id');
            const dateInput = document.getElementById('date_certification');
            
            // Form submission handling
            form.addEventListener('submit', function(e) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...';
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
            });
            
            // Enhanced form validation
            function validateForm() {
                const isValid = certificationSelect.value && dateInput.value;
                submitBtn.disabled = !isValid;
                
                if (isValid) {
                    submitBtn.style.opacity = '1';
                } else {
                    submitBtn.style.opacity = '0.7';
                }
            }
            
            certificationSelect.addEventListener('change', validateForm);
            dateInput.addEventListener('change', validateForm);
            
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
            
            // Date input enhancement
            dateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const today = new Date();
                
                if (selectedDate > today) {
                    this.setCustomValidity('La date ne peut pas être dans le futur');
                } else {
                    this.setCustomValidity('');
                }
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
                }, 5000);
            }
        });
    </script>
    <script src="../assets/js/user.js"></script>
</body>
</html>