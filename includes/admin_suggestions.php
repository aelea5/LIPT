<?php
/**
 * Admin: public suggestions inbox.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function admin_suggestions_flash_take(): ?string
{
    if (empty($_SESSION['litp_suggestions_flash'])) {
        return null;
    }

    $message = (string) $_SESSION['litp_suggestions_flash'];
    unset($_SESSION['litp_suggestions_flash']);

    return $message;
}

function admin_suggestions_unread_count(): int
{
    $stmt = db()->query('SELECT COUNT(*) FROM suggestions WHERE is_read = 0');

    return (int) $stmt->fetchColumn();
}

/**
 * @return list<array<string, mixed>>
 */
function admin_suggestions_list(): array
{
    $stmt = db()->query(
        'SELECT id, name, email, message, created_at, is_read
         FROM suggestions
         ORDER BY is_read ASC, created_at DESC'
    );

    return $stmt->fetchAll();
}

function admin_suggestions_render_inbox(?string $flash_message = null): void
{
    $action_url = site_url('dashboard/actions/suggestion_action.php');
    $unread = admin_suggestions_unread_count();
    $items = admin_suggestions_list();
    ?>
    <article class="card dashboard-card dashboard-card--wide" id="suggestions-inbox">
        <h2>
            Suggestions inbox
            <?php if ($unread > 0): ?>
                <span class="badge badge--count"><?= $unread ?></span>
            <?php endif; ?>
        </h2>
        <p class="text-muted">
            Public suggestions from
            <a href="<?= htmlspecialchars(site_url('suggestions.php'), ENT_QUOTES, 'UTF-8') ?>">suggestions.php</a>.
        </p>

        <?php if ($flash_message): ?>
            <p class="contact-directory__flash" role="status"><?= htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($items === []): ?>
            <p class="text-muted">No suggestions yet.</p>
        <?php else: ?>
            <ul class="suggestion-inbox-list">
                <?php foreach ($items as $item): ?>
                    <?php
                    $id = (int) $item['id'];
                    $is_unread = (int) ($item['is_read'] ?? 0) === 0;
                    $name = trim((string) ($item['name'] ?? ''));
                    $display_name = $name !== '' ? $name : 'Anonymous';
                    $email = trim((string) ($item['email'] ?? ''));
                    ?>
                    <li class="suggestion-inbox-list__item card<?= $is_unread ? ' suggestion-inbox-list__item--unread' : '' ?>">
                        <div class="suggestion-inbox-list__meta">
                            <strong><?= htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if ($email !== ''): ?>
                                &middot;
                                <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php else: ?>
                                &middot;
                                <span class="text-muted">No email</span>
                            <?php endif; ?>
                            <span class="text-muted">
                                &middot;
                                <?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $item['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <?php if ($is_unread): ?>
                                <span class="badge badge--unread">Unread</span>
                            <?php endif; ?>
                        </div>
                        <p class="suggestion-inbox-list__message"><?= nl2br(htmlspecialchars((string) $item['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <div class="suggestion-inbox-list__actions">
                            <?php if ($is_unread): ?>
                                <form method="post" action="<?= htmlspecialchars($action_url, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="suggestion_id" value="<?= $id ?>">
                                    <button type="submit" class="btn btn--secondary btn--small">Mark as read</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="<?= htmlspecialchars($action_url, ENT_QUOTES, 'UTF-8') ?>" data-confirm-delete="Delete this suggestion?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="suggestion_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn--secondary btn--small">Delete</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
    <?php
}
