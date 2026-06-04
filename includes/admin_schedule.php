<?php
/**
 * Admin: schedule manager (CRUD).
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @return list<array{id: int, org_name: string}>
 */
function admin_schedule_nonprofits(): array
{
    return db()->query(
        'SELECT id, org_name FROM nonprofits ORDER BY org_name ASC'
    )->fetchAll();
}

/**
 * @return list<array<string, mixed>>
 */
function admin_schedule_list(): array
{
    $stmt = db()->query(
        'SELECT s.id, s.nonprofit_id, s.event_date, s.menu_description,
                s.expected_guests, s.status, s.notes, s.cancellation_url, n.org_name
         FROM schedule s
         LEFT JOIN nonprofits n ON n.id = s.nonprofit_id
         ORDER BY s.event_date ASC'
    );

    return $stmt->fetchAll();
}

function admin_schedule_is_thursday(string $date): bool
{
    $ts = strtotime($date);

    return $ts !== false && (int) date('N', $ts) === 4;
}

/**
 * @return array{error: ?string, data: ?array<string, mixed>}
 */
function admin_schedule_validate_input(): array
{
    $event_date = trim((string) ($_POST['event_date'] ?? ''));
    $nonprofit_id = (int) ($_POST['nonprofit_id'] ?? 0);
    $menu = trim((string) ($_POST['menu_description'] ?? ''));
    $guests_raw = trim((string) ($_POST['expected_guests'] ?? ''));
    $status = (string) ($_POST['status'] ?? 'draft');
    $notes = trim((string) ($_POST['notes'] ?? ''));
    $cancellation_url = trim((string) ($_POST['cancellation_url'] ?? ''));

    if ($event_date === '') {
        return ['error' => 'Event date is required.', 'data' => null];
    }

    if (!admin_schedule_is_thursday($event_date)) {
        return ['error' => 'Lunch in the Park is on Thursdays only. Please pick a Thursday.', 'data' => null];
    }

    if (!in_array($status, ['draft', 'open', 'confirmed', 'completed', 'cancelled'], true)) {
        $status = 'draft';
    }

    if ($status === 'open') {
        $nonprofit_id = null;
    } elseif ($nonprofit_id < 1) {
        if ($status === 'cancelled') {
            $nonprofit_id = null;
        } else {
            return ['error' => 'Please select a nonprofit host.', 'data' => null];
        }
    } else {
        $np = db()->prepare('SELECT id FROM nonprofits WHERE id = ?');
        $np->execute([$nonprofit_id]);
        if (!$np->fetch()) {
            return ['error' => 'Invalid nonprofit selected.', 'data' => null];
        }
    }

    if ($cancellation_url !== '' && !filter_var($cancellation_url, FILTER_VALIDATE_URL)) {
        return ['error' => 'Cancellation URL must be a valid web address.', 'data' => null];
    }

    $expected_guests = null;
    if ($guests_raw !== '') {
        if (!ctype_digit($guests_raw) || (int) $guests_raw < 0) {
            return ['error' => 'Expected guests must be a whole number.', 'data' => null];
        }
        $expected_guests = (int) $guests_raw;
    }

    return [
        'error' => null,
        'data' => [
            'event_date' => $event_date,
            'nonprofit_id' => $nonprofit_id,
            'menu_description' => $menu !== '' ? $menu : null,
            'expected_guests' => $expected_guests,
            'status' => $status,
            'notes' => $notes !== '' ? $notes : null,
            'cancellation_url' => $cancellation_url !== '' ? $cancellation_url : null,
        ],
    ];
}

function admin_schedule_confirmed_upcoming_count(): int
{
    $stmt = db()->query(
        "SELECT COUNT(*) FROM schedule
         WHERE status = 'confirmed' AND event_date >= CURDATE()"
    );

    return (int) $stmt->fetchColumn();
}

function admin_schedule_handle_post(): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['schedule_action'])) {
        return null;
    }

    $action = (string) $_POST['schedule_action'];

    if ($action === 'delete') {
        $id = (int) ($_POST['schedule_id'] ?? 0);
        if ($id < 1) {
            return 'Could not delete that entry.';
        }

        $stmt = db()->prepare('DELETE FROM schedule WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0
            ? 'Schedule entry deleted.'
            : 'Could not delete that entry.';
    }

    if ($action !== 'save') {
        return null;
    }

    $validated = admin_schedule_validate_input();
    if ($validated['error'] !== null) {
        return $validated['error'];
    }

    $data = $validated['data'];
    $schedule_id = (int) ($_POST['schedule_id'] ?? 0);

    if ($schedule_id > 0) {
        $stmt = db()->prepare(
            'UPDATE schedule SET
                nonprofit_id = ?,
                event_date = ?,
                menu_description = ?,
                expected_guests = ?,
                status = ?,
                notes = ?,
                cancellation_url = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['nonprofit_id'],
            $data['event_date'],
            $data['menu_description'],
            $data['expected_guests'],
            $data['status'],
            $data['notes'],
            $data['cancellation_url'],
            $schedule_id,
        ]);

        return 'Schedule entry updated.';
    }

    $stmt = db()->prepare(
        'INSERT INTO schedule (nonprofit_id, event_date, menu_description, expected_guests, status, notes, cancellation_url)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['nonprofit_id'],
        $data['event_date'],
        $data['menu_description'],
        $data['expected_guests'],
        $data['status'],
        $data['notes'],
        $data['cancellation_url'],
    ]);

    return 'Schedule entry added.';
}

/**
 * @return array<string, mixed>|null
 */
function admin_schedule_entry_for_edit(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT s.*, n.org_name
         FROM schedule s
         LEFT JOIN nonprofits n ON n.id = s.nonprofit_id
         WHERE s.id = ?
         LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function admin_schedule_render_manager(?string $flash_message = null): void
{
    $nonprofits = admin_schedule_nonprofits();
    $entries = admin_schedule_list();
    $edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    $editing = $edit_id > 0 ? admin_schedule_entry_for_edit($edit_id) : null;

    $form = [
        'schedule_id' => $editing ? (int) $editing['id'] : 0,
        'event_date' => $editing ? (string) $editing['event_date'] : ($_POST['event_date'] ?? ''),
        'nonprofit_id' => $editing
            ? (int) ($editing['nonprofit_id'] ?? 0)
            : (int) ($_POST['nonprofit_id'] ?? 0),
        'menu_description' => $editing ? (string) ($editing['menu_description'] ?? '') : ($_POST['menu_description'] ?? ''),
        'expected_guests' => $editing
            ? ($editing['expected_guests'] !== null ? (string) $editing['expected_guests'] : '')
            : ($_POST['expected_guests'] ?? ''),
        'status' => $editing ? (string) $editing['status'] : ($_POST['status'] ?? 'draft'),
        'notes' => $editing ? (string) ($editing['notes'] ?? '') : ($_POST['notes'] ?? ''),
        'cancellation_url' => $editing
            ? (string) ($editing['cancellation_url'] ?? '')
            : ($_POST['cancellation_url'] ?? ''),
    ];
    ?>
    <article class="card dashboard-card dashboard-card--wide" id="schedule-manager">
        <h2>Schedule manager</h2>
        <p class="text-muted">
            Add and edit Thursday lunches. <strong>Open</strong>, <strong>confirmed</strong>, <strong>completed</strong>,
            and <strong>cancelled</strong> dates appear on the
            <a href="<?= htmlspecialchars(site_url('roster.php'), ENT_QUOTES, 'UTF-8') ?>">public schedule</a>.
        </p>

        <?php if ($flash_message): ?>
            <p class="contact-directory__flash" role="status"><?= htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($nonprofits === []): ?>
            <p class="text-accent">Add at least one nonprofit before creating schedule entries.</p>
        <?php else: ?>
            <div class="schedule-form-panel card">
                <h3><?= $form['schedule_id'] > 0 ? 'Edit schedule entry' : 'Add schedule entry' ?></h3>
                <?php if ($form['schedule_id'] > 0): ?>
                    <p><a href="<?= htmlspecialchars(site_url('dashboard/index.php#schedule-manager'), ENT_QUOTES, 'UTF-8') ?>">Cancel edit</a></p>
                <?php endif; ?>

                <form method="post" action="<?= htmlspecialchars(site_url('dashboard/index.php#schedule-manager'), ENT_QUOTES, 'UTF-8') ?>" class="schedule-form" data-enhanced>
                    <input type="hidden" name="schedule_action" value="save">
                    <input type="hidden" name="schedule_id" value="<?= $form['schedule_id'] ?>">

                    <div class="form-group">
                        <label for="event_date">Event date (Thursdays only) <span class="text-accent">*</span></label>
                        <input
                            type="date"
                            id="event_date"
                            name="event_date"
                            required
                            data-thursday-only
                            value="<?= htmlspecialchars($form['event_date'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <p class="form-hint" data-thursday-hint hidden>Please choose a Thursday.</p>
                    </div>

                    <div class="form-group">
                        <label for="nonprofit_id">
                            Nonprofit host <span class="text-accent" data-schedule-host-required>*</span>
                        </label>
                        <select id="nonprofit_id" name="nonprofit_id" data-schedule-nonprofit required>
                            <option value="">No host (open or cancelled date)</option>
                            <?php foreach ($nonprofits as $np): ?>
                                <option value="<?= (int) $np['id'] ?>"
                                    <?= $form['nonprofit_id'] === (int) $np['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $np['org_name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Leave blank for open or cancelled dates.</p>
                    </div>

                    <div class="form-group">
                        <label for="menu_description">Menu description</label>
                        <textarea id="menu_description" name="menu_description" rows="3"><?= htmlspecialchars($form['menu_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="expected_guests">Expected guests</label>
                        <input type="number" id="expected_guests" name="expected_guests" min="0" step="1"
                            value="<?= htmlspecialchars($form['expected_guests'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (['draft', 'open', 'confirmed', 'completed', 'cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $form['status'] === $st ? 'selected' : '' ?>>
                                    <?= ucfirst($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes (public when cancelled)</label>
                        <input type="text" id="notes" name="notes" maxlength="255"
                            placeholder="RAGBRAI, VBS, or a short friendly note"
                            value="<?= htmlspecialchars($form['notes'], ENT_QUOTES, 'UTF-8') ?>">
                        <p class="form-hint">Use <strong>RAGBRAI</strong> or <strong>VBS</strong> for standard cancellation messages.</p>
                    </div>

                    <div class="form-group">
                        <label for="cancellation_url">Cancellation link (optional)</label>
                        <input type="url" id="cancellation_url" name="cancellation_url"
                            placeholder="https://..."
                            value="<?= htmlspecialchars($form['cancellation_url'], ENT_QUOTES, 'UTF-8') ?>">
                        <p class="form-hint">Linked from the word RAGBRAI when notes are set to RAGBRAI.</p>
                    </div>

                    <button type="submit" class="btn btn--primary">
                        <?= $form['schedule_id'] > 0 ? 'Save changes' : 'Add entry' ?>
                    </button>
                </form>
            </div>

            <div class="schedule-table-wrap">
                <h3>All schedule entries</h3>
                <?php if ($entries === []): ?>
                    <p class="text-muted">No schedule entries yet.</p>
                <?php else: ?>
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Organization</th>
                                <th scope="col">Menu</th>
                                <th scope="col">Guests</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $row): ?>
                                <?php
                                $id = (int) $row['id'];
                                $menu = trim((string) ($row['menu_description'] ?? ''));
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime((string) $row['event_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (!empty($row['org_name'])): ?>
                                            <?= htmlspecialchars((string) $row['org_name'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($menu !== ''): ?>
                                            <?= htmlspecialchars($menu, ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $row['expected_guests'] !== null
                                            ? htmlspecialchars((string) $row['expected_guests'], ENT_QUOTES, 'UTF-8')
                                            : 'N/A' ?>
                                    </td>
                                    <td><span class="badge badge--status-<?= htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst((string) $row['status']), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="schedule-table__actions">
                                        <a class="btn btn--secondary btn--small" href="<?= htmlspecialchars(site_url('dashboard/index.php?edit=' . $id . '#schedule-manager'), ENT_QUOTES, 'UTF-8') ?>">Edit</a>
                                        <form method="post" action="<?= htmlspecialchars(site_url('dashboard/index.php#schedule-manager'), ENT_QUOTES, 'UTF-8') ?>" class="schedule-delete-form" data-confirm-delete="Delete this schedule entry?">
                                            <input type="hidden" name="schedule_action" value="delete">
                                            <input type="hidden" name="schedule_id" value="<?= $id ?>">
                                            <button type="submit" class="btn btn--secondary btn--small">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </article>
    <?php
}
