<?php
/**
 * Public schedule roster from database.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @return array<string, true>
 */
function roster_schedule_column_map(): array
{
    static $columns = null;

    if ($columns === null) {
        $columns = [];
        $stmt = db()->query('SHOW COLUMNS FROM schedule');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $columns[(string) $col['Field']] = true;
        }
    }

    return $columns;
}

/**
 * @return list<array<string, mixed>>
 */
function roster_fetch_schedule_rows(): array
{
    $available = roster_schedule_column_map();
    $wanted = ['id', 'event_date', 'menu_description', 'status', 'notes', 'cancellation_url', 'nonprofit_id'];
    $select = [];

    foreach ($wanted as $column) {
        if (isset($available[$column])) {
            $select[] = $column;
        }
    }

    if ($select === []) {
        throw new RuntimeException('The schedule table is missing required columns.');
    }

    $sql = 'SELECT ' . implode(', ', $select) . ' FROM schedule ORDER BY event_date ASC';

    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param list<array<string, mixed>> $rows
 * @return array<int, string>
 */
function roster_org_names_by_id(array $rows): array
{
    $ids = [];

    foreach ($rows as $row) {
        $id = (int) ($row['nonprofit_id'] ?? 0);
        if ($id > 0) {
            $ids[$id] = true;
        }
    }

    if ($ids === []) {
        return [];
    }

    $id_list = array_keys($ids);
    $placeholders = implode(',', array_fill(0, count($id_list), '?'));
    $stmt = db()->prepare(
        "SELECT id, org_name FROM nonprofits WHERE id IN ($placeholders)"
    );
    $stmt->execute($id_list);

    $names = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $org) {
        $names[(int) $org['id']] = (string) $org['org_name'];
    }

    return $names;
}

/**
 * Normalize schedule status from the database for consistent comparisons.
 */
function roster_row_status(array $row): string
{
    return strtolower(trim((string) ($row['status'] ?? '')));
}

/**
 * @return list<string>
 */
function roster_public_statuses(): array
{
    return ['open', 'confirmed', 'completed', 'cancelled'];
}

/**
 * Resolve how a row should display when status was saved before ENUM migration (e.g. still draft).
 */
function roster_resolve_status(array $row): string
{
    $status = roster_row_status($row);

    if (in_array($status, roster_public_statuses(), true)) {
        return $status;
    }

    if ($status !== 'draft' && $status !== '') {
        return $status;
    }

    $has_host = roster_row_has_host($row);
    $notes = trim((string) ($row['notes'] ?? ''));

    if (!$has_host) {
        return $notes !== '' ? 'cancelled' : 'open';
    }

    return 'draft';
}

function roster_row_has_host(array $row): bool
{
    $nonprofit_id = $row['nonprofit_id'] ?? null;

    if ($nonprofit_id === null || $nonprofit_id === '') {
        return false;
    }

    return (int) $nonprofit_id > 0;
}

/**
 * All public schedule rows, including entries with no nonprofit host (open/cancelled).
 *
 * @return list<array<string, mixed>>
 */
function roster_public_entries(): array
{
    $rows = roster_fetch_schedule_rows();
    $org_names = roster_org_names_by_id($rows);
    $public = [];

    foreach ($rows as $row) {
        $status = roster_resolve_status($row);

        if (!in_array($status, roster_public_statuses(), true)) {
            continue;
        }

        $row['status'] = $status;
        $np_id = (int) ($row['nonprofit_id'] ?? 0);
        $row['org_name'] = $np_id > 0 ? ($org_names[$np_id] ?? null) : null;
        $public[] = $row;
    }

    return $public;
}

/**
 * @param list<array<string, mixed>> $entries
 * @return array{main: list<array<string, mixed>>, past: list<array<string, mixed>>}
 */
function roster_split_entries(array $entries): array
{
    $today = date('Y-m-d');
    $main = [];
    $past = [];

    foreach ($entries as $row) {
        $date = (string) $row['event_date'];
        $status = roster_row_status($row);
        $is_past_hosted = $date < $today && in_array($status, ['confirmed', 'completed'], true);

        if ($is_past_hosted) {
            $past[] = $row;
        } else {
            $main[] = $row;
        }
    }

    return ['main' => $main, 'past' => $past];
}

function roster_participate_url(): string
{
    return site_url('index.php#want-to-participate');
}

/**
 * @param array<string, mixed> $row
 */
function roster_render_cancelled_note(array $row): void
{
    $notes = trim((string) ($row['notes'] ?? ''));
    $url = trim((string) ($row['cancellation_url'] ?? ''));
    $upper = strtoupper($notes);

    if ($upper === 'RAGBRAI' || stripos($notes, 'ragbrai') !== false) {
        ?>
        <em class="roster-cancelled-note">
            No lunch this week, we are welcoming
            <?php if ($url !== ''): ?>
                <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">RAGBRAI</a>
            <?php else: ?>
                RAGBRAI
            <?php endif; ?>
            to town!
        </em>
        <?php
        return;
    }

    if ($upper === 'VBS' || stripos($notes, 'vbs') !== false) {
        ?>
        <em class="roster-cancelled-note">No lunch this week, see you next time!</em>
        <?php
        return;
    }

    if ($notes !== '') {
        ?>
        <em class="roster-cancelled-note"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?></em>
        <?php
        return;
    }

    ?>
    <em class="roster-cancelled-note text-muted">No lunch this week.</em>
    <?php
}

/**
 * @param array<string, mixed> $row
 */
function roster_render_row(array $row): void
{
    $status = roster_row_status($row);
    $date_label = date('l, F j, Y', strtotime((string) $row['event_date']));

    if ($status === 'open') {
        ?>
        <tr class="roster-row roster-row--open">
            <td><?= htmlspecialchars($date_label, ENT_QUOTES, 'UTF-8') ?></td>
            <td colspan="2">
                <p class="roster-open-available"><strong>This date is available!</strong></p>
                <p class="roster-open-invite">
                    Could your org host this Thursday? We would love to have you.
                    <a href="<?= htmlspecialchars(roster_participate_url(), ENT_QUOTES, 'UTF-8') ?>">Tell us you&rsquo;re interested</a>
                </p>
            </td>
        </tr>
        <?php
        return;
    }

    if ($status === 'cancelled') {
        ?>
        <tr class="roster-row roster-row--cancelled">
            <td><?= htmlspecialchars($date_label, ENT_QUOTES, 'UTF-8') ?></td>
            <td colspan="2">
                <?php roster_render_cancelled_note($row); ?>
            </td>
        </tr>
        <?php
        return;
    }

    $menu = trim((string) ($row['menu_description'] ?? ''));
    $org = trim((string) ($row['org_name'] ?? ''));
    ?>
    <tr class="roster-row roster-row--confirmed">
        <td><?= htmlspecialchars($date_label, ENT_QUOTES, 'UTF-8') ?></td>
        <td>
            <?php if ($org !== ''): ?>
                <?= htmlspecialchars($org, ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                <span class="text-muted">Host TBA</span>
            <?php endif; ?>
        </td>
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

function roster_render_schedule(): void
{
    try {
        $entries = roster_public_entries();
    } catch (Throwable $e) {
        error_log('roster_public_entries: ' . $e->getMessage());
        ?>
        <article class="card">
            <p class="roster-empty">We could not load the schedule right now. Please check back soon.</p>
        </article>
        <?php
        return;
    }

    $split = roster_split_entries($entries);
    $main = $split['main'];
    $past = $split['past'];

    if ($main === [] && $past === []) {
        ?>
        <article class="card">
            <p class="roster-empty">Dates are being finalized. Check back soon.</p>
        </article>
        <?php
        return;
    }
    ?>

    <article class="card roster-card">
        <?php if ($main === []): ?>
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
                    <?php foreach ($main as $row): ?>
                        <?php roster_render_row($row); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </article>

    <?php if ($past !== []): ?>
        <details class="roster-past card">
            <summary class="roster-past__summary">Earlier this summer</summary>
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
