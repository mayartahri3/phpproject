<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekup Certifications</title>
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

        .header {
            background: var(--white);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--blue-100);
            box-shadow: var(--shadow-lg);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-blue), var(--blue-700), var(--primary-blue-light));
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1001;
        }

        .logo a {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .logo a:hover {
            transform: scale(1.02);
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .logo a:hover .logo-icon {
            transform: rotate(10deg);
            box-shadow: var(--shadow-lg);
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .nav {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .nav ul {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            padding: 0;
        }

        .nav ul li {
            position: relative;
        }

        .nav ul li a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav ul li a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav ul li a:hover {
            color: var(--primary-blue);
            background: var(--blue-50);
            transform: translateY(-2px);
        }

        .nav ul li a:hover::before {
            left: 100%;
        }

        .nav ul li a.active {
            color: var(--white);
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            box-shadow: var(--shadow);
        }

        .nav ul li a.active::before {
            display: none;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            z-index: 1001;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.25rem;
            background: var(--gray-50);
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .user-info:hover {
            background: var(--blue-50);
            border-color: var(--blue-200);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-blue), var(--blue-700));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1rem;
            font-weight: 600;
            box-shadow: var(--shadow);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.95rem;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: var(--white);
        }

        .logout-btn:active {
            transform: translateY(0);
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray-600);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            background: var(--gray-100);
            color: var(--primary-blue);
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            box-shadow: var(--shadow-xl);
            z-index: 1000;
        }

        .mobile-menu.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        .mobile-menu ul {
            list-style: none;
            padding: 1rem;
            margin: 0;
        }

        .mobile-menu ul li {
            margin-bottom: 0.5rem;
        }

        .mobile-menu ul li:last-child {
            margin-bottom: 0;
        }

        .mobile-menu ul li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .mobile-menu ul li a:hover {
            background: var(--blue-50);
            color: var(--primary-blue);
        }

        .mobile-user-info {
            padding: 1rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .nav ul {
                gap: 0.25rem;
            }
            
            .nav ul li a {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 1rem;
                height: 70px;
            }

            .logo h1 {
                font-size: 1.5rem;
            }

            .logo-icon {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }

            .nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .user-info {
                padding: 0.5rem 1rem;
            }

            .user-details {
                display: none;
            }

            .logout-btn {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 640px) {
            .header-container {
                height: 65px;
            }

            .logo h1 {
                display: none;
            }

            .user-info {
                padding: 0.5rem;
            }

            .logout-btn {
                padding: 0.5rem;
            }

            .logout-btn span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <a href="<?= isAdmin() ? '../admin/index.php' : '../user/index.php' ?>">
                    <div class="logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h1>Tekup Certifications</h1>
                </a>
            </div>
            
            <nav class="nav">
                <?php if (isAdmin()): ?>
                    <ul>
                        <li><a href="../admin/index.php"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</a></li>
                        <li><a href="../admin/certified-students.php"><i class="fas fa-user-graduate"></i> Étudiants Certifiés</a></li>
                        <li><a href="../admin/training-requests.php"><i class="fas fa-clipboard-list"></i> Demandes de Formation</a></li>
                        <li><a href="../admin/feedbacks.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
                        <li><a href="../admin/posts.php"><i class="fas fa-bullhorn"></i> Posts</a></li>
                    </ul>
                <?php else: ?>
                    <ul>
                        <li><a href="../user/index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                        <li><a href="../user/certifications.php"><i class="fas fa-certificate"></i>Certifications</a></li>
                        <li><a href="../user/request-training.php"><i class="fas fa-clipboard-list"></i>Demander une Formation</a></li>
                        <li><a href="../user/register-certification.php"><i class="fas fa-file-alt"></i>Enregistrer une Certification</a></li>
                        <li><a href="../user/feedback.php"><i class="fas fa-comments"></i>Feedbacks</a></li>
                        <li><a href="../user/trainings.php"><i class="fas fa-chalkboard-teacher"></i>Mes Formations</a></li>
                    </ul>
                <?php endif; ?>
            </nav>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                    </div>
                    <a href="../user/profile.php" class="user-details">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <span class="user-role"><?= isAdmin() ? 'Administrateur' : 'Utilisateur' ?></span>
                    </a>
                </div>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>

            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="mobile-menu" id="mobileMenu">
            <?php if (isAdmin()): ?>
                <ul>
                    <li><a href="../admin/index.php"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</a></li>
                    <li><a href="../admin/certified-students.php"><i class="fas fa-user-graduate"></i> Étudiants Certifiés</a></li>
                    <li><a href="../admin/training-requests.php"><i class="fas fa-clipboard-list"></i> Demandes de Formation</a></li>
                    <li><a href="../admin/feedbacks.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
                    <li><a href="../admin/posts.php"><i class="fas fa-bullhorn"></i> Posts</a></li>
                </ul>
            <?php else: ?>
                <ul>
                    <li><a href="../user/index.php"><i class="fas fa-tachometer-alt"></i>Tableau de Bord</a></li>
                    <li><a href="../user/certifications.php"><i class="fas fa-certificate"></i>Certifications</a></li>
                    <li><a href="../user/request-training.php"><i class="fas fa-clipboard-list"></i>Demander une Formation</a></li>
                    <li><a href="../user/register-certification.php"><i class="fas fa-file-alt"></i>Enregistrer une Certification</a></li>
                    <li><a href="../user/feedback.php"><i class="fas fa-comments"></i>Feedbacks</a></li>
                    <li><a href="../user/trainings.php"><i class="fas fa-chalkboard-teacher"></i>Mes Formations</a></li>
                </ul>
            <?php endif; ?>
            <div class="mobile-user-info">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                    </div>
                    <a href="../user/profile.php" class="user-details">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <span class="user-role"><?= isAdmin() ? 'Administrateur' : 'Utilisateur' ?></span>
                    </a>
                </div>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </header>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (!mobileMenu.contains(event.target) && !toggleBtn.contains(event.target)) {
                mobileMenu.classList.remove('active');
            }
        });

        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const header = document.querySelector('.header');
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });
    </script>
</body>
</html>