<?php
/**
 * Public schedule roster from database.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @return array{upcoming: list<array<string, mixed>>, past: list<array<string, mixed>>}
 */
function roster_public_entries(): array
{
    $stmt = db()->query(
        "SELECT s.event_date, s.menu_description, n.org_name
         FROM schedule s
         INNER JOIN nonprofits n ON n.id = s.nonprofit_id
         WHERE s.status IN ('confirmed', 'completed')
         ORDER BY s.event_date ASC"
    );
    $rows = $stmt->fetchAll();

    $today = date('Y-m-d');
    $upcoming = [];
    $past = [];

    foreach ($rows as $row) {
        $date = (string) $row['event_date'];
        if ($date >= $today) {
            $upcoming[] = $row;
        } else {
            $past[] = $row;
        }
    }

    return ['upcoming' => $upcoming, 'past' => $past];
}

function roster_render_schedule(): void
{
    $data = roster_public_entries();
    $upcoming = $data['upcoming'];
    $past = $data['past'];
    $has_any = $upcoming !== [] || $past !== [];

    if (!$has_any): ?>
        <article class="card">
            <h2>Upcoming Thursdays</h2>
            <p class="roster-empty">Dates are being finalized. Check back soon.</p>
        </article>
        <?php
        return;
    endif;
    ?>

    <article class="card">
        <h2>Upcoming Thursdays</h2>
        <?php if ($upcoming === []): ?>
            <p class="roster-empty text-muted">No upcoming dates posted right now.</p>
        <?php else: ?>
            <table class="roster-table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Host</th>
                        <th scope="col">Menu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcoming as $row): ?>
                        <?php roster_render_row($row); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>

    <?php if ($past !== []): ?>
        <details class="roster-past card">
            <summary class="roster-past__summary">Past lunches</summary>
            <table class="roster-table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Host</th>
                        <th scope="col">Menu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($past as $row): ?>
                        <?php roster_render_row($row); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </details>
    <?php endif;
}

/**
 * @param array<string, mixed> $row
 */
function roster_render_row(array $row): void
{
    $menu = trim((string) ($row['menu_description'] ?? ''));
    ?>
    <tr>
        <td><?= htmlspecialchars(date('l, F j, Y', strtotime((string) $row['event_date'])), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $row['org_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td>
            <?php if ($menu !== ''): ?>
                <?= htmlspecialchars($menu, ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                <span class="text-muted">Menu TBA</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}
