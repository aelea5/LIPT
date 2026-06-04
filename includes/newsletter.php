<?php
/**
 * Newsletter opt-in (coming soon) — save subscribers.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function newsletter_subscribe(string $name, string $email): ?string
{
    $name = trim($name);
    $email = trim($email);

    if ($name === '' || $email === '') {
        return 'Name and email are required for the newsletter list.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }

    $stmt = db()->prepare(
        'SELECT id, opted_out FROM newsletter_subscribers WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ((int) $existing['opted_out'] === 0) {
            return null;
        }

        $update = db()->prepare(
            'UPDATE newsletter_subscribers SET name = ?, opted_out = 0, opted_in_at = NOW() WHERE id = ?'
        );
        $update->execute([$name, (int) $existing['id']]);

        return null;
    }

    $insert = db()->prepare(
        'INSERT INTO newsletter_subscribers (name, email) VALUES (?, ?)'
    );
    $insert->execute([$name, $email]);

    return null;
}

/**
 * Handle footer newsletter POST. Returns status message or null.
 */
function newsletter_handle_post(): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['newsletter_signup'])) {
        return null;
    }

    if (empty($_POST['newsletter_opt_in'])) {
        return 'Please check the box to opt in.';
    }

    $email = trim((string) ($_POST['newsletter_email'] ?? ''));
    $name = trim((string) ($_POST['newsletter_name'] ?? ''));
    if ($name === '' && $email !== '') {
        $local = strstr($email, '@', true);
        $name = $local !== false && $local !== '' ? $local : 'Newsletter subscriber';
    }

    $error = newsletter_subscribe($name, $email);
    if ($error !== null) {
        return $error;
    }

    return 'You\'re on the list. We\'ll notify you when the newsletter launches.';
}

function newsletter_is_public_page(string $body_class): bool
{
    return !str_contains($body_class, 'page-dashboard')
        && !str_contains($body_class, 'page-login')
        && !str_contains($body_class, 'page-create-admin');
}

/**
 * Compact footer newsletter opt-in.
 */
function newsletter_render_footer(string $message = ''): void
{
    ?>
    <div class="footer-newsletter">
        <p class="footer-newsletter__intro">
            Want to know what&rsquo;s coming up this summer? Join the list and we&rsquo;ll reach out when
            the newsletter launches.
        </p>
        <form class="footer-newsletter__form" method="post" action="">
            <input type="hidden" name="newsletter_signup" value="1">
            <label class="footer-newsletter__row">
                <input type="email" name="newsletter_email" class="footer-newsletter__email"
                    placeholder="Your email" autocomplete="email" required
                    value="<?= htmlspecialchars($_POST['newsletter_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <span class="footer-newsletter__check-wrap">
                    <input type="checkbox" name="newsletter_opt_in" value="1"
                        <?= isset($_POST['newsletter_opt_in']) ? 'checked' : '' ?>>
                    <span>Notify me when the Lunch in the Park newsletter launches</span>
                </span>
            </label>
            <p class="footer-newsletter__note">
                <span class="badge badge--coming-soon">Coming soon</span>
                By checking this box you are opting in to receive an occasional email newsletter when it becomes available. You can opt out at any time.
            </p>
            <?php if ($message !== ''): ?>
                <p class="footer-newsletter__flash" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <button type="submit" class="btn btn--secondary btn--small">Join the list</button>
        </form>
    </div>
    <?php
}
