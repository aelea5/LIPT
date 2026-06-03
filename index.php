<?php
$page_title = 'Home';
$body_class = 'page-home';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--home">
    <div class="container">
        <h1>Welcome to Lunch in the Park</h1>
        <p class="page-intro">
            Neighbors sharing an $8 lunch outdoors on scheduled Thursdays, June through August.
            Pull up a chair, bring a friend, and stay as long as you like.
        </p>
        <p>
            <a class="btn btn--primary" href="<?= htmlspecialchars(site_url('roster.php'), ENT_QUOTES, 'UTF-8') ?>">See the schedule</a>
            <a class="btn btn--secondary" href="<?= htmlspecialchars(site_url('suggestions.php'), ENT_QUOTES, 'UTF-8') ?>">Share a suggestion</a>
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="card-grid">
            <article class="card">
                <h2>Schedule</h2>
                <p class="text-muted">Which Thursdays we&rsquo;re serving, who&rsquo;s hosting, and what&rsquo;s on the menu — June through August only.</p>
                <p><a href="<?= htmlspecialchars(site_url('roster.php'), ENT_QUOTES, 'UTF-8') ?>">View the schedule &rarr;</a></p>
            </article>
            <article class="card">
                <h2>Nonprofit Spotlight</h2>
                <p class="text-muted">Local organizations doing good work — and how you can lend a hand.</p>
                <p><a href="<?= htmlspecialchars(site_url('nonprofit.php'), ENT_QUOTES, 'UTF-8') ?>">Meet this month&rsquo;s partner &rarr;</a></p>
            </article>
            <article class="card">
                <h2>Your Voice</h2>
                <p class="text-muted">Menu ideas, accessibility needs, volunteer offers — we read every note.</p>
                <p><a href="<?= htmlspecialchars(site_url('suggestions.php'), ENT_QUOTES, 'UTF-8') ?>">Send a suggestion &rarr;</a></p>
            </article>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
