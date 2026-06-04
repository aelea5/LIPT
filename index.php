<?php
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/participation.php';

$participation = participation_handle_post();

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
            Sticking to our 30 year tradition, every Thursday this summer a different Jesup nonprofit sets up
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
                <p class="text-muted">Who&rsquo;s serving, what&rsquo;s on the menu, and which Thursdays we&rsquo;re open this summer.</p>
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
            nonprofits while you&rsquo;re at it. That idea is still going. Every week a different organization
            takes the pavilion of the summer, feeds the crowd, and walks away with every dollar raised.
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
