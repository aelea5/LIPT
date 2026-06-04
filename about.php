<?php
require_once __DIR__ . '/includes/site.php';

$page_title = 'About';
$body_class = 'page-about';
$hero_image = image_url(LITP_IMAGE_JESUP_MODEL);
require_once __DIR__ . '/includes/header.php';
?>

<section
    class="page-hero page-hero--image"
    style="--hero-image: url('<?= htmlspecialchars($hero_image, ENT_QUOTES, 'UTF-8') ?>')"
    aria-label="About Lunch in the Park"
>
    <span class="visually-hidden">Background photo: stylized view of downtown Jesup with the water tower.</span>
    <div class="container page-hero__inner">
        <h1>About Lunch in the Park</h1>
        <p class="page-intro">
            A Thursday tradition at the Land &rsquo;O Corn Park Pavilion, neighbors, nonprofits, and good food.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container about-layout">
        <article class="card about-feature">
            <figure class="about-feature__figure">
                <img
                    src="<?= htmlspecialchars(image_url(LITP_IMAGE_JESUP_MODEL), ENT_QUOTES, 'UTF-8') ?>"
                    alt="Stylized miniature view of downtown Jesup with the water tower and main street"
                    width="800"
                    height="500"
                    loading="lazy"
                    decoding="async"
                >
                <figcaption class="form-hint">Jesup, our hometown and the heart of Lunch in the Park.</figcaption>
            </figure>
            <div class="about-feature__body">
                <h2>Rooted in Jesup</h2>
                <p>
                    Lunch in the Park brings people together at the pavilion on Young and Main.
                    Local nonprofits host scheduled Thursdays from June through August, serving an $8 lunch
                    that keeps the focus on community, not fuss.
                </p>
                <p>
                    Whether you live a block away or you&rsquo;re visiting family, you&rsquo;re welcome at the table.
                </p>
            </div>
        </article>

        <article
            class="page-band page-band--image"
            style="--band-image: url('<?= htmlspecialchars(image_url(LITP_IMAGE_HOUSE), ENT_QUOTES, 'UTF-8') ?>')"
        >
            <span class="visually-hidden">Background photo: a Jesup neighborhood home with a neighbor waving from the porch.</span>
            <div class="container page-band__inner">
                <h2>Good neighbors, good company</h2>
                <p>
                    Jesup is a town where people still wave from the porch. Lunch in the Park fits that spirit:
                    informal, friendly, and open to everyone.
                </p>
            </div>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
