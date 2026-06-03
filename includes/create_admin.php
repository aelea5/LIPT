<?php
/**
 * One-time web setup: creates the first admin user, then delete this file.
 */

declare(strict_types=1);

require_once __DIR__ . '/site.php';
require_once __DIR__ . '/db.php';

const CREATE_ADMIN_USERNAME = 'aeleaita5';
const CREATE_ADMIN_EMAIL = 'j_floto@yahoo.com';
const CREATE_ADMIN_PASSWORD = '12171314@Jf';

function create_admin_exists(): bool
{
    $stmt = db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");

    return (int) $stmt->fetchColumn() > 0;
}

$status = 'info';
$heading = 'Admin setup';
$message = '';
$detail = '';

if (create_admin_exists()) {
    $status = 'warning';
    $heading = 'Already set up';
    $message = 'An admin account already exists. No new user was created.';
    $detail = 'Delete includes/create_admin.php from your server for security.';
} else {
    $hash = password_hash(CREATE_ADMIN_PASSWORD, PASSWORD_DEFAULT);

    try {
        $stmt = db()->prepare(
            'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            CREATE_ADMIN_USERNAME,
            CREATE_ADMIN_EMAIL,
            $hash,
            'admin',
        ]);

        $status = 'success';
        $heading = 'Admin created';
        $message = 'Your admin account is ready.';
        $detail = sprintf(
            'Username: %s — Sign in at the login page, then delete includes/create_admin.php from your server immediately.',
            CREATE_ADMIN_USERNAME
        );
    } catch (PDOException $e) {
        error_log('create_admin.php: ' . $e->getMessage());
        $status = 'error';
        $heading = 'Setup failed';
        $message = 'Could not create the admin user. Check that the database is configured and setup.sql has been run.';
        $detail = 'See the server error log for details.';
    }
}

$page_title = 'Admin setup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="page-create-admin">
    <main class="create-admin">
        <div class="create-admin__panel card create-admin__panel--<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
            <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="create-admin__message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($detail !== ''): ?>
                <p class="create-admin__detail text-muted"><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <?php if ($status === 'success'): ?>
                <p><a class="btn btn--primary" href="<?= htmlspecialchars(site_url('login.php'), ENT_QUOTES, 'UTF-8') ?>">Go to sign in</a></p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
