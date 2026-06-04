<?php
/**
 * Public "Want to Participate?" interest form.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/newsletter.php';

/**
 * @return array{error: ?string, success: ?string}
 */
function participation_handle_post(): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['participation_request'])) {
        return ['error' => null, 'success' => null];
    }

    $contact_name = trim((string) ($_POST['contact_name'] ?? ''));
    $org_name = trim((string) ($_POST['org_name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $requested_username = trim((string) ($_POST['requested_username'] ?? ''));

    if ($contact_name === '' || $org_name === '' || $email === '') {
        return ['error' => 'Contact name, organization name, and email are required.', 'success' => null];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['error' => 'Please enter a valid email address.', 'success' => null];
    }

    if ($requested_username !== '' && !preg_match('/^[a-zA-Z0-9_]{3,64}$/', $requested_username)) {
        return [
            'error' => 'Username must be 3–64 characters and use only letters, numbers, and underscores.',
            'success' => null,
        ];
    }

    try {
        $stmt = db()->prepare(
            'INSERT INTO nonprofit_requests (contact_name, org_name, phone, email, requested_username)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $contact_name,
            $org_name,
            $phone !== '' ? $phone : null,
            $email,
            $requested_username !== '' ? $requested_username : null,
        ]);
    } catch (PDOException $e) {
        error_log('participation request: ' . $e->getMessage());

        return ['error' => 'We could not save your request. Please try again later.', 'success' => null];
    }

    $newsletter_note = '';
    if (!empty($_POST['newsletter_opt_in'])) {
        $sub_error = newsletter_subscribe($contact_name, $email);
        if ($sub_error === null) {
            $newsletter_note = ' You\'re also on the newsletter list for when we launch.';
        }
    }

    return [
        'error' => null,
        'success' => 'Thank you! Your participation request was sent. We\'ll be in touch soon.' . $newsletter_note,
    ];
}

function participation_render_form(?string $error = null, ?string $success = null): void
{
    ?>
    <section id="want-to-participate" class="participate-section">
        <div class="card participate-card">
            <h2>Want to Participate?</h2>
            <p class="text-muted participate-card__intro">
                Got a group that could use a fundraiser and doesn&rsquo;t mind feeding a crowd? We&rsquo;d love
                to have you. Fill this out and we&rsquo;ll be in touch.
            </p>

            <?php if ($success): ?>
                <p class="participate-card__flash participate-card__flash--success" role="status">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p class="participate-card__flash participate-card__flash--error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form id="participate-form" method="post" action="#want-to-participate" class="participate-form" data-enhanced>
                <input type="hidden" name="participation_request" value="1">

                <div class="form-group">
                    <label for="contact_name">Contact name <span class="text-accent">*</span></label>
                    <input type="text" id="contact_name" name="contact_name" required
                        value="<?= htmlspecialchars($_POST['contact_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="org_name">Organization name <span class="text-accent">*</span></label>
                    <input type="text" id="org_name" name="org_name" required
                        value="<?= htmlspecialchars($_POST['org_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="participate_phone">Contact phone</label>
                    <input type="tel" id="participate_phone" name="phone"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="participate_email">Contact email <span class="text-accent">*</span></label>
                    <input type="email" id="participate_email" name="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="requested_username">Requested username</label>
                    <input type="text" id="requested_username" name="requested_username"
                        pattern="[a-zA-Z0-9_]{3,64}"
                        placeholder="We will create one if left blank"
                        value="<?= htmlspecialchars($_POST['requested_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="newsletter-opt-in">
                    <p class="newsletter-opt-in__heading">
                        <span class="badge badge--coming-soon">Coming soon</span>
                        Newsletter
                    </p>
                    <label class="newsletter-opt-in__label">
                        <input type="checkbox" name="newsletter_opt_in" value="1"
                            <?= isset($_POST['newsletter_opt_in']) ? 'checked' : '' ?>>
                        Notify me when the Lunch in the Park newsletter launches
                    </label>
                    <p class="newsletter-opt-in__note form-hint">
                        This feature is coming soon. By checking this box you are opting in to receive an occasional email newsletter when it becomes available. You can opt out at any time. Uses the name and email from your request above.
                    </p>
                </div>

                <button type="submit" class="btn btn--primary">Send request</button>
            </form>
        </div>
    </section>
    <?php
}
