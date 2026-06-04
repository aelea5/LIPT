<?php
/**
 * Admin: public suggestions inbox.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

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
        'SELECT id, name, email, message, is_read, created_at
         FROM suggestions
         ORDER BY is_read ASC, created_at DESC'
    );

    return $stmt->fetchAll();
}

function admin_suggestions_handle_post(): ?string
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['suggestion_mark_read'])) {
        return null;
    }

    $id = (int) ($_POST['suggestion_id'] ?? 0);
    if ($id < 1) {
        return 'Could not mark that suggestion as read.';
    }

    $stmt = db()->prepare('UPDATE suggestions SET is_read = 1 WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->rowCount() > 0
        ? 'Marked as read.'
        : 'Could not mark that suggestion as read.';
}

function admin_suggestions_render_inbox(?string $flash_message = null): void
{
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
                        <?php if ($is_unread): ?>
                            <form method="post" action="#suggestions-inbox" class="suggestion-inbox-list__action">
                                <input type="hidden" name="suggestion_mark_read" value="1">
                                <input type="hidden" name="suggestion_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn--secondary btn--small">Mark as read</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
    <?php
}
