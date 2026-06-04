    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <p class="site-footer__name"><?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="site-footer__tagline"><?= htmlspecialchars(SITE_TAGLINE, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="site-footer__note">At the Land &rsquo;O Corn Park Pavilion on Young and Main.</p>

            <?php
            require_once __DIR__ . '/newsletter.php';
            $footer_body_class = $body_class ?? '';
            if (newsletter_is_public_page($footer_body_class)) {
                $footer_newsletter_message = newsletter_handle_post() ?? '';
                newsletter_render_footer($footer_newsletter_message);
            }
            ?>

            <nav class="site-footer__helpful-links" aria-labelledby="footer-helpful-links-heading">
                <h2 id="footer-helpful-links-heading" class="site-footer__helpful-links-heading">Helpful Links</h2>
                <ul class="site-footer__helpful-links-list">
                    <li>
                        <a href="https://www.jesupiowa.com" target="_blank" rel="noopener noreferrer">City of Jesup</a>
                    </li>
                    <li>
                        <a href="https://www.jesup-iowa.com" target="_blank" rel="noopener noreferrer">Jesup Iowa Community</a>
                    </li>
                    <li>
                        <a href="https://www.redletterproject.com" target="_blank" rel="noopener noreferrer">Red Letter Project</a>
                    </li>
                    <li>
                        <a href="https://jesup.lib.ia.us" target="_blank" rel="noopener noreferrer">Jesup Public Library</a>
                    </li>
                </ul>
            </nav>

            <p class="site-footer__copy">&copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</p>
            <img
                src="<?= htmlspecialchars(image_url(LITP_IMAGE_LOGO), ENT_QUOTES, 'UTF-8') ?>"
                alt="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> official logo"
                class="site-footer__logo"
                height="60"
                width="auto"
                decoding="async"
            >
        </div>
    </footer>

    <script src="<?= htmlspecialchars(asset_url('js/main.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
