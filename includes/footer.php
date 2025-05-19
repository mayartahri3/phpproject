<header class="header">
    <div class="logo">
        <a href="<?= isAdmin() ? '../admin/index.php' : '../user/index.php' ?>">
            <h1>Tekup Certifications</h1>
        </a>
    </div>
    
    <nav class="nav">
        <?php if (isAdmin()): ?>
            <ul>
                <li><a href="../admin/index.php">Tableau de Bord</a></li>
                <li><a href="../admin/certified-students.php">Étudiants Certifiés</a></li>
                <li><a href="../admin/training-requests.php">Demandes de Formation</a></li>
                <li><a href="../admin/feedbacks.php">Feedbacks</a></li>
                <li><a href="../admin/posts.php">Posts</a></li>
            </ul>
        <?php else: ?>
            <ul>
                <li><a href="../user/index.php">Tableau de Bord</a></li>
                <li><a href="../user/certifications.php">Certifications</a></li>
                <li><a href="../user/request-training.php">Demander une Formation</a></li>
                <li><a href="../user/register-certification.php">Enregistrer une Certification</a></li>
                <li><a href="../user/trainings.php">Mes Formations</a></li>
            </ul>
        <?php endif; ?>
    </nav>
    
    <div class="user-menu">
        <span class="user-name"><?= $_SESSION['user_name'] ?></span>
        <a href="../auth/logout.php" class="logout-btn">Déconnexion</a>
    </div>
</header>