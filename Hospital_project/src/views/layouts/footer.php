<?php
/**
 * Footer Layout
 * Hospital Management System
 */
?>
            </main>
        </div>
    </div>
    
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
                    </span>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">
                        <i class="fas fa-clock"></i>
                        <?php echo date('Y-m-d H:i:s'); ?>
                    </span>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            }
        });
        
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
        
        // Auto-refresh data every 30 seconds for dashboard pages
        if (window.location.pathname.includes('dashboard') || window.location.pathname.includes('current')) {
            setInterval(function() {
                // Only refresh if no modal is open
                if (!document.querySelector('.modal.show')) {
                    location.reload();
                }
            }, 30000);
        }
    </script>
</body>
</html>