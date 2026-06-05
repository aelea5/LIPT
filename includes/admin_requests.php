<?php
/**
 * Admin: pending nonprofit participation requests.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function admin_requests_pending_count(): int
{
    $stmt = db()->query("SELECT COUNT(*) FROM nonprofit_requests WHERE status = 'pending'");

    return (int) $stmt->fetchColumn();
}

/**
 * @return list<array<string, mixed>>
 */
function admin_requests_pending_list(): array
{
    $stmt = db()->query(
        "SELECT * FROM nonprofit_requests WHERE status = 'pending' ORDER BY created_at ASC"
    );

    return $stmt->fetchAll();
}

function admin_requests_username_available(string $username): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$username]);

    return (int) $stmt->fetchColumn() === 0;
}

function admin_requests_username_from_org(string $org_name): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '', $org_name) ?? '');
    if (strlen($slug) < 3) {
        $slug = 'nonprofit';
    }

    return substr($slug, 0, 48);
}

function admin_requests_unique_username(string $base): string
{
    $candidate = $base;
    $n = 1;
    while (!admin_requests_username_available($candidate)) {
        $candidate = $base . $n;
        $n++;
    }

    return $candidate;
}

/**
 * @param array<string, mixed> $request
 */
function admin_requests_default_username(array $request): string
{
    $requested = trim((string) ($request['requested_username'] ?? ''));
    if ($requested !== '' && preg_match('/^[a-zA-Z0-9_]{3,64}$/', $requested)) {
        return admin_requests_unique_username($requested);
    }

    return admin_requests_unique_username(
        admin_requests_username_from_org((string) ($request['org_name'] ?? ''))
    );
}

/**
 * @param array<string, mixed> $request
 */
function admin_requests_resolve_username(array $request, string $admin_username): string
{
    $admin_username = trim($admin_username);
    if ($admin_username !== '') {
        return $admin_username;
    }

    $requested = trim((string) ($request['requested_username'] ?? ''));
    if ($requested !== '') {
        return $requested;
    }

    return admin_requests_username_from_org((string) ($request['org_name'] ?? ''));
}

/**
 * @param array{id: int, username: string} $admin_user
 */
function admin_requests_handle_post(array $admin_user): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['request_action'])) {
        return null;
    }

    $request_id = (int) ($_POST['request_id'] ?? 0);
    $action = (string) $_POST['request_action'];

    if ($request_id < 1) {
        return 'Invalid request.';
    }

    $stmt = db()->prepare('SELECT * FROM nonprofit_requests WHERE id = ? LIMIT 1');
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();

    if (!$request || ($request['status'] ?? '') !== 'pending') {
        return 'That request is no longer pending.';
    }

    if ($action === 'decline') {
        $update = db()->prepare("UPDATE nonprofit_requests SET status = 'declined' WHERE id = ?");
        $update->execute([$request_id]);

        return $update->rowCount() > 0
            ? 'Request declined.'
            : 'Could not decline that request.';
    }

    if ($action !== 'approve') {
        return null;
    }

    $password = (string) ($_POST['approve_password'] ?? '');
    if (strlen($password) < 8) {
        return 'Temporary password must be at least 8 characters.';
    }

    $username = admin_requests_resolve_username(
        $request,
        (string) ($_POST['approve_username'] ?? '')
    );

    if (!preg_match('/^[a-zA-Z0-9_]{3,64}$/', $username)) {
        return 'Username must be 3–64 characters (letters, numbers, underscores).';
    }

    if (!admin_requests_username_available($username)) {
        return 'That username is already taken. Choose another.';
    }

    $email = trim((string) ($request['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'This request does not have a valid email address.';
    }

    $email_check = db()->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $email_check->execute([$email]);
    if ((int) $email_check->fetchColumn() > 0) {
        return 'A user account with that email already exists.';
    }

    $org_name = trim((string) ($request['org_name'] ?? ''));
    $contact_name = trim((string) ($request['contact_name'] ?? ''));
    if ($org_name === '' || $contact_name === '') {
        return 'This request is missing required organization or contact details.';
    }

    $phone = trim((string) ($request['phone'] ?? ''));
    $phone = $phone !== '' ? $phone : null;

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) {
        return 'Could not set the temporary password. Please try again.';
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $user_stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password, role, force_password_change) VALUES (?, ?, ?, ?, 1)'
        );
        $user_stmt->execute([$username, $email, $hash, 'nonprofit']);
        $user_id = (int) $pdo->lastInsertId();

        if ($user_id < 1) {
            throw new RuntimeException('User insert did not return an ID.');
        }

        $np_stmt = $pdo->prepare(
            'INSERT INTO nonprofits (user_id, org_name, contact_name, phone) VALUES (?, ?, ?, ?)'
        );
        $np_stmt->execute([$user_id, $org_name, $contact_name, $phone]);

        $req_stmt = $pdo->prepare(
            "UPDATE nonprofit_requests SET status = 'approved' WHERE id = ? AND status = 'pending'"
        );
        $req_stmt->execute([$request_id]);

        if ($req_stmt->rowCount() < 1) {
            throw new RuntimeException('Request status was not updated.');
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('approve request: ' . $e->getMessage());

        return 'Could not create the account. No changes were saved. Please try again.';
    }

    return sprintf(
        'Approved. New username: %s. Share it with the organization along with the temporary password you set.',
        $username
    );
}

function admin_requests_render_card(?string $flash_message = null): void
{
    $pending = admin_requests_pending_list();
    $count = count($pending);
    $form_action = site_url('dashboard/index.php') . '#pending-requests';
    ?>
    <article class="card dashboard-card dashboard-card--wide" id="pending-requests">
        <h2>
            Pending requests
            <?php if ($count > 0): ?>
                <span class="badge badge--count"><?= $count ?></span>
            <?php endif; ?>
        </h2>

        <?php if ($flash_message): ?>
            <p class="contact-directory__flash" role="status"><?= htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($count === 0): ?>
            <p class="text-muted">No pending participation requests.</p>
        <?php else: ?>
            <ul class="request-list">
                <?php foreach ($pending as $request): ?>
                    <?php
                    $rid = (int) $request['id'];
                    $default_username = admin_requests_default_username($request);
                    ?>
                    <li class="request-list__item card">
                        <div class="request-list__summary">
                            <h3><?= htmlspecialchars((string) $request['org_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="text-muted">
                                <?= htmlspecialchars((string) $request['contact_name'], ENT_QUOTES, 'UTF-8') ?>
                                &middot;
                                <a href="mailto:<?= htmlspecialchars((string) $request['email'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $request['email'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <?php if (!empty($request['phone'])): ?>
                                    &middot; <?= htmlspecialchars((string) $request['phone'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($request['requested_username'])): ?>
                                <p class="form-hint">Requested username: <?= htmlspecialchars((string) $request['requested_username'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <p class="form-hint">Submitted <?= htmlspecialchars(date('M j, Y', strtotime((string) $request['created_at'])), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <div class="request-list__actions">
                            <form method="post" action="<?= htmlspecialchars($form_action, ENT_QUOTES, 'UTF-8') ?>" class="request-approve-form">
                                <input type="hidden" name="request_action" value="approve">
                                <input type="hidden" name="request_id" value="<?= $rid ?>">
                                <div class="form-group">
                                    <label for="approve_username_<?= $rid ?>">Username</label>
                                    <input type="text" id="approve_username_<?= $rid ?>" name="approve_username"
                                        value="<?= htmlspecialchars($default_username, ENT_QUOTES, 'UTF-8') ?>"
                                        pattern="[a-zA-Z0-9_]{3,64}"
                                        placeholder="Auto-generated from org name if blank">
                                </div>
                                <div class="form-group">
                                    <label for="approve_password_<?= $rid ?>">Temporary password</label>
                                    <input type="text" id="approve_password_<?= $rid ?>" name="approve_password"
                                        required minlength="8" autocomplete="new-password">
                                </div>
                                <button type="submit" class="btn btn--primary btn--small">Approve</button>
                            </form>

                            <form method="post" action="<?= htmlspecialchars($form_action, ENT_QUOTES, 'UTF-8') ?>" class="request-decline-form">
                                <input type="hidden" name="request_action" value="decline">
                                <input type="hidden" name="request_id" value="<?= $rid ?>">
                                <button type="submit" class="btn btn--secondary btn--small">Decline</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
    <?php
}
