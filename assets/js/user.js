document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire de demande de formation
    const requestForm = document.querySelector('form[action*="request-training.php"]');
    if (requestForm) {
        requestForm.addEventListener('submit', function(e) {
            const certificationId = document.getElementById('certification_id').value;
            
            if (!certificationId) {
                alert('Veuillez sélectionner une certification.');
                e.preventDefault();
            }
        });
    }
    
    // Validation du formulaire d'enregistrement de certification
    const registerCertForm = document.querySelector('form[action*="register-certification.php"]');
    if (registerCertForm) {
        registerCertForm.addEventListener('submit', function(e) {
            const certificationId = document.getElementById('certification_id').value;
            const dateCertification = document.getElementById('date_certification').value;
            
            if (!certificationId || !dateCertification) {
                alert('Tous les champs sont obligatoires.');
                e.preventDefault();
            }
            
            // Vérifier que la date n'est pas dans le futur
            if (dateCertification && new Date(dateCertification) > new Date()) {
                alert('La date d\'obtention ne peut pas être dans le futur.');
                e.preventDefault();
            }
        });
    }
    
    // Validation du formulaire de feedback
    const feedbackForm = document.querySelector('form[action*="feedback.php"]');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            const formationId = document.getElementById('formation_id').value;
            const note = document.getElementById('note').value;
            const commentaire = document.getElementById('commentaire').value;
            
            if (!formationId || !note || !commentaire) {
                alert('Tous les champs sont obligatoires.');
                e.preventDefault();
            }
        });
    }
    
    // Confirmation d'inscription à une formation
    const registerTrainingLinks = document.querySelectorAll('a[href*="trainings.php?action=register"]');
    if (registerTrainingLinks) {
        registerTrainingLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous inscrire à cette formation ?')) {
                    e.preventDefault();
                }
            });
        });
    }
});