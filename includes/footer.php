    </main>
</div>
</div>

<script src="<?= base_url('assets/js/app.js') ?>?v=3.0"></script>
<script>
    <?php 
    $jsPath = __DIR__ . '/../assets/js/app.js';
    if (file_exists($jsPath)) {
        echo file_get_contents($jsPath);
    }
    ?>
</script>
</body>
</html>
