<?php
$page_title = 'Suggestions';
$body_class = 'page-suggestions';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Suggestions</h1>
        <p class="page-intro">
            Tell us what would make Lunch in the Park better for you and your neighbors.
            You don&rsquo;t need an account — just your ideas.
        </p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <form class="card" action="#" method="post" data-enhanced>
            <div class="form-group">
                <label for="suggestion-name">Your name <span class="text-muted">(optional)</span></label>
                <input type="text" id="suggestion-name" name="name" autocomplete="name">
            </div>

            <div class="form-group">
                <label for="suggestion-email">Email <span class="text-muted">(optional, if you want a reply)</span></label>
                <input type="email" id="suggestion-email" name="email" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="suggestion-message">Your suggestion</label>
                <textarea id="suggestion-message" name="message" rows="6" required></textarea>
                <p class="form-hint">Menu ideas, accessibility, volunteer offers, or anything else on your mind.</p>
            </div>

            <button type="submit" class="btn btn--primary">Send suggestion</button>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
