    </div> <!-- End container -->

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2026 <?php echo APP_NAME; ?> - Version <?php echo APP_VERSION; ?></p>
        </div>
    </footer>

    <!-- jQuery with fallback -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>
    <script>
        // Fallback to alternative CDN if jQuery fails to load
        if (typeof jQuery === 'undefined') {
            document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"><\/script>');
        }
    </script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    
    <!-- Debug BASE_URL -->
    <script>
        console.log('BASE_URL:', '<?php echo BASE_URL; ?>');
        console.log('jQuery loaded:', typeof jQuery !== 'undefined');
    </script>
</body>
</html>
