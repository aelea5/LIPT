<?php
require_once dirname(__DIR__) . '/includes/site.php';
require_once dirname(__DIR__) . '/includes/auth.php';

auth_require_admin();

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: ' . site_url('login.php'));
    exit;
}

$page_title = 'Dashboard';
$body_class = 'page-dashboard';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Volunteer Dashboard</h1>
        <p class="page-intro">Protected area for coordinators — content management coming soon.</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="card-grid">
            <article class="card">
                <h2>Schedule</h2>
                <p class="text-muted">Add and edit Thursday dates (June&ndash;August).</p>
            </article>
            <article class="card">
                <h2>Nonprofit spotlight</h2>
                <p class="text-muted">Update the featured organization.</p>
            </article>
            <article class="card">
                <h2>Suggestions inbox</h2>
                <p class="text-muted">Review public submissions.</p>
            </article>
        </div>

        <p style="margin-top: var(--space-lg);">
            <a href="<?= htmlspecialchars(site_url('dashboard/?logout=1'), ENT_QUOTES, 'UTF-8') ?>">Sign out</a>
        </p>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
