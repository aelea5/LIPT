<?php
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if (auth_is_logged_in()) {
    $user = auth_user();
    header('Location: ' . auth_dashboard_url_for_role($user['role']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $role = auth_attempt_login($username, $password);

    if ($role !== null) {
        $destination = auth_dashboard_url_for_role($role);
        if ($role === 'nonprofit') {
            $logged_in = auth_user();
            if ($logged_in && auth_needs_verify_prompt((int) $logged_in['id'])) {
                $_SESSION['litp_show_verify_prompt'] = true;
            }
        }
        header('Location: ' . $destination);
        exit;
    }

    $error = 'That username or password didn\'t match. Please try again.';
}

$page_title = 'Sign in';
$body_class = 'page-login';
require_once __DIR__ . '/includes/header.php';
?>

<section class="login-section" aria-labelledby="login-heading">
    <div class="login-panel card">
        <h1 id="login-heading" class="login-panel__title">Welcome back</h1>
        <p class="login-panel__intro">Sign in to manage your Lunch in the Park dashboard.</p>

        <?php if ($error !== ''): ?>
            <p class="login-panel__error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="login-form" method="post" action="<?= htmlspecialchars(site_url('login.php'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn--primary login-form__submit">Sign in</button>
        </form>

        <p class="login-panel__back">
            <a href="<?= htmlspecialchars(site_url(), ENT_QUOTES, 'UTF-8') ?>">&larr; Back to the site</a>
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
