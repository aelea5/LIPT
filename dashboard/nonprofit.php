<?php
require_once dirname(__DIR__) . '/includes/site.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/db.php';
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

$upcoming_assignment = null;

try {
    $np_stmt = db()->prepare(
        'SELECT id FROM nonprofits WHERE user_id = ? LIMIT 1'
    );
    $np_stmt->execute([(int) $user['id']]);
    $nonprofit_record = $np_stmt->fetch(PDO::FETCH_ASSOC);

    if ($nonprofit_record) {
        $sched_stmt = db()->prepare(
            'SELECT event_date, menu_description, expected_guests
             FROM schedule
             WHERE nonprofit_id = ?
               AND event_date >= CURDATE()
             ORDER BY event_date ASC
             LIMIT 1'
        );
        $sched_stmt->execute([(int) $nonprofit_record['id']]);
        $row = $sched_stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $upcoming_assignment = $row;
        }
    }
} catch (PDOException $e) {
    error_log('nonprofit dashboard assignment: ' . $e->getMessage());
}

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
                <?php if ($upcoming_assignment !== null): ?>
                    <?php
                    $event_ts = strtotime((string) $upcoming_assignment['event_date']);
                    $formatted_date = date('l, F j, Y', $event_ts);
                    $menu_text = trim((string) ($upcoming_assignment['menu_description'] ?? ''));
                    if ($menu_text === '') {
                        $menu_text = 'Menu not set yet';
                    }
                    $expected_guests = $upcoming_assignment['expected_guests'] ?? null;
                    ?>
                    <dl class="detail-list">
                        <div class="detail-list__row">
                            <dt>Date</dt>
                            <dd><?= htmlspecialchars($formatted_date, ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="detail-list__row">
                            <dt>Time</dt>
                            <dd>11am to 1pm</dd>
                        </div>
                        <div class="detail-list__row">
                            <dt>Location</dt>
                            <dd>Land O&rsquo; Corn Park Pavilion, Young and Main</dd>
                        </div>
                        <div class="detail-list__row">
                            <dt>Menu</dt>
                            <dd><?= htmlspecialchars($menu_text, ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <?php if ($expected_guests !== null && $expected_guests !== '' && (int) $expected_guests > 0): ?>
                            <div class="detail-list__row">
                                <dt>Expected guests</dt>
                                <dd><?= htmlspecialchars((string) (int) $expected_guests, ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <p class="dashboard-card__hint text-muted">You keep every dollar raised. Plan for 90 to 150 guests.</p>
                <?php else: ?>
                    <p>You do not have a date assigned yet. Check back soon or contact the coordinator.</p>
                    <p>
                        <a
                            class="btn btn--primary"
                            href="<?= htmlspecialchars($admin_email_href, ENT_QUOTES, 'UTF-8') ?>"
                            data-contact-admin-link
                            data-href-mobile="<?= htmlspecialchars($admin_sms_href, ENT_QUOTES, 'UTF-8') ?>"
                            data-href-desktop="<?= htmlspecialchars($admin_email_href, ENT_QUOTES, 'UTF-8') ?>"
                        >Contact coordinator</a>
                    </p>
                <?php endif; ?>
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

            <article class="card dashboard-card dashboard-card--wide">
                <h2>What&rsquo;s Provided / What&rsquo;s Expected</h2>
                <p class="text-muted">Park pavilion setup, supplies, and what your team brings.</p>

                <div class="host-supplies-grid">
                    <div class="host-supplies-column">
                        <h3 class="host-supplies-heading">What we provide</h3>
                        <ul class="host-supplies-list host-supplies-list--check">
                            <li>Tables</li>
                            <li>Large coffee percolator</li>
                            <li>Up to three large beverage dispensers</li>
                            <li>Power access at the pavilion</li>
                            <li>Cups, plates, and to-go carriers</li>
                            <li>Flatware packages including salt, pepper, and napkins</li>
                            <li>Lemonade and coffee</li>
                            <li>Dessert</li>
                        </ul>
                    </div>

                    <div class="host-supplies-column">
                        <h3 class="host-supplies-heading">What your team brings</h3>
                        <ul class="host-supplies-list host-supplies-list--bullet">
                            <li>All food for your chosen menu</li>
                            <li>Any additional serving equipment your menu requires</li>
                            <li>Your volunteer crew</li>
                            <li>If selling canned soda: your own ice and cooler</li>
                        </ul>
                    </div>
                </div>

                <div class="host-supplies-expect">
                    <h3 class="host-supplies-heading">What to expect</h3>
                    <ul class="host-supplies-list host-supplies-list--bullet">
                        <li>Plan for 90 to 150 guests</li>
                        <li>You serve from 11am to 1pm</li>
                        <li>Every attendee pays $8 flat. That covers food, dessert, and a drink</li>
                        <li>You keep every dollar raised</li>
                        <li>The pavilion is covered. Rain does not cancel the event</li>
                    </ul>
                </div>
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
