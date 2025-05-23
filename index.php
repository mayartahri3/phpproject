<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
}

// Récupérer les certifications par domaine
$certificationsByDomain = getCertificationsByDomain($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekup Certifications - Accueil</title>
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
            background: var(--white);
            color: var(--gray-700);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
     
            top: 0;
            z-index: 1000;
            padding: 1.25rem 0;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-800));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-blue-light), var(--blue-600));
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
            box-shadow: var(--shadow);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--blue-800) 100%);
            color: var(--white);
            padding: 5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero .slogan {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            color: var(--blue-100);
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .hero-btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-lg);
        }

        .hero-btn-primary {
            background: var(--white);
            color: var(--primary-blue);
        }

        .hero-btn-primary:hover {
            background: var(--gray-50);
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        .hero-btn-secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        .features {
            padding: 5rem 2rem;
            background: var(--gray-50);
        }

        .features h2 {
            font-size: 2.25rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--gray-800);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1rem;
        }

        .feature-card p {
            font-size: 1rem;
            color: var(--gray-600);
        }

        .certifications {
            padding: 5rem 2rem;
            background: var(--white);
        }

        .certifications h2 {
            font-size: 2.25rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--gray-800);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .certifications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .certification-category {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .certification-category:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .certification-category h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--blue-100);
        }

        .certification-category p {
            font-size: 1rem;
            color: var(--gray-600);
        }

        .footer {
            background: linear-gradient(135deg, var(--gray-800), var(--gray-700));
            color: var(--gray-100);
            padding: 3rem 2rem;
            text-align: center;
        }

        .footer p {
            font-size: 0.95rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @media (max-width: 1024px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .slogan {
                font-size: 1.25rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .features-grid, .certifications-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 3rem 1.5rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero .slogan {
                font-size: 1.1rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .hero-btn {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }

            .features, .certifications {
                padding: 3rem 1.5rem;
            }

            .features h2, .certifications h2 {
                font-size: 1.75rem;
            }

            .feature-card, .certification-category {
                padding: 1.5rem;
            }
        }

        @media (max-width: 640px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .auth-buttons {
                flex-direction: column;
                width: 100%;
                align-items: center;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .hero {
                padding: 2rem 1rem;
            }

            .hero h1 {
                font-size: 1.75rem;
            }

            .hero .slogan {
                font-size: 1rem;
            }

            .hero p {
                font-size: 0.95rem;
            }

            .features h2, .certifications h2 {
                font-size: 1.5rem;
            }

            .footer {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <a href="index.php">
                    <i class="fas fa-graduation-cap"></i>
                    <h1>Tekup Certifications</h1>
                </a>
            </div>
            
            <div class="auth-buttons">
                <a href="auth/login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
                <a href="auth/register.php" class="btn btn-secondary"><i class="fas fa-user-plus"></i> S'inscrire</a>
            </div>
        </div>
    </header>
    
    <section class="hero">
        <div class="hero-content">
            <h1 class="fade-in">Plateforme de Certifications Professionnelles</h1>
            <div class="slogan fade-in">Be Tekuper, Be Certified</div>
            <p class="fade-in">Découvrez les certifications offertes gratuitement par la faculté Tekup et rejoignez nos sessions de formation.</p>
            
            <div class="hero-buttons fade-in">
                <a href="auth/register.php" class="hero-btn hero-btn-primary"><i class="fas fa-user-plus"></i> S'inscrire</a>
                <a href="auth/login.php" class="hero-btn hero-btn-secondary"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
            </div>
        </div>
    </section>
    
    <section class="features">
        <h2><i class="fas fa-star"></i> Nos Services</h2>
        
        <div class="features-grid">
            <div class="feature-card fade-in">
                <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                <h3>Certifications Gratuites</h3>
                <p>Accédez à des certifications professionnelles reconnues, offertes gratuitement par la faculté Tekup.</p>
            </div>
            
            <div class="feature-card fade-in">
                <div class="feature-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <h3>Formations par des Experts</h3>
                <p>Bénéficiez de formations dispensées par des étudiants certifiés de la faculté.</p>
            </div>
            
            <div class="feature-card fade-in">
                <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
                <h3>Demandes Personnalisées</h3>
                <p>Soumettez des demandes pour ouvrir des sessions de formation pour les certifications qui vous intéressent.</p>
            </div>
        </div>
    </section>
    
    <section class="certifications">
        <h2><i class="fas fa-book"></i> Certifications Disponibles</h2>
        
        <div class="certifications-grid">
            <?php foreach ($certificationsByDomain as $domain): ?>
                <div class="certification-category fade-in">
                    <h3><?= htmlspecialchars($domain['domaine']) ?></h3>
                    <p><?= htmlspecialchars($domain['certifications']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <footer class="footer">
        <p>© <?= date('Y') ?> Tekup Certifications. Tous droits réservés.</p>
    </footer>
</body>
</html>