    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p class="site-footer__name"><?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="site-footer__tagline"><?= htmlspecialchars(SITE_TAGLINE, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="site-footer__note">At the Land &rsquo;O Corn Park Pavilion on Young and Main.</p>
            <p class="site-footer__copy">&copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?= htmlspecialchars(asset_url('js/main.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
