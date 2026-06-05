<?php
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/participation.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/roster_display.php';

$participation = participation_handle_post();

$thursday_card_show = false;
$thursday_card = null;

try {
    $stmt = db()->query(
        "SELECT s.id, s.event_date, s.menu_description, s.status, s.notes, s.cancellation_url,
                n.org_name
         FROM schedule s
         LEFT JOIN nonprofits n ON n.id = s.nonprofit_id
         WHERE s.event_date >= CURDATE()
           AND s.event_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
           AND s.status IN ('open', 'confirmed', 'cancelled')
         ORDER BY s.event_date ASC
         LIMIT 1"
    );
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $thursday_card_show = true;
        $thursday_card = $row;
    }
} catch (PDOException $e) {
    error_log('index.php thursday card: ' . $e->getMessage());
}

$thursday_card_heading = null;
if ($thursday_card_show && $thursday_card !== null) {
    $event_ts = strtotime((string) $thursday_card['event_date']);
    $same_week = date('o-W', $event_ts) === date('o-W');
    $thursday_card_heading = $same_week
        ? 'This Thursday'
        : 'Coming Up Thursday, ' . date('F j', $event_ts);
}

$page_title = 'Home';
$body_class = 'page-home';
$hero_celebration = image_url(LITP_IMAGE_CELEBRATION);
$band_house = image_url(LITP_IMAGE_HOUSE);
$band_gazebo = image_url(LITP_IMAGE_GAZEBO);
require_once __DIR__ . '/includes/header.php';
?>

<section
    class="page-hero page-hero--home page-hero--image"
    style="--hero-image: url('<?= htmlspecialchars($hero_celebration, ENT_QUOTES, 'UTF-8') ?>')"
    aria-labelledby="home-hero-heading"
>
    <span class="visually-hidden">Background photo: people celebrating at Lunch in the Park.</span>
    <div class="container page-hero__inner">
        <h1 id="home-hero-heading">Welcome to Lunch in the Park</h1>
        <p class="page-intro">
            Sticking to our 30 year tradition, select Thursdays this summer, a different Jesup nonprofit sets up
            at the Land O&rsquo; Corn Park Pavilion and serves a lunch for $8 a plate. You show up, eat well,
            and every dollar goes straight to a local cause. No reservation. No dress code. Just good food
            and good people.
        </p>
        <p class="page-hero__actions">
            <a class="btn btn--primary" href="<?= htmlspecialchars(site_url('roster.php'), ENT_QUOTES, 'UTF-8') ?>">See who&rsquo;s serving</a>
            <a class="btn btn--secondary btn--on-dark" href="<?= htmlspecialchars(site_url('suggestions.php'), ENT_QUOTES, 'UTF-8') ?>">Share an idea</a>
        </p>
    </div>
</section>

<section class="home-cards">
    <div class="container">
        <div class="card-grid card-grid--home">
            <a class="card card--link" href="<?= htmlspecialchars(site_url('roster.php'), ENT_QUOTES, 'UTF-8') ?>">
                <h2>Schedule</h2>
                <p class="text-muted">Who&rsquo;s serving, what&rsquo;s on the menu, and which select Thursdays we&rsquo;re serving this summer.</p>
                <span class="card__cta">See the schedule</span>
            </a>
            <a class="card card--link" href="<?= htmlspecialchars(site_url('suggestions.php'), ENT_QUOTES, 'UTF-8') ?>">
                <h2>Your Voice</h2>
                <p class="text-muted">Got an idea? Accessibility need? Want to volunteer? We actually read these.</p>
                <span class="card__cta">Send a note</span>
            </a>
        </div>
    </div>
</section>

<section
    class="page-band page-band--image page-band--mission"
    style="--band-image: url('<?= htmlspecialchars($band_house, ENT_QUOTES, 'UTF-8') ?>')"
    aria-labelledby="mission-heading"
>
    <span class="visually-hidden">Background photo: a Jesup neighborhood home.</span>
    <div class="container page-band__inner">
        <h2 id="mission-heading">Our Mission</h2>
        <p>
            Thirty years ago someone had a simple idea: get neighbors together over a meal and help local
            nonprofits while you&rsquo;re at it. That idea is still going. On each serving Thursday, a different organization
            takes the pavilion for the summer, feeds the crowd, and walks away with every dollar raised.
            We just show up and eat.
        </p>
        <p>
            <a class="btn btn--secondary btn--on-dark" href="<?= htmlspecialchars(site_url('nonprofit.php'), ENT_QUOTES, 'UTF-8') ?>">Meet the nonprofits</a>
        </p>
    </div>
</section>

<section
    class="page-band page-band--image page-band--pavilion"
    style="--band-image: url('<?= htmlspecialchars($band_gazebo, ENT_QUOTES, 'UTF-8') ?>')"
    aria-labelledby="pavilion-heading"
>
    <span class="visually-hidden">Background photo: the Land O&rsquo; Corn Park gazebo with an American flag.</span>
    <div class="container page-band__inner">
        <h2 id="pavilion-heading" class="visually-hidden">The pavilion</h2>
        <p>
            Land O&rsquo; Corn Park. Picnic tables, fresh air, and whoever happens to sit down next to you.
            That&rsquo;s the whole thing, and it&rsquo;s been working for thirty years.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php participation_render_form($participation['error'], $participation['success']); ?>
    </div>
</section>

<?php if ($thursday_card_show && $thursday_card !== null && $thursday_card_heading !== null): ?>
    <?php
    $card_status = (string) ($thursday_card['status'] ?? '');
    $roster_url = site_url('roster.php');
    $participate_url = site_url('index.php#want-to-participate');
    ?>
    <aside class="thursday-card" id="thursday-card" data-thursday-card hidden aria-labelledby="thursday-card-heading">
        <button type="button" class="thursday-card__close" data-thursday-card-close aria-label="Close this Thursday notice">&times;</button>
        <h2 class="thursday-card__heading" id="thursday-card-heading"><?= htmlspecialchars($thursday_card_heading, ENT_QUOTES, 'UTF-8') ?></h2>

        <div class="thursday-card__body">
            <?php if ($card_status === 'confirmed'): ?>
                <p class="thursday-card__org">
                    <strong><?= htmlspecialchars((string) ($thursday_card['org_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?: 'Host TBA' ?></strong>
                </p>
                <?php $menu = trim((string) ($thursday_card['menu_description'] ?? '')); ?>
                <?php if ($menu !== ''): ?>
                    <p class="thursday-card__menu"><?= htmlspecialchars($menu, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <p class="thursday-card__details">11am to 1pm, Land O&rsquo; Corn Park Pavilion, Young and Main</p>
                <p class="thursday-card__link">
                    <a href="<?= htmlspecialchars($roster_url, ENT_QUOTES, 'UTF-8') ?>">See the full schedule</a>
                </p>

            <?php elseif ($card_status === 'cancelled'): ?>
                <div class="thursday-card__message">
                    <?php roster_render_cancelled_note($thursday_card); ?>
                </div>
                <p class="thursday-card__link">
                    <a href="<?= htmlspecialchars($roster_url, ENT_QUOTES, 'UTF-8') ?>">See the full schedule</a>
                </p>

            <?php elseif ($card_status === 'open'): ?>
                <p class="thursday-card__message">
                    We are still looking for a host this Thursday. Could your group feed the crowd?
                </p>
                <p class="thursday-card__link">
                    <a href="<?= htmlspecialchars($participate_url, ENT_QUOTES, 'UTF-8') ?>">Learn how to participate</a>
                </p>
            <?php endif; ?>
        </div>
    </aside>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
