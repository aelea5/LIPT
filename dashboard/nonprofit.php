<?php
require_once dirname(__DIR__) . '/includes/site.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/contact_directory.php';

auth_require_login();

$user = auth_user();
if (($user['role'] ?? '') === 'admin') {
    header('Location: ' . site_url('dashboard/index.php'));
    exit;
}

if (isset($_GET['logout'])) {
    auth_logout();
    header('Location: ' . site_url('index.php'));
    exit;
}

$role = 'nonprofit';
$directory_message = contact_directory_handle_post($role, $user);
$show_verify_modal = auth_needs_verify_prompt((int) $user['id']);
$open_profile_edit = isset($_GET['edit']) && $_GET['edit'] === '1';
$own_nonprofit = edit_contact_nonprofit_for_user((int) $user['id']);
$own_edit_dialog_id = 'contact-edit-' . (int) ($own_nonprofit['id'] ?? 0);

$page_title = 'Nonprofit Dashboard';
$body_class = 'page-dashboard page-dashboard-nonprofit';
require_once dirname(__DIR__) . '/includes/header.php';

$admin_sms_href = 'sms:' . ADMIN_SMS_PHONE;
$admin_email_href = 'mailto:' . ADMIN_EMAIL;
$verify_prompt_url = site_url('dashboard/verify_prompt.php');
?>

<section class="page-hero">
    <div class="container">
        <h1>Nonprofit Dashboard</h1>
        <p class="page-intro">
            Signed in as <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>.
            Tools for your assigned lunch date: menu, planning, and admin contact.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <nav class="dashboard-nav" aria-label="Dashboard">
            <ul class="dashboard-nav__list">
                <li><a href="<?= htmlspecialchars(site_url('dashboard/nonprofit.php'), ENT_QUOTES, 'UTF-8') ?>" aria-current="page">Dashboard</a></li>
                <li><a href="#contact-directory">Contact Directory</a></li>
                <li><a href="#edit-my-info">Profile &amp; contact</a></li>
            </ul>
            <div class="dashboard-nav__user">
                <span class="dashboard-nav__signed-in">
                    Signed in as <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <a class="btn btn--secondary btn--small" href="<?= htmlspecialchars(site_url('dashboard/nonprofit.php?logout=1'), ENT_QUOTES, 'UTF-8') ?>">Sign out</a>
            </div>
        </nav>

        <div class="card-grid dashboard-grid">
            <article class="card dashboard-card">
                <h2>Profile &amp; Contact Settings</h2>
                <p class="text-muted">
                    Update your organization name, phone, preferred contact method, and who can see your info.
                </p>
                <p>
                    <a class="btn btn--secondary" href="#edit-my-info" data-open-dialog="<?= htmlspecialchars($own_edit_dialog_id, ENT_QUOTES, 'UTF-8') ?>">Edit profile &amp; contact</a>
                </p>
            </article>

            <article class="card dashboard-card">
                <h2>Your Assigned Date and Menu</h2>
                <p class="text-muted">Your Thursday assignment and planned menu will appear here.</p>
                <dl class="detail-list">
                    <div class="detail-list__row">
                        <dt>Date</dt>
                        <dd class="text-muted">Not assigned</dd>
                    </div>
                    <div class="detail-list__row">
                        <dt>Menu</dt>
                        <dd class="text-muted">N/A</dd>
                    </div>
                </dl>
            </article>

            <article class="card dashboard-card">
                <h2>P&amp;L Planning Tool</h2>
                <p class="text-muted">Estimate costs and revenue for your $8 lunch service.</p>
                <div class="placeholder-panel" aria-hidden="true">
                    <p>Food cost</p>
                    <p>Servings</p>
                    <p>Projected balance</p>
                </div>
                <p class="dashboard-card__hint text-muted">Calculator coming soon.</p>
            </article>

            <article class="card dashboard-card">
                <h2>What&rsquo;s Provided / What&rsquo;s Expected</h2>
                <p class="text-muted">Park pavilion setup, supplies, and what your team brings.</p>
                <ul class="checklist-placeholder">
                    <li><span class="text-muted">Provided list, coming soon</span></li>
                    <li><span class="text-muted">Expected list, coming soon</span></li>
                </ul>
            </article>

            <article class="card dashboard-card">
                <h2>Contact Admin</h2>
                <p class="text-muted">Reach the coordinator: text on your phone, email on desktop.</p>
                <div class="contact-admin">
                    <a href="<?= htmlspecialchars($admin_sms_href, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary contact-admin__sms">
                        Text admin
                    </a>
                    <a href="<?= htmlspecialchars($admin_email_href, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary contact-admin__email">
                        Email admin
                    </a>
                </div>
            </article>

            <article class="card dashboard-card" id="nonprofit-save-panel">
                <h2>Save Info to Device</h2>
                <p class="text-muted">Print this page or save as PDF for offline reference at the park.</p>
                <button type="button" class="btn btn--secondary" data-print-panel="#nonprofit-save-panel">
                    Print / save PDF
                </button>
            </article>
        </div>

        <?php contact_directory_render($role, $user, $directory_message, $open_profile_edit); ?>
    </div>
</section>

<?php if ($show_verify_modal): ?>
    <div
        class="verify-modal"
        data-verify-modal
        data-verify-prompt-url="<?= htmlspecialchars($verify_prompt_url, ENT_QUOTES, 'UTF-8') ?>"
        role="dialog"
        aria-modal="true"
        aria-labelledby="verify-modal-heading"
    >
        <div class="verify-modal__backdrop" data-verify-modal-dismiss></div>
        <div class="verify-modal__panel card">
            <h2 id="verify-modal-heading">Quick check: has any of your contact info changed?</h2>
            <p class="text-muted">A quick review helps keep our directory accurate for everyone.</p>
            <p class="verify-modal__error text-accent" data-verify-modal-error hidden role="alert"></p>
            <div class="verify-modal__actions">
                <button type="button" class="btn btn--primary" data-verify-prompt-edit>
                    Yes, update now
                </button>
                <button type="button" class="btn btn--secondary" data-verify-prompt-confirm>
                    No, everything looks good
                </button>
                <button type="button" class="btn btn--secondary" data-verify-modal-dismiss>
                    Skip for now
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
