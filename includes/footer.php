    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 MediTrack. All rights reserved.</p>
            <p>For support, contact: support@hospital.com</p>
        </div>
    </footer>
    
    <script src="<?php echo getBaseUrl(); ?>assets/js/main.js"></script>
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js_file): ?>
            <script src="<?php echo $js_file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
