<?php
/**
 * Reusable nonprofit contact edit form (admin and nonprofit).
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * @return array<string, mixed>|null
 */
function edit_contact_nonprofit_for_user(int $user_id): ?array
{
    $stmt = db()->prepare(
        'SELECT n.*, u.email AS user_email
         FROM nonprofits n
         INNER JOIN users u ON u.id = n.user_id
         WHERE n.user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * @return array<string, mixed>
 */
function edit_contact_blank_entry(int $user_id, string $email = ''): array
{
    return [
        'id' => 0,
        'user_id' => $user_id,
        'org_name' => '',
        'contact_name' => '',
        'phone' => '',
        'phone_textable' => 0,
        'preferred_contact' => 'email',
        'share_preference' => 'admin_only',
        'description' => '',
        'website_url' => '',
        'facebook_url' => '',
        'admin_notes' => '',
        'user_email' => $email,
    ];
}

/**
 * @param 'admin'|'nonprofit' $role
 * @param array{id: int, username: string, email?: string, role: string} $current_user
 */
function edit_contact_handle_post(string $role, array $current_user): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['edit_contact_save'])) {
        return null;
    }

    return edit_contact_save($role, $current_user);
}

/**
 * @param 'admin'|'nonprofit' $role
 * @param array{id: int, username: string, role: string} $current_user
 */
function edit_contact_save(string $role, array $current_user): string
{
    $nonprofit_id = (int) ($_POST['nonprofit_id'] ?? 0);
    $org_name = trim((string) ($_POST['org_name'] ?? ''));
    $contact_name = trim((string) ($_POST['contact_name'] ?? ''));

    if ($org_name === '' || $contact_name === '') {
        return 'Organization name and contact name are required.';
    }

    $phone = trim((string) ($_POST['phone'] ?? ''));
    $phone_textable = isset($_POST['phone_textable']) ? 1 : 0;
    $preferred = (string) ($_POST['preferred_contact'] ?? 'email');
    $share = (string) ($_POST['share_preference'] ?? 'admin_only');

    if (!in_array($preferred, ['call', 'text', 'email'], true)) {
        $preferred = 'email';
    }
    if (!in_array($share, ['all', 'admin_only', 'none'], true)) {
        $share = 'admin_only';
    }

    $description = trim((string) ($_POST['description'] ?? ''));
    $website_url = trim((string) ($_POST['website_url'] ?? ''));
    $facebook_url = trim((string) ($_POST['facebook_url'] ?? ''));
    $admin_notes = trim((string) ($_POST['admin_notes'] ?? ''));
    $editor = (string) $current_user['username'];
    $is_admin = $role === 'admin';

    if ($is_admin && $nonprofit_id < 1) {
        return 'Invalid organization.';
    }

    if (!$is_admin) {
        $own = edit_contact_nonprofit_for_user((int) $current_user['id']);
        if (!$own) {
            $stmt = db()->prepare(
                'INSERT INTO nonprofits (
                    user_id, org_name, contact_name, phone, phone_textable,
                    preferred_contact, share_preference, description,
                    website_url, facebook_url, last_edited_by, last_edited_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([
                (int) $current_user['id'],
                $org_name,
                $contact_name,
                $phone !== '' ? $phone : null,
                $phone_textable,
                $preferred,
                $share,
                $description !== '' ? $description : null,
                $website_url !== '' ? $website_url : null,
                $facebook_url !== '' ? $facebook_url : null,
                $editor,
            ]);

            return 'Your information was saved successfully.';
        }

        if ((int) $own['id'] !== $nonprofit_id) {
            return 'You can only edit your own organization.';
        }
    }

    $sql = 'UPDATE nonprofits SET
        org_name = ?,
        contact_name = ?,
        phone = ?,
        phone_textable = ?,
        preferred_contact = ?,
        share_preference = ?,
        description = ?,
        website_url = ?,
        facebook_url = ?,
        last_edited_by = ?,
        last_edited_at = NOW()';

    $params = [
        $org_name,
        $contact_name,
        $phone !== '' ? $phone : null,
        $phone_textable,
        $preferred,
        $share,
        $description !== '' ? $description : null,
        $website_url !== '' ? $website_url : null,
        $facebook_url !== '' ? $facebook_url : null,
        $editor,
    ];

    if ($is_admin) {
        $sql .= ', admin_notes = ?';
        $params[] = $admin_notes !== '' ? $admin_notes : null;
    }

    $sql .= ' WHERE id = ?';
    $params[] = $nonprofit_id;

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return 'Saved successfully.';
}

/**
 * @param array<string, mixed> $entry
 * @param 'admin'|'nonprofit' $role
 * @param array{id: int, username: string, role: string} $current_user
 */
function edit_contact_render(
    array $entry,
    string $role,
    array $current_user,
    bool $open_on_load = false
): void {
    $is_admin = $role === 'admin';
    $id = (int) ($entry['id'] ?? 0);
    $dialog_id = 'contact-edit-' . $id;
    $is_own = !$is_admin && (int) ($entry['user_id'] ?? 0) === (int) $current_user['id'];
    ?>
    <dialog
        id="<?= htmlspecialchars($dialog_id, ENT_QUOTES, 'UTF-8') ?>"
        class="contact-edit-dialog"
        <?php if ($open_on_load): ?>open<?php endif; ?>
    >
        <form method="post" action="#contact-directory" class="contact-edit-form" id="edit-my-info-form">
            <input type="hidden" name="edit_contact_save" value="1">
            <input type="hidden" name="nonprofit_id" value="<?= $id ?>">

            <header class="contact-edit-dialog__header">
                <h3><?= $is_own ? 'Edit my info' : 'Edit organization' ?></h3>
                <button type="button" class="contact-edit-dialog__close" data-close-dialog aria-label="Close">&times;</button>
            </header>

            <div class="form-group">
                <label for="org_name_<?= $id ?>">Organization name <span class="text-accent">*</span></label>
                <input type="text" id="org_name_<?= $id ?>" name="org_name" required
                    value="<?= htmlspecialchars((string) ($entry['org_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="contact_name_<?= $id ?>">Contact name <span class="text-accent">*</span></label>
                <input type="text" id="contact_name_<?= $id ?>" name="contact_name" required
                    value="<?= htmlspecialchars((string) ($entry['contact_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="phone_<?= $id ?>">Phone</label>
                <input type="tel" id="phone_<?= $id ?>" name="phone"
                    value="<?= htmlspecialchars((string) ($entry['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group form-group--checkbox">
                <label>
                    <input type="checkbox" name="phone_textable" value="1"
                        <?= (int) ($entry['phone_textable'] ?? 0) === 1 ? 'checked' : '' ?>>
                    This number can receive texts
                </label>
            </div>

            <fieldset class="form-group">
                <legend>Preferred contact method</legend>
                <?php foreach (['call' => 'Call', 'text' => 'Text', 'email' => 'Email'] as $val => $label): ?>
                    <label class="radio-inline">
                        <input type="radio" name="preferred_contact" value="<?= $val ?>"
                            <?= ($entry['preferred_contact'] ?? 'email') === $val ? 'checked' : '' ?>>
                        <?= $label ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <fieldset class="form-group">
                <legend>Sharing preference</legend>
                <label class="radio-stack">
                    <input type="radio" name="share_preference" value="all"
                        <?= ($entry['share_preference'] ?? '') === 'all' ? 'checked' : '' ?>>
                    Share with all Lunch in the Park participants
                </label>
                <label class="radio-stack">
                    <input type="radio" name="share_preference" value="admin_only"
                        <?= ($entry['share_preference'] ?? 'admin_only') === 'admin_only' ? 'checked' : '' ?>>
                    Share with Lunch in the Park admin only
                </label>
                <label class="radio-stack">
                    <input type="radio" name="share_preference" value="none"
                        <?= ($entry['share_preference'] ?? '') === 'none' ? 'checked' : '' ?>>
                    Do not share my information
                </label>
            </fieldset>

            <div class="form-group">
                <label for="website_url_<?= $id ?>">Website</label>
                <input type="url" id="website_url_<?= $id ?>" name="website_url"
                    value="<?= htmlspecialchars((string) ($entry['website_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="facebook_url_<?= $id ?>">Facebook</label>
                <input type="url" id="facebook_url_<?= $id ?>" name="facebook_url"
                    value="<?= htmlspecialchars((string) ($entry['facebook_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <?php if ($is_admin): ?>
                <div class="form-group contact-edit-form__admin-notes">
                    <label for="admin_notes_<?= $id ?>">
                        Admin only, not visible to the organization
                    </label>
                    <textarea
                        id="admin_notes_<?= $id ?>"
                        name="admin_notes"
                        rows="4"
                        data-admin-notes-input
                    ><?= htmlspecialchars((string) ($entry['admin_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <p class="form-hint">Private notes for coordinators.</p>
                    <button
                        type="button"
                        class="btn btn--secondary btn--small"
                        data-google-calendar-reminder
                        data-notes-source="admin_notes_<?= $id ?>"
                    >
                        Set reminder
                    </button>
                </div>
            <?php endif; ?>

            <footer class="contact-edit-dialog__footer">
                <button type="submit" class="btn btn--primary">Save</button>
                <button type="button" class="btn btn--secondary" data-close-dialog>Cancel</button>
            </footer>
        </form>
    </dialog>
    <?php
}
