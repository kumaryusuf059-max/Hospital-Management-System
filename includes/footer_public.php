    </main>
    <footer class="site-footer glass">
        <div class="container footer-inner">
            <p>&copy; <?= date('Y') ?> Dhami Hospital. Futuristic care interface.</p>
            <p class="footer-links">
                <a href="<?= h($GLOBALS['__asset_prefix'] ?? '') ?>register.php">Patient Register</a>
                <span class="dot">Â·</span>
                <a href="<?= h($GLOBALS['__asset_prefix'] ?? '') ?>login.php?role=patient">Login</a>
            </p>
        </div>
    </footer>
    <script src="<?= h($GLOBALS['__asset_prefix'] ?? '') ?>assets/js/main.js"></script>
</body>
</html>
