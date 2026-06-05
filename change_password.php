<?php
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: ' . site_url('index.php'));
    exit;
}

auth_require_login();

$user = auth_user();
$forced = auth_must_change_password();
$error = '';
$success = false;
$redirect_url = auth_dashboard_url_for_role($user['role'] ?? 'nonprofit');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($current === '' || $new === '' || $confirm === '') {
        $error = 'Please fill in all password fields.';
    } elseif (strlen($new) < 8) {
        $error = 'Your new password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        $stmt = db()->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $user['id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, (string) $row['password'])) {
            $error = 'Your current password is not correct. Please try again.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            if ($hash === false) {
                $error = 'Could not update your password. Please try again.';
            } else {
                $update = db()->prepare(
                    'UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?'
                );
                $update->execute([$hash, (int) $user['id']]);
                $success = true;
            }
        }
    }
}

$page_title = $forced ? 'Set your password' : 'Change your password';
$body_class = 'page-login page-change-password';
require_once __DIR__ . '/includes/header.php';
?>

<section class="login-section" aria-labelledby="change-password-heading">
    <div class="login-panel card">
        <?php if ($success): ?>
            <h1 id="change-password-heading" class="login-panel__title">Password updated</h1>
            <p class="login-panel__success" role="status">
                Your password has been saved. Taking you to your dashboard now.
            </p>
            <meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') ?>">
            <p class="login-panel__back">
                <a href="<?= htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') ?>">Go to dashboard now</a>
            </p>
        <?php else: ?>
            <?php if ($forced): ?>
                <h1 id="change-password-heading" class="login-panel__title">Welcome!</h1>
                <p class="login-panel__intro">
                    Before you get started, please set a new password for your account. Your current password
                    was assigned by the program coordinator.
                </p>
            <?php else: ?>
                <h1 id="change-password-heading" class="login-panel__title">Change your password</h1>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <p class="login-panel__error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form class="login-form" method="post" action="<?= htmlspecialchars(site_url('change_password.php'), ENT_QUOTES, 'UTF-8') ?>" data-enhanced>
                <div class="form-group">
                    <label for="current_password">Current password</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="form-group">
                    <label for="new_password">New password</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm new password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="btn btn--primary login-form__submit">Save new password</button>
            </form>

            <p class="login-panel__back">
                <a href="<?= htmlspecialchars(site_url('change_password.php?logout=1'), ENT_QUOTES, 'UTF-8') ?>">Sign out instead</a>
            </p>

            <?php if (!$forced): ?>
                <p class="login-panel__back">
                    <a href="<?= htmlspecialchars($redirect_url, ENT_QUOTES, 'UTF-8') ?>">&larr; Back to dashboard</a>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
