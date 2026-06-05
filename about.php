<?php
require_once __DIR__ . '/includes/site.php';

$page_title = 'About';
$body_class = 'page-about';
$hero_image = image_url(LITP_IMAGE_JESUP_MODEL);
require_once __DIR__ . '/includes/header.php';
?>

<section
    class="page-hero page-hero--image page-hero--about"
    style="--hero-image: url('<?= htmlspecialchars($hero_image, ENT_QUOTES, 'UTF-8') ?>')"
    aria-labelledby="about-hero-heading"
>
    <span class="visually-hidden">Background photo: stylized view of downtown Jesup with the water tower.</span>
    <div class="container page-hero__inner">
        <h1 id="about-hero-heading">About Lunch in the Park</h1>
        <p class="page-intro">A Jesup summer tradition, 30 years and counting.</p>
    </div>
</section>

<section class="page-content page-content--about">
    <div class="container about-layout">
        <article class="about-section">
            <h2>What it is</h2>
            <p>
                Lunch in the Park is a simple idea that has been working in Jesup for over 30 years. Every selected
                Thursday from June through August, a local nonprofit sets up at the Land O&rsquo; Corn Park Pavilion
                and serves lunch for $8 a plate. They keep every dollar. You get a good meal and a reason to get outside.
            </p>
        </article>

        <article class="about-section">
            <h2>How it works</h2>
            <p>
                No tickets. No reservation. Just show up between 11am and 1pm, bring $8, and eat. A different
                organization hosts each week, local groups doing real work in this community. The money they raise
                goes directly to their cause.
            </p>
            <ol class="about-steps">
                <li class="about-steps__item card">
                    <span class="about-steps__label">Step 1</span>
                    <p class="about-steps__title">Show up</p>
                </li>
                <li class="about-steps__item card">
                    <span class="about-steps__label">Step 2</span>
                    <p class="about-steps__title">Pay $8</p>
                </li>
                <li class="about-steps__item card">
                    <span class="about-steps__label">Step 3</span>
                    <p class="about-steps__title">Good cause gets funded</p>
                </li>
            </ol>
        </article>

        <article class="about-section">
            <h2>The History</h2>
            <p>Lunch in the Park has been a Jesup summer tradition for over 30 years.</p>
            <div class="about-history-placeholder" role="status">
                <p>Full history coming soon</p>
            </div>
        </article>

        <article class="about-section">
            <h2>A New Chapter</h2>
            <p>
                In 2026 the program passed to new hands. We are building on what Lori Schutte and those before her
                created, with a goal of connecting more neighbors, supporting more nonprofits, and making sure this
                tradition is around for another 30 years.
            </p>
        </article>

        <article class="about-section about-section--sister card">
            <div class="about-sister__body">
                <h2>Red Letter Project</h2>
                <p>
                    Lunch in the Park is part of a broader effort to strengthen Jesup&rsquo;s community. Check out the
                    <a href="https://www.redletterproject.com" target="_blank" rel="noopener noreferrer">Red Letter Project</a>,
                    neighbors helping neighbors, year round.
                </p>
            </div>
            <figure class="about-sister__figure">
                <img
                    src="<?= htmlspecialchars(image_url(LITP_IMAGE_HOUSE), ENT_QUOTES, 'UTF-8') ?>"
                    alt="A Jesup neighborhood home with a neighbor waving from the porch"
                    width="400"
                    height="260"
                    loading="lazy"
                    decoding="async"
                >
            </figure>
        </article>

        <article class="about-cta card">
            <h2>See you Thursday</h2>
            <p>Check the schedule and come hungry.</p>
            <p>
                <a class="btn btn--primary" href="<?= htmlspecialchars(site_url('roster.php'), ENT_QUOTES, 'UTF-8') ?>">See the schedule</a>
            </p>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
