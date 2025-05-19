document.addEventListener('DOMContentLoaded', function() {
    // Gestion du modal pour modifier un post
    const editButtons = document.querySelectorAll('.btn-edit');
    const modal = document.getElementById('edit-modal');
    const closeBtn = document.querySelector('.close');
    
    if (editButtons && modal) {
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const titre = this.getAttribute('data-titre');
                const contenu = this.getAttribute('data-contenu');
                const type = this.getAttribute('data-type');
                
                document.getElementById('edit-post-id').value = id;
                document.getElementById('edit-titre').value = titre;
                document.getElementById('edit-contenu').value = contenu;
                document.getElementById('edit-type').value = type;
                
                modal.style.display = 'block';
            });
        });
        
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Validation des filtres de date
    const filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;
            
            if ((dateDebut && !dateFin) || (!dateDebut && dateFin)) {
                alert('Veuillez spécifier les deux dates pour filtrer par période.');
                e.preventDefault();
            }
            
            if (dateDebut && dateFin && new Date(dateDebut) > new Date(dateFin)) {
                alert('La date de début doit être antérieure à la date de fin.');
                e.preventDefault();
            }
        });
    }
    
    // Confirmation de suppression
    const deleteButtons = document.querySelectorAll('button[value="delete"]');
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                    e.preventDefault();
                }
            });
        });
    }
});