<?php
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/db.php';

$error = null;
$success = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $message = trim((string) ($_POST['message'] ?? ''));
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($message === '') {
        $error = 'Please share your suggestion before sending.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address, or leave it blank.';
    } else {
        try {
            $stmt = db()->prepare(
                'INSERT INTO suggestions (name, email, message) VALUES (?, ?, ?)'
            );
            $stmt->execute([$name, $email, $message]);
            $success = true;
        } catch (PDOException $e) {
            error_log('suggestions.php: ' . $e->getMessage());
            $error = 'We couldn\'t save your suggestion just now. Please try again in a moment.';
        }
    }
}

$page_title = 'Suggestions';
$body_class = 'page-suggestions';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Suggestions</h1>
        <p class="page-intro">
            Tell us what would make Lunch in the Park better for you and your neighbors.
            You don&rsquo;t need an account, just your ideas.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if ($success): ?>
            <div class="card participate-card__flash participate-card__flash--success" role="status">
                <h2>Thanks for sharing. We read every note.</h2>
                <p class="text-muted">Your suggestion is in our inbox. We appreciate you taking the time.</p>
                <p><a href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8') ?>">&larr; Back to home</a></p>
            </div>
        <?php else: ?>
            <?php if ($error !== null): ?>
                <p class="participate-card__flash participate-card__flash--error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form class="card" method="post" action="<?= htmlspecialchars(site_url('suggestions.php'), ENT_QUOTES, 'UTF-8') ?>" data-enhanced>
                <div class="form-group">
                    <label for="suggestion-name">Your name <span class="text-muted">(optional)</span></label>
                    <input type="text" id="suggestion-name" name="name" autocomplete="name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="suggestion-email">Email <span class="text-muted">(optional, if you want a reply)</span></label>
                    <input type="email" id="suggestion-email" name="email" autocomplete="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="suggestion-message">Your suggestion <span class="text-accent">*</span></label>
                    <textarea id="suggestion-message" name="message" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <p class="form-hint">Menu ideas, accessibility, volunteer offers, or anything else on your mind.</p>
                </div>

                <button type="submit" class="btn btn--primary">Send suggestion</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
