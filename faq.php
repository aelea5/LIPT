<?php
require_once __DIR__ . '/includes/site.php';

$page_title = 'FAQ';
$body_class = 'page-faq';
$participate_url = site_url('index.php#want-to-participate');
$suggestions_url = site_url('suggestions.php');

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--faq" aria-labelledby="faq-hero-heading">
    <div class="container page-hero__inner">
        <h1 id="faq-hero-heading">Frequently Asked Questions</h1>
        <p class="page-intro">Everything you need to know about participating or showing up hungry.</p>
    </div>
</section>

<section class="page-content page-content--faq">
    <div class="container faq-layout">
        <div class="faq-accordion" data-faq-accordion>
            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-1" aria-expanded="false" aria-controls="faq-a-1">
                        How much does it cost to participate?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-1" class="faq-answer" role="region" aria-labelledby="faq-q-1" hidden>
                    <p>Nothing. The only cost is your food. You keep every dollar you raise.</p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-2" aria-expanded="false" aria-controls="faq-a-2">
                        How do we sign up?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-2" class="faq-answer" role="region" aria-labelledby="faq-q-2" hidden>
                    <p>
                        Fill out the
                        <a href="<?= htmlspecialchars($participate_url, ENT_QUOTES, 'UTF-8') ?>">participation form</a>
                        on this site, or call or text James at 208-316-5068.
                    </p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-3" aria-expanded="false" aria-controls="faq-a-3">
                        What equipment and supplies are provided?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-3" class="faq-answer" role="region" aria-labelledby="faq-q-3" hidden>
                    <p>
                        Tables, a large coffee percolator, up to three large beverage dispensers, and power access.
                        We also provide cups, plates, to-go carriers, flatware packages with salt, pepper and napkins,
                        lemonade, and coffee.
                    </p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-4" aria-expanded="false" aria-controls="faq-a-4">
                        What is included in the $8 attendee price?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-4" class="faq-answer" role="region" aria-labelledby="faq-q-4" hidden>
                    <p>
                        Food, dessert, and a drink. If you want to sell canned soda separately you are welcome to,
                        just bring your own ice and cooler.
                    </p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-5" aria-expanded="false" aria-controls="faq-a-5">
                        How many people typically attend?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-5" class="faq-answer" role="region" aria-labelledby="faq-q-5" hidden>
                    <p>
                        It fluctuates with weather and competing events, but plan for 90 to 150 guests as of 2026.
                    </p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-6" aria-expanded="false" aria-controls="faq-a-6">
                        Can we choose our own menu?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-6" class="faq-answer" role="region" aria-labelledby="faq-q-6" hidden>
                    <p>
                        Absolutely, with some guidance. This is not a full scale restaurant operation, so we provide a
                        <a href="<?= htmlspecialchars($suggestions_url, ENT_QUOTES, 'UTF-8') ?>">menu ideas page</a>
                        to help you plan something that works well at the pavilion and keeps your costs down.
                    </p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-7" aria-expanded="false" aria-controls="faq-a-7">
                        Where is the pavilion?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-7" class="faq-answer" role="region" aria-labelledby="faq-q-7" hidden>
                    <p>
                        Right in the heart of downtown Jesup. On Young Street between Main and 6th. You can not miss it.
                    </p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-8" aria-expanded="false" aria-controls="faq-a-8">
                        What if it rains?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-8" class="faq-answer" role="region" aria-labelledby="faq-q-8" hidden>
                    <p>The pavilion is covered and it is huge. Rain does not slow us down in Jesup.</p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-9" aria-expanded="false" aria-controls="faq-a-9">
                        Can a nonprofit participate more than once in a season?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-9" class="faq-answer" role="region" aria-labelledby="faq-q-9" hidden>
                    <p>
                        All available dates are open to new organizations through May 15th. After that, if slots remain
                        open, returning organizations are welcome to book a second date.
                    </p>
                </div>
            </div>

            <div class="faq-item">
                <h2 class="faq-item__heading">
                    <button type="button" class="faq-question" id="faq-q-10" aria-expanded="false" aria-controls="faq-a-10">
                        Do we need a 501(c)(3)?
                        <span class="faq-question__icon" aria-hidden="true"></span>
                    </button>
                </h2>
                <div id="faq-a-10" class="faq-answer" role="region" aria-labelledby="faq-q-10" hidden>
                    <p>
                        You must be a charitable organization with a clear, demonstrable use for the funds. You do not
                        need a formal 501(c)(3) but you will need to show how proceeds will be used before your
                        participation is approved.
                    </p>
                </div>
            </div>
        </div>

        <article class="faq-cta card">
            <h2>Still have questions?</h2>
            <p>Reach out directly, we are happy to help.</p>
            <div class="faq-cta__actions">
                <a class="btn btn--primary" href="<?= htmlspecialchars($suggestions_url, ENT_QUOTES, 'UTF-8') ?>">Send a suggestion</a>
                <a
                    class="btn btn--secondary"
                    href="tel:2083165068"
                    data-faq-contact-link
                    data-href-mobile="sms:2083165068"
                    data-href-desktop="tel:2083165068"
                >Call or text James</a>
            </div>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
