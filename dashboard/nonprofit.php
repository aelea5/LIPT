<?php
require_once dirname(__DIR__) . '/includes/site.php';
require_once dirname(__DIR__) . '/includes/auth.php';

auth_require_login();

$user = auth_user();
if (($user['role'] ?? '') === 'admin') {
    header('Location: ' . site_url('dashboard/index.php'));
    exit;
}

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: ' . site_url('login.php'));
    exit;
}

$page_title = 'Nonprofit Dashboard';
$body_class = 'page-dashboard';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Your nonprofit dashboard</h1>
        <p class="page-intro">
            Signed in as <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>.
            Organization tools coming soon.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="card-grid">
            <article class="card">
                <h2>Your profile</h2>
                <p class="text-muted">Update organization name, description, and links.</p>
            </article>
            <article class="card">
                <h2>Upcoming lunches</h2>
                <p class="text-muted">Set your date, menu, and expected guests.</p>
            </article>
            <article class="card">
                <h2>Ingredients</h2>
                <p class="text-muted">Track items and costs for your scheduled day.</p>
            </article>
        </div>

        <p style="margin-top: var(--space-lg);">
            <a href="<?= htmlspecialchars(site_url('dashboard/nonprofit.php?logout=1'), ENT_QUOTES, 'UTF-8') ?>">Sign out</a>
        </p>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
