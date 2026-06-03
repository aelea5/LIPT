<?php
$page_title = 'Schedule';
$body_class = 'page-roster';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Schedule</h1>
        <p class="page-intro">
            Lunch in the Park runs on <strong>Thursdays only</strong>, from June through August — not every Thursday,
            only the dates listed below. Each lunch is <strong>$8</strong>. A different host team serves each scheduled day.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <article class="card">
            <h2>Upcoming Thursdays</h2>
            <p class="text-muted">Updated weekly.</p>
            <table>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Host</th>
                        <th scope="col">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" class="text-muted">No dates posted yet.</td>
                    </tr>
                </tbody>
            </table>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
