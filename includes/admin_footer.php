
        <footer class="mt-5 pt-3 border-top text-center text-muted small">
            &copy; <?php echo date('Y'); ?> <strong>NSBM NewsDesk</strong> &mdash; Campus News Management System
        </footer>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?php echo $root_base ?? '../../'; ?>assets/js/main.js?v=<?php echo @filemtime(__DIR__ . '/../../assets/js/main.js') ?: time(); ?>"></script>
</body>
</html>

