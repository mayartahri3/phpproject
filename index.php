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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            background-color: #0066cc;
            color: #fff;
            padding: 60px 20px;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .hero-btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            transition: all 0.2s;
        }
        
        .hero-btn-primary {
            background-color: #fff;
            color: #0066cc;
        }
        
        .hero-btn-primary:hover {
            background-color: #f0f0f0;
            text-decoration: none;
        }
        
        .hero-btn-secondary {
            background-color: transparent;
            color: #fff;
            border: 2px solid #fff;
        }
        
        .hero-btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }
        
        .features {
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .features h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: #0066cc;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .certifications {
            padding: 60px 20px;
            background-color: #f8f9fa;
        }
        
        .certifications h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2rem;
        }
        
        .certifications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .certification-category {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .certification-category h3 {
            font-size: 1.3rem;
            color: #0066cc;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .footer {
            background-color: #333;
            color: #fff;
            padding: 30px 20px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .features h2, .certifications h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <h1>Tekup Certifications</h1>
            </a>
        </div>
        
        <div class="auth-buttons">
            <a href="auth/login.php" class="btn">Se connecter</a>
            <a href="auth/register.php" class="btn btn-secondary">S'inscrire</a>
        </div>
    </header>
    
    <section class="hero">
        <h1>Plateforme de Certifications Professionnelles</h1>
        <p>Découvrez les certifications offertes gratuitement par la faculté Tekup et rejoignez nos sessions de formation.</p>
        
        <div class="hero-buttons">
            <a href="auth/register.php" class="hero-btn hero-btn-primary">S'inscrire</a>
            <a href="auth/login.php" class="hero-btn hero-btn-secondary">Se connecter</a>
        </div>
    </section>
    
    <section class="features">
        <h2>Nos Services</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🎓</div>
                <h3>Certifications Gratuites</h3>
                <p>Accédez à des certifications professionnelles reconnues, offertes gratuitement par la faculté Tekup.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">👨‍🏫</div>
                <h3>Formations par des Experts</h3>
                <p>Bénéficiez de formations dispensées par des étudiants certifiés de la faculté.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Demandes Personnalisées</h3>
                <p>Soumettez des demandes pour ouvrir des sessions de formation pour les certifications qui vous intéressent.</p>
            </div>
        </div>
    </section>
    
    <section class="certifications">
        <h2>Certifications Disponibles</h2>
        
        <div class="certifications-grid">
            <?php foreach ($certificationsByDomain as $domain): ?>
                <div class="certification-category">
                    <h3><?= $domain['domaine'] ?></h3>
                    <p><?= $domain['certifications'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Tekup Certifications. Tous droits réservés.</p>
    </footer>
</body>
</html>