<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Récupérer toutes les certifications
$certifications = getAllCertifications($pdo);

// Regrouper les certifications par domaine
$certificationsByDomain = [];
foreach ($certifications as $cert) {
    $certificationsByDomain[$cert['domaine']][] = $cert;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certifications - Tekup Certifications</title>
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
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            text-align: center;
            margin-bottom: 4rem;
            padding: 3rem;
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
            font-size: 3rem;
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
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        .search-filter-section {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--blue-100);
        }

        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--gray-50);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px var(--blue-100);
            background-color: var(--white);
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 0.75rem 1.5rem;
            background: var(--gray-100);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: var(--gray-600);
        }

        .filter-tab.active,
        .filter-tab:hover {
            background: var(--primary-blue);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .certifications-list {
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        .certification-domain-section {
            background: var(--white);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--blue-100);
            position: relative;
            overflow: hidden;
        }

        .certification-domain-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--blue-700));
        }

        .domain-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .domain-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .domain-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
        }

        .domain-count {
            background: var(--blue-100);
            color: var(--primary-blue);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .certification-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .certification-card {
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

        .certification-card::before {
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

        .certification-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--blue-200);
        }

        .certification-card:hover::before {
            transform: scaleX(1);
        }

        .certification-header {
            margin-bottom: 1.5rem;
        }

        .certification-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .certification-description {
            color: var(--gray-600);
            line-height: 1.7;
            flex-grow: 1;
            margin-bottom: 2rem;
        }

        .certification-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray-500);
        }

        .meta-item i {
            color: var(--primary-blue);
        }

        .certification-actions {
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

        .certification-domain-section {
            animation: fadeInUp 0.6s ease-out;
        }

        .certification-card {
            animation: fadeInUp 0.6s ease-out;
        }

        @media (max-width: 768px) {
            .user-content {
                padding: 1rem;
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

            .stats-bar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .certification-cards {
                grid-template-columns: 1fr;
            }

            .search-bar {
                flex-direction: column;
            }

            .filter-tabs {
                justify-content: center;
            }

            .domain-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .domain-title {
                font-size: 1.5rem;
            }

            .certification-domain-section {
                padding: 1.5rem;
            }
        }

        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="user-container">
        <?php include '../includes/header.php'; ?>
        
        <div class="user-content">
            <div class="page-header">
                <h1>
                    <i class="fas fa-certificate"></i>
                    Certifications Disponibles
                </h1>
                <p>Découvrez notre catalogue complet de certifications professionnelles.<br>
                   Choisissez la formation qui correspond à vos objectifs de carrière.</p>
                
                <div class="stats-bar">
                    <div class="stat-item">
                        <span class="stat-number" id="total-certifications"><?= count($certifications) ?></span>
                        <span class="stat-label">Certifications</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= count($certificationsByDomain) ?></span>
                        <span class="stat-label">Domaines</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Reconnues</span>
                    </div>
                </div>
            </div>
            
            <div class="search-filter-section">
                <div class="search-bar">
                    <input type="text" class="search-input" id="searchInput" placeholder="Rechercher une certification...">
                </div>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-domain="all">
                        <i class="fas fa-th-large"></i> Tous les domaines
                    </button>
                    <?php foreach ($certificationsByDomain as $domain => $certs): ?>
                        <button class="filter-tab" data-domain="<?= htmlspecialchars(strtolower($domain)) ?>">
                            <?= getDomainIcon($domain) ?> <?= htmlspecialchars($domain) ?> (<?= count($certs) ?>)
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="certifications-list" id="certificationsList">
                <?php if (empty($certificationsByDomain)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>Aucune certification trouvée</h3>
                        <p>Il n'y a actuellement aucune certification disponible.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($certificationsByDomain as $domain => $certs): ?>
                        <div class="certification-domain-section" data-domain="<?= htmlspecialchars(strtolower($domain)) ?>">
                            <div class="domain-header">
                                <div class="domain-title">
                                    <div class="domain-icon">
                                        <?= getDomainIcon($domain) ?>
                                    </div>
                                    <?= htmlspecialchars($domain) ?>
                                </div>
                                <div class="domain-count">
                                    <?= count($certs) ?> certification<?= count($certs) > 1 ? 's' : '' ?>
                                </div>
                            </div>
                            
                            <div class="certification-cards">
                                <?php foreach ($certs as $cert): ?>
                                    <div class="certification-card" data-name="<?= htmlspecialchars(strtolower($cert['nom_certification'])) ?>" data-description="<?= htmlspecialchars(strtolower($cert['description'])) ?>">
                                        <div class="certification-header">
                                            <h3 class="certification-title"><?= htmlspecialchars($cert['nom_certification']) ?></h3>
                                        </div>
                                        
                                        <p class="certification-description">
                                            <?= htmlspecialchars($cert['description']) ?>
                                        </p>
                                        
                                        <div class="certification-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-tag"></i>
                                                <span><?= htmlspecialchars($cert['domaine']) ?></span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-award"></i>
                                                <span>Certification officielle</span>
                                            </div>
                                        </div>
                                        
                                        <div class="certification-actions">
                                            <a href="request-training.php?certification_id=<?= $cert['id'] ?>" class="btn">
                                                <i class="fas fa-graduation-cap"></i>
                                                Demander une formation
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterTabs = document.querySelectorAll('.filter-tab');
            const certificationSections = document.querySelectorAll('.certification-domain-section');
            const certificationCards = document.querySelectorAll('.certification-card');
            const totalCertificationsElement = document.getElementById('total-certifications');
            
            let activeFilter = 'all';
            
            // Search functionality
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;
                
                certificationSections.forEach(section => {
                    const domain = section.dataset.domain;
                    const shouldShowSection = activeFilter === 'all' || activeFilter === domain;
                    
                    if (!shouldShowSection) {
                        section.classList.add('hidden');
                        return;
                    }
                    
                    const cardsInSection = section.querySelectorAll('.certification-card');
                    let hasVisibleCards = false;
                    
                    cardsInSection.forEach(card => {
                        const name = card.dataset.name;
                        const description = card.dataset.description;
                        const matchesSearch = searchTerm === '' || 
                                            name.includes(searchTerm) || 
                                            description.includes(searchTerm);
                        
                        if (matchesSearch) {
                            card.style.display = 'flex';
                            hasVisibleCards = true;
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    if (hasVisibleCards) {
                        section.classList.remove('hidden');
                    } else {
                        section.classList.add('hidden');
                    }
                });
                
                // Update total count
                totalCertificationsElement.textContent = visibleCount;
                
                // Show empty state if no results
                const certificationsList = document.getElementById('certificationsList');
                const existingEmptyState = certificationsList.querySelector('.empty-state');
                
                if (visibleCount === 0 && searchTerm !== '') {
                    if (!existingEmptyState) {
                        const emptyState = document.createElement('div');
                        emptyState.className = 'empty-state';
                        emptyState.innerHTML = `
                            <i class="fas fa-search"></i>
                            <h3>Aucun résultat trouvé</h3>
                            <p>Aucune certification ne correspond à votre recherche "${searchInput.value}"</p>
                        `;
                        certificationsList.appendChild(emptyState);
                    }
                } else if (existingEmptyState) {
                    existingEmptyState.remove();
                }
            }
            
            // Filter functionality
            function applyFilter(domain) {
                activeFilter = domain;
                
                // Update active tab
                filterTabs.forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.domain === domain);
                });
                
                performSearch();
            }
            
            // Event listeners
            searchInput.addEventListener('input', performSearch);
            
            filterTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    applyFilter(tab.dataset.domain);
                });
            });
            
            // Enhanced card interactions
            certificationCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Smooth scroll to sections
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const domain = this.dataset.domain;
                    if (domain !== 'all') {
                        setTimeout(() => {
                            const section = document.querySelector(`[data-domain="${domain}"]`);
                            if (section && !section.classList.contains('hidden')) {
                                section.scrollIntoView({ 
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        }, 100);
                    }
                });
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    searchInput.focus();
                }
                
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.blur();
                }
            });
            
            // Add search hint
            let searchHintTimeout;
            searchInput.addEventListener('focus', function() {
                clearTimeout(searchHintTimeout);
                searchHintTimeout = setTimeout(() => {
                    if (this.value === '') {
                        this.placeholder = 'Tapez pour rechercher... (Ctrl+F)';
                    }
                }, 1000);
            });
            
            searchInput.addEventListener('blur', function() {
                clearTimeout(searchHintTimeout);
                this.placeholder = 'Rechercher une certification...';
            });
        });
        
        // Helper function for domain icons (to be defined in PHP)
        <?php
        function getDomainIcon($domain) {
            $icons = [
                'Informatique' => '<i class="fas fa-laptop-code"></i>',
                'Réseau' => '<i class="fas fa-network-wired"></i>',
                'Sécurité' => '<i class="fas fa-shield-alt"></i>',
                'Cloud' => '<i class="fas fa-cloud"></i>',
                'Data' => '<i class="fas fa-database"></i>',
                'IA' => '<i class="fas fa-robot"></i>',
                'Web' => '<i class="fas fa-globe"></i>',
                'Mobile' => '<i class="fas fa-mobile-alt"></i>',
                'DevOps' => '<i class="fas fa-cogs"></i>',
                'Blockchain' => '<i class="fas fa-link"></i>'
            ];
            
            return $icons[$domain] ?? '<i class="fas fa-certificate"></i>';
        }
        ?>
    </script>
    <script src="../assets/js/user.js"></script>
</body>
</html>