<?php
require_once dirname(__DIR__) . '/includes/site.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/contact_directory.php';
require_once dirname(__DIR__) . '/includes/admin_requests.php';
require_once dirname(__DIR__) . '/includes/admin_suggestions.php';
require_once dirname(__DIR__) . '/includes/admin_schedule.php';

auth_require_admin();

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: ' . site_url('login.php'));
    exit;
}

$user = auth_user();
$role = 'admin';
$directory_message = contact_directory_handle_post($role, $user ?? null);
$requests_message = admin_requests_handle_post($user ?? ['id' => 0, 'username' => 'admin']);
$suggestions_message = admin_suggestions_handle_post();
$schedule_message = admin_schedule_handle_post();
$directory_flash = ($requests_message !== null || $suggestions_message !== null || $schedule_message !== null)
    ? null
    : $directory_message;
$dates_filled = admin_schedule_confirmed_upcoming_count();

$page_title = 'Admin Dashboard';
$body_class = 'page-dashboard page-dashboard-admin';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p class="page-intro">Overview and tools for site coordinators.</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <nav class="dashboard-nav" aria-label="Dashboard">
            <ul class="dashboard-nav__list">
                <li><a href="<?= htmlspecialchars(site_url('dashboard/index.php'), ENT_QUOTES, 'UTF-8') ?>" aria-current="page">Admin</a></li>
                <li><a href="<?= htmlspecialchars(site_url('dashboard/nonprofit.php'), ENT_QUOTES, 'UTF-8') ?>">Nonprofit</a></li>
                <li><a href="#pending-requests">Pending Requests</a></li>
                <li><a href="#suggestions-inbox">Suggestions</a></li>
                <li><a href="#schedule-manager">Schedule</a></li>
                <li><a href="#contact-directory">Contact Directory</a></li>
            </ul>
        </nav>

        <div class="card-grid dashboard-grid">
            <article class="card dashboard-card dashboard-card--wide">
                <h2>Site Stats</h2>
                <ul class="stat-list">
                    <li class="stat-list__item">
                        <span class="stat-list__label">Visitors</span>
                        <span class="stat-list__value">N/A</span>
                    </li>
                    <li class="stat-list__item">
                        <span class="stat-list__label">Signups</span>
                        <span class="stat-list__value">N/A</span>
                    </li>
                    <li class="stat-list__item">
                        <span class="stat-list__label">Dates filled</span>
                        <span class="stat-list__value"><?= (int) $dates_filled ?></span>
                    </li>
                </ul>
                <p class="text-muted dashboard-card__hint">Counts will populate when tracking is enabled.</p>
            </article>

            <article class="card dashboard-card">
                <h2>Message Center</h2>
                <p class="text-muted">Inbox for host and nonprofit messages.</p>
                <ul class="inbox-preview" aria-label="Sample messages">
                    <li class="inbox-preview__item inbox-preview__item--unread">
                        <span class="inbox-preview__subject">No messages yet</span>
                        <span class="badge badge--unread">Unread</span>
                    </li>
                </ul>
                <div class="dashboard-actions">
                    <button type="button" class="btn btn--secondary" disabled>Mark read</button>
                    <button type="button" class="btn btn--secondary" disabled>Archive</button>
                    <button type="button" class="btn btn--secondary" disabled>Delete</button>
                </div>
            </article>

            <article class="card dashboard-card">
                <h2>Manage Nonprofits</h2>
                <p class="text-muted">Assign hosts, spotlight partners, and contact details.</p>
                <p><a class="btn btn--secondary" href="#contact-directory">Contact directory</a></p>
            </article>

            <?php admin_requests_render_card($requests_message); ?>

            <?php admin_suggestions_render_inbox($suggestions_message); ?>
        </div>

        <?php admin_schedule_render_manager($schedule_message); ?>

        <?php contact_directory_render($role, $user ?? ['id' => 0, 'username' => '', 'role' => 'admin'], $directory_flash); ?>

        <p class="dashboard-footer-links">
            <a href="<?= htmlspecialchars(site_url('dashboard/index.php?logout=1'), ENT_QUOTES, 'UTF-8') ?>">Sign out</a>
        </p>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
