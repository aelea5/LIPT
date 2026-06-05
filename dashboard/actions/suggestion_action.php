<?php
/**
 * Admin: mark suggestion read or delete, then redirect to dashboard.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/site.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';

auth_require_admin();

$redirect = site_url('dashboard/index.php#suggestions-inbox');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$id = (int) ($_POST['suggestion_id'] ?? 0);

if ($id < 1) {
    $_SESSION['litp_suggestions_flash'] = 'Could not find that suggestion.';
    header('Location: ' . $redirect);
    exit;
}

try {
    if ($action === 'mark_read') {
        $stmt = db()->prepare('UPDATE suggestions SET is_read = 1 WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['litp_suggestions_flash'] = $stmt->rowCount() > 0
            ? 'Marked as read.'
            : 'Could not mark that suggestion as read.';
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM suggestions WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['litp_suggestions_flash'] = $stmt->rowCount() > 0
            ? 'Suggestion deleted.'
            : 'Could not delete that suggestion.';
    } else {
        $_SESSION['litp_suggestions_flash'] = 'Unknown action.';
    }
} catch (PDOException $e) {
    error_log('suggestion_action.php: ' . $e->getMessage());
    $_SESSION['litp_suggestions_flash'] = 'Something went wrong. Please try again.';
}

header('Location: ' . $redirect);
exit;
