<?php
require_once __DIR__ . '/includes/site.php';

$page_title = 'Menu Ideas';
$body_class = 'page-menu-ideas';
$login_url = site_url('login.php');
$participate_url = site_url('index.php#want-to-participate');

$plate_price = 8.0;
$guest_count = 100;

$menu_ideas = [
    [
        'name' => 'Pulled Pork Sandwiches',
        'cost_low' => 2.00,
        'cost_high' => 2.50,
        'description' => 'Pork shoulders are cheap, cook overnight, and hold perfectly in a roaster oven.',
    ],
    [
        'name' => 'Sloppy Joes',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'Ground beef stretches far with sauce, and volunteers just need to ladle it onto buns.',
    ],
    [
        'name' => 'Walking Tacos',
        'cost_low' => 1.75,
        'cost_high' => 2.25,
        'description' => 'Serving directly into individual chip bags eliminates plates and makes cleanup effortless.',
    ],
    [
        'name' => 'Maid-Rite Sandwiches',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'An Iowa classic. Seasoned loose meat kept warm in a roaster, simple and beloved.',
    ],
    [
        'name' => 'Chili and Cinnamon Rolls',
        'cost_low' => 2.00,
        'cost_high' => 2.50,
        'description' => 'The Iowa school lunch combo that hits pure nostalgia for every Midwest crowd.',
    ],
    [
        'name' => 'Baked Potato Bar',
        'cost_low' => 1.25,
        'cost_high' => 1.75,
        'description' => 'Potatoes are incredibly cheap and guests customize their own plates from cold topping stations.',
    ],
    [
        'name' => 'Hot Dog and Brat Boil',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'Pre-cooked links held hot in water in a roaster. No grill needed.',
    ],
    [
        'name' => 'Chicken Salad Croissants',
        'cost_low' => 2.00,
        'cost_high' => 2.50,
        'description' => 'Everything prepped ahead and served cold. Zero electricity needed at the pavilion.',
    ],
    [
        'name' => 'Goulash',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'Macaroni and ground beef in tomato sauce is cheap, filling, and holds its texture well.',
    ],
    [
        'name' => 'Shredded BBQ Chicken Sandwiches',
        'cost_low' => 1.75,
        'cost_high' => 2.25,
        'description' => 'Chicken breasts shred easily and stay moist in broth or gravy for hours.',
    ],
    [
        'name' => 'Macaroni and Cheese with Ham',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'Bulk cheese sauce and pasta are budget friendly while diced ham adds a hearty feel.',
    ],
    [
        'name' => 'Frito Pie',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'Ladle roaster chili over corn chips for a fast, highly profitable comfort meal.',
    ],
    [
        'name' => 'Ham and Bean Soup',
        'cost_low' => 1.00,
        'cost_high' => 1.50,
        'description' => 'Dried beans are the ultimate high margin food and simmer beautifully with ham.',
    ],
    [
        'name' => 'Biscuits and Sausage Gravy',
        'cost_low' => 1.25,
        'cost_high' => 1.75,
        'description' => 'Gravy holds in a roaster while volunteers split pre-baked biscuits. Easy and filling.',
    ],
    [
        'name' => 'Chicken and Noodles over Mashed Potatoes',
        'cost_low' => 1.75,
        'cost_high' => 2.25,
        'description' => 'The ultimate Midwest comfort staple, entirely batch cooked and crowd approved.',
    ],
    [
        'name' => 'Taco Salad',
        'cost_low' => 1.75,
        'cost_high' => 2.25,
        'description' => 'Same roaster meat as walking tacos but bulked up with cheap iceberg lettuce.',
    ],
    [
        'name' => 'Mostaccioli',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'Baked pasta is inexpensive, universally loved, and stays hot in disposable pans.',
    ],
    [
        'name' => 'Tater Tot Casserole',
        'cost_low' => 2.00,
        'cost_high' => 2.50,
        'description' => 'The ultimate Midwest crowd pleaser, baked ahead and served quickly in squares.',
    ],
    [
        'name' => 'Deli Sandwich Bar',
        'cost_low' => 2.00,
        'cost_high' => 2.50,
        'description' => 'No electricity needed. Everything prepped ahead for a fresh fast moving lunch line.',
    ],
    [
        'name' => 'Pulled BBQ Chicken Sandwiches',
        'cost_low' => 1.75,
        'cost_high' => 2.25,
        'description' => 'A slightly cheaper alternative to pork using the same simple roaster method.',
    ],
    [
        'name' => 'Haystacks',
        'cost_low' => 1.50,
        'cost_high' => 2.00,
        'description' => 'An Iowa church fundraiser staple. Rice or chips base with chili, cheese, and toppings.*',
        'has_footnote' => true,
    ],
];

/**
 * @return array{0: int, 1: int}
 */
function menu_ideas_profit_range(float $cost_low, float $cost_high, float $plate_price, int $guests): array
{
    $profit_min = (int) round(($plate_price - $cost_high) * $guests);
    $profit_max = (int) round(($plate_price - $cost_low) * $guests);

    return [$profit_min, $profit_max];
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero page-hero--menu-ideas" aria-labelledby="menu-ideas-hero-heading">
    <div class="container page-hero__inner">
        <h1 id="menu-ideas-hero-heading">Menu Ideas</h1>
        <p class="page-intro">
            Tried and true meals that work great at the pavilion, keep costs low, and make your fundraiser profitable.
        </p>
    </div>
</section>

<section class="page-content page-content--menu-ideas">
    <div class="container menu-ideas-layout">
        <p class="menu-ideas-intro">
            Every meal below has been chosen with your volunteers and your bottom line in mind. These are crowd pleasers
            that batch cook well, hold their temperature, and don&rsquo;t require a professional kitchen. Prices are
            estimates based on typical bulk ingredient costs. Your actual cost may vary.
        </p>

        <aside class="menu-ideas-profit card" aria-label="Profitability overview">
            <p>
                At $8 per plate, even a $2.50 food cost leaves you with $5.50 per guest. Serve 100 people and walk away
                with $550. Serve 150 and that&rsquo;s $825. These numbers are why Lunch in the Park works.
            </p>
        </aside>

        <div class="menu-ideas-grid">
            <?php foreach ($menu_ideas as $meal): ?>
                <?php
                [$profit_min, $profit_max] = menu_ideas_profit_range(
                    (float) $meal['cost_low'],
                    (float) $meal['cost_high'],
                    $plate_price,
                    $guest_count
                );
                $cost_label = sprintf(
                    '~$%.2f - $%.2f per serving',
                    $meal['cost_low'],
                    $meal['cost_high']
                );
                $profit_label = sprintf(
                    'At %d guests: est. $%s - $%s profit',
                    $guest_count,
                    number_format($profit_min),
                    number_format($profit_max)
                );
                ?>
                <article class="card menu-idea-card">
                    <h2 class="menu-idea-card__title"><?= htmlspecialchars($meal['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="menu-idea-card__cost badge"><?= htmlspecialchars($cost_label, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="menu-idea-card__desc"><?= htmlspecialchars($meal['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="menu-idea-card__profit"><?= htmlspecialchars($profit_label, ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="menu-ideas-footnote text-muted">
            * Haystacks use a rice or chips base. Your group picks what works best.
        </p>

        <article class="menu-ideas-cta card">
            <h2>Ready to pick your menu?</h2>
            <p>Log in to your dashboard to use the P&amp;L planning tool and estimate your costs before your big day.</p>
            <p>
                <a class="btn btn--primary" href="<?= htmlspecialchars($login_url, ENT_QUOTES, 'UTF-8') ?>">Sign in</a>
            </p>
            <p class="menu-ideas-cta__secondary">
                Not signed up yet?
                <a href="<?= htmlspecialchars($participate_url, ENT_QUOTES, 'UTF-8') ?>">We would love to have you.</a>
            </p>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
