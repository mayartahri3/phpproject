document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire de connexion
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            let isValid = true;
            
            // Validation de l'email
            if (!validateEmail(email)) {
                alert('Veuillez entrer une adresse email valide.');
                isValid = false;
            }
            
            // Validation du mot de passe
            if (password.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Validation du formulaire d'inscription
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const nom = document.getElementById('nom').value;
            const prenom = document.getElementById('prenom').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            let isValid = true;
            
            // Validation des champs obligatoires
            if (!nom || !prenom || !email || !password || !confirmPassword) {
                alert('Tous les champs sont obligatoires.');
                isValid = false;
            }
            
            // Validation de l'email
            if (!validateEmail(email)) {
                alert('Veuillez entrer une adresse email valide.');
                isValid = false;
            }
            
            // Validation du mot de passe
            if (password.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                isValid = false;
            }
            
            // Vérification de la correspondance des mots de passe
            if (password !== confirmPassword) {
                alert('Les mots de passe ne correspondent pas.');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Fonction de validation d'email
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});