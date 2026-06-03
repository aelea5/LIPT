<?php
/**
 * Session-based authentication for Lunch in the Park.
 */

require_once __DIR__ . '/site.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('AUTH_SESSION_KEY', 'litp_user');

/**
 * @return array{id: int, username: string, email: string, role: string}|null
 */
function auth_user(): ?array
{
    $user = $_SESSION[AUTH_SESSION_KEY] ?? null;

    return is_array($user) ? $user : null;
}

function auth_is_logged_in(): bool
{
    return auth_user() !== null;
}

function auth_login_url(?string $redirect = null): string
{
    $url = site_url('login.php');
    if ($redirect !== null && $redirect !== '') {
        $url .= '?redirect=' . urlencode($redirect);
    }

    return $url;
}

function auth_require_login(): void
{
    if (auth_is_logged_in()) {
        return;
    }

    $redirect = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: ' . auth_login_url($redirect));
    exit;
}

function auth_require_admin(): void
{
    auth_require_login();

    $user = auth_user();
    if (($user['role'] ?? '') === 'admin') {
        return;
    }

    header('Location: ' . site_url('dashboard/nonprofit.php'));
    exit;
}

function auth_dashboard_url_for_role(string $role): string
{
    return $role === 'admin'
        ? site_url('dashboard/index.php')
        : site_url('dashboard/nonprofit.php');
}

/**
 * Validate credentials and start a session on success.
 *
 * @return 'admin'|'nonprofit'|null Role on success, null on failure.
 */
function auth_attempt_login(string $username, string $password): ?string
{
    require_once __DIR__ . '/db.php';

    $username = trim($username);
    if ($username === '' || $password === '') {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id, username, email, password, role FROM users WHERE username = ? LIMIT 1'
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password'])) {
        return null;
    }

    $role = $row['role'];
    if ($role !== 'admin' && $role !== 'nonprofit') {
        return null;
    }

    session_regenerate_id(true);

    $_SESSION[AUTH_SESSION_KEY] = [
        'id'       => (int) $row['id'],
        'username' => $row['username'],
        'email'    => $row['email'],
        'role'     => $role,
    ];

    return $role;
}

function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}
