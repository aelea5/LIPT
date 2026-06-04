<?php
/**
 * Nonprofit contact directory — reusable component.
 *
 * Usage:
 *   $role = 'admin'; // or 'nonprofit'
 *   require_once .../contact_directory.php';
 *   $message = contact_directory_handle_post($role, $user);
 *   contact_directory_render($role, $user, $message);
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/edit_contact_form.php';

/** @alias for edit_contact_nonprofit_for_user */
function contact_directory_nonprofit_for_user(int $user_id): ?array
{
    return edit_contact_nonprofit_for_user($user_id);
}

/**
 * @param 'admin'|'nonprofit' $role
 * @param array{id: int, username: string, email?: string, role: string}|null $current_user
 */
function contact_directory_handle_post(string $role, ?array $current_user = null): ?string
{
    if (($current_user['id'] ?? 0) < 1) {
        return null;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return null;
    }

    if (isset($_POST['contact_directory_verify']) && $role === 'admin') {
        $nonprofit_id = (int) ($_POST['nonprofit_id'] ?? 0);
        if ($nonprofit_id < 1) {
            return 'Could not update verification date.';
        }

        $stmt = db()->prepare('UPDATE nonprofits SET last_verified = CURDATE() WHERE id = ?');
        $stmt->execute([$nonprofit_id]);

        return $stmt->rowCount() > 0
            ? 'Marked as verified today.'
            : 'Could not update verification date.';
    }

    return edit_contact_handle_post($role, $current_user);
}

/**
 * @param 'admin'|'nonprofit' $role
 * @return list<array<string, mixed>>
 */
function contact_directory_fetch(string $role): array
{
    $sql = <<<'SQL'
        SELECT
            n.id,
            n.user_id,
            n.org_name,
            n.contact_name,
            n.phone,
            n.phone_textable,
            n.preferred_contact,
            n.share_preference,
            n.last_verified,
            n.last_edited_by,
            n.last_edited_at,
            n.admin_notes,
            u.email AS user_email
        FROM nonprofits n
        INNER JOIN users u ON u.id = n.user_id
    SQL;

    if ($role === 'nonprofit') {
        $sql .= " WHERE n.share_preference = 'all'";
    }

    $sql .= ' ORDER BY n.org_name ASC';

    return db()->query($sql)->fetchAll();
}

function contact_directory_preferred_label(string $preferred): string
{
    return match ($preferred) {
        'call'  => 'Call',
        'text'  => 'Text',
        default => 'Email',
    };
}

/**
 * @param array<string, mixed> $entry
 */
function contact_directory_primary_contact(array $entry): string
{
    $preferred = (string) ($entry['preferred_contact'] ?? 'email');
    $phone = trim((string) ($entry['phone'] ?? ''));
    $email = trim((string) ($entry['user_email'] ?? ''));

    if ($preferred === 'call' || $preferred === 'text') {
        if ($phone !== '') {
            $tel = preg_replace('/[^\d+]/', '', $phone) ?? $phone;

            return '<a href="tel:' . htmlspecialchars($tel, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</a>';
        }

        return '<span class="text-muted">No phone on file</span>';
    }

    if ($email !== '') {
        return '<a href="mailto:' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</a>';
    }

    return '<span class="text-muted">No email on file</span>';
}

function contact_directory_share_label(string $share): string
{
    return match ($share) {
        'all'        => 'Share with all Lunch in the Park participants',
        'admin_only' => 'Share with Lunch in the Park admin only',
        default      => 'Do not share my information',
    };
}

/**
 * @param 'admin'|'nonprofit' $role
 * @param array{id: int, username: string, email?: string, role: string} $current_user
 */
function contact_directory_render(
    string $role,
    array $current_user,
    ?string $flash_message = null,
    bool $open_own_edit = false
): void {
    if ($role !== 'admin' && $role !== 'nonprofit') {
        throw new InvalidArgumentException('Contact directory role must be admin or nonprofit.');
    }

    $is_admin = $role === 'admin';
    $entries = contact_directory_fetch($role);
    $own_nonprofit = edit_contact_nonprofit_for_user((int) $current_user['id']);
    $search_id = 'contact-directory-search-' . $role;
    ?>
    <section
        id="contact-directory"
        class="contact-directory"
        aria-labelledby="contact-directory-heading"
        data-contact-directory
    >
        <header class="contact-directory__header contact-directory-no-print">
            <div>
                <h2 id="contact-directory-heading">Contact Directory</h2>
                <p class="contact-directory__intro text-muted">
                    <?php if ($is_admin): ?>
                        All nonprofit contacts. Mark verified after confirming details.
                    <?php else: ?>
                        Organizations sharing contact info with all participants.
                    <?php endif; ?>
                </p>
            </div>
            <div class="contact-directory__header-actions">
                <?php if (!$is_admin): ?>
                    <button type="button" class="btn btn--primary" data-open-dialog="contact-edit-<?= (int) ($own_nonprofit['id'] ?? 0) ?>">
                        Edit my info
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn--secondary" data-print-contact-directory>
                    Print directory
                </button>
            </div>
        </header>

        <?php if ($flash_message !== null && $flash_message !== ''): ?>
            <p class="contact-directory__flash" role="status"><?= htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <div class="contact-directory__toolbar contact-directory-no-print">
            <label class="contact-directory__search-label" for="<?= htmlspecialchars($search_id, ENT_QUOTES, 'UTF-8') ?>">Search</label>
            <input
                type="search"
                id="<?= htmlspecialchars($search_id, ENT_QUOTES, 'UTF-8') ?>"
                class="contact-directory__search"
                placeholder="Org or contact name&hellip;"
                autocomplete="off"
                data-contact-directory-search
            >
        </div>

        <p class="contact-directory__print-title contact-directory-print-only">
            <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>, Nonprofit Contact Directory
        </p>
        <p class="contact-directory__print-meta contact-directory-print-only text-muted">
            Printed <?= htmlspecialchars(date('F j, Y'), ENT_QUOTES, 'UTF-8') ?>
        </p>

        <?php if ($entries === []): ?>
            <p class="contact-directory__empty card">
                <?= $is_admin
                    ? 'No nonprofits in the database yet.'
                    : 'No organizations are currently sharing contacts with the directory.' ?>
            </p>
        <?php else: ?>
            <ul class="contact-directory__list" data-contact-directory-list>
                <?php foreach ($entries as $entry): ?>
                    <?php
                    $entry_id = (int) ($entry['id'] ?? 0);
                    $org_name = (string) ($entry['org_name'] ?? '');
                    $contact_name = trim((string) ($entry['contact_name'] ?? ''));
                    $preferred = (string) ($entry['preferred_contact'] ?? 'email');
                    $share = (string) ($entry['share_preference'] ?? 'admin_only');
                    $phone_textable = (int) ($entry['phone_textable'] ?? 0) === 1;
                    $show_textable = $phone_textable && ($preferred === 'call' || $preferred === 'text');
                    $search_blob = strtolower($org_name . ' ' . $contact_name);
                    $last_verified = $entry['last_verified'] ?? null;
                    $notes_id = 'admin_notes_card_' . $entry_id;
                    ?>
                    <li
                        class="contact-card card"
                        data-contact-card
                        data-search="<?= htmlspecialchars($search_blob, ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <h3 class="contact-card__org"><?= htmlspecialchars($org_name, ENT_QUOTES, 'UTF-8') ?></h3>

                        <?php if ($contact_name !== ''): ?>
                            <p class="contact-card__contact">
                                <span class="contact-card__label">Contact</span>
                                <?= htmlspecialchars($contact_name, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>

                        <p class="contact-card__method">
                            <span class="contact-card__label">Preferred</span>
                            <?= htmlspecialchars(contact_directory_preferred_label($preferred), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <p class="contact-card__detail">
                            <span class="contact-card__label">Reach them</span>
                            <?= contact_directory_primary_contact($entry) ?>
                        </p>

                        <?php if ($show_textable): ?>
                            <p class="contact-card__textable">
                                <span class="badge badge--textable">OK to text this number</span>
                            </p>
                        <?php endif; ?>

                        <?php if ($is_admin): ?>
                            <dl class="contact-card__admin-meta">
                                <?php if (trim((string) ($entry['phone'] ?? '')) !== '' && $preferred === 'email'): ?>
                                    <div class="contact-card__admin-row">
                                        <dt>Phone</dt>
                                        <dd><?= htmlspecialchars((string) $entry['phone'], ENT_QUOTES, 'UTF-8') ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if (trim((string) ($entry['user_email'] ?? '')) !== '' && $preferred !== 'email'): ?>
                                    <div class="contact-card__admin-row">
                                        <dt>Email</dt>
                                        <dd>
                                            <a href="mailto:<?= htmlspecialchars((string) $entry['user_email'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars((string) $entry['user_email'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </dd>
                                    </div>
                                <?php endif; ?>
                                <div class="contact-card__admin-row">
                                    <dt>Sharing</dt>
                                    <dd><?= htmlspecialchars(contact_directory_share_label($share), ENT_QUOTES, 'UTF-8') ?></dd>
                                </div>
                                <div class="contact-card__admin-row">
                                    <dt>Last verified</dt>
                                    <dd>
                                        <?php if ($last_verified): ?>
                                            <?= htmlspecialchars(date('M j, Y', strtotime((string) $last_verified)), ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not yet verified</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <?php if (!empty($entry['last_edited_by'])): ?>
                                    <div class="contact-card__admin-row">
                                        <dt>Last edited</dt>
                                        <dd>
                                            <?= htmlspecialchars((string) $entry['last_edited_by'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php if (!empty($entry['last_edited_at'])): ?>
                                                <span class="text-muted">(
                                                <?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $entry['last_edited_at'])), ENT_QUOTES, 'UTF-8') ?>
                                                )</span>
                                            <?php endif; ?>
                                        </dd>
                                    </div>
                                <?php endif; ?>
                            </dl>

                            <div class="form-group contact-card__admin-notes-form contact-directory-no-print">
                                <label for="<?= htmlspecialchars($notes_id, ENT_QUOTES, 'UTF-8') ?>">
                                    Admin only, not visible to the organization
                                </label>
                                <textarea
                                    id="<?= htmlspecialchars($notes_id, ENT_QUOTES, 'UTF-8') ?>"
                                    rows="3"
                                    readonly
                                    class="contact-card__notes-readonly"
                                ><?= htmlspecialchars((string) ($entry['admin_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <p class="form-hint">Edit the organization to save notes. Use Set reminder for a calendar event.</p>
                                <button
                                    type="button"
                                    class="btn btn--secondary btn--small"
                                    data-google-calendar-reminder
                                    data-notes-source="<?= htmlspecialchars($notes_id, ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    Set reminder
                                </button>
                            </div>

                            <div class="contact-card__actions contact-directory-no-print">
                                <form class="contact-card__verify" method="post" action="#contact-directory">
                                    <input type="hidden" name="contact_directory_verify" value="1">
                                    <input type="hidden" name="nonprofit_id" value="<?= $entry_id ?>">
                                    <button type="submit" class="btn btn--secondary btn--small">
                                        Mark as verified today
                                    </button>
                                </form>
                                <button type="button" class="btn btn--secondary btn--small" data-open-dialog="contact-edit-<?= $entry_id ?>">
                                    Edit
                                </button>
                            </div>

                            <?php edit_contact_render($entry, $role, $current_user); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="contact-directory__no-results contact-directory-no-print" data-contact-directory-empty hidden>
                No matches for your search.
            </p>
        <?php endif; ?>

        <?php if (!$is_admin): ?>
            <div id="edit-my-info" class="contact-directory__own-edit">
                <?php
                $own_entry = $own_nonprofit ?? edit_contact_blank_entry(
                    (int) $current_user['id'],
                    (string) ($current_user['email'] ?? '')
                );
                edit_contact_render($own_entry, $role, $current_user, $open_own_edit);
                ?>
            </div>
        <?php endif; ?>
    </section>
    <?php
}
