<?php
require_once __DIR__ . '/includes/site.php';
require_once __DIR__ . '/includes/roster_display.php';

$page_title = 'Schedule';
$body_class = 'page-roster';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>This Summer&rsquo;s Schedule</h1>
        <p class="page-intro">
            We run on selected Thursdays from June through August, not every week. Check the dates below,
            show up hungry, and bring $8. A different Jesup nonprofit hosts each week and keeps every dollar
            raised. Lemonade, water, coffee, and dessert come with every plate. Every $8 plate includes food,
            dessert, and a drink. Dates fill up. Available slots are open to new organizations through May 15th.
        </p>
        <p class="roster-location">
            Every serving Thursday, 11am to 1pm at the Land O&rsquo; Corn Park Pavilion on Young Street between
            Main and 6th, right in the heart of downtown Jesup.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container roster-layout">
        <?php roster_render_schedule(); ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
