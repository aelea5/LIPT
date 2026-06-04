<?php
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/roster_display.php';

$page_title = 'Schedule';
$body_class = 'page-roster';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Schedule</h1>
        <p class="page-intro">
            Lunch in the Park runs on <strong>Thursdays only</strong>, from June through August, not every Thursday,
            only the dates listed below. Each lunch is <strong>$8</strong>. A different host team serves each scheduled day.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container roster-layout">
        <?php roster_render_schedule(); ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
