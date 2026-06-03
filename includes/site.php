<?php
/**
 * Site-wide configuration for Lunch in the Park.
 * SITE_BASE is auto-detected from the filesystem; override in .env if needed.
 */

require_once __DIR__ . '/env.php';

define('SITE_NAME', 'Lunch in the Park');
define('SITE_TAGLINE', '$8 community lunches in the park — Thursdays, June through August.');

/**
 * Web path prefix for this install (e.g. "/" or "/lunch-in-the-park/").
 */
function site_base_path(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $override = env('SITE_BASE');
    if ($override !== null && $override !== '') {
        $override = '/' . trim($override, '/') . '/';
        $cached = $override === '//' ? '/' : $override;

        return $cached;
    }

    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $appRoot = realpath(dirname(__DIR__));

    if ($docRoot && $appRoot && str_starts_with($appRoot, $docRoot)) {
        $relative = substr($appRoot, strlen($docRoot));
        $relative = str_replace('\\', '/', $relative);
        $cached = $relative === '' ? '/' : $relative . '/';

        return $cached;
    }

    $cached = '/';

    return $cached;
}

if (!defined('SITE_BASE')) {
    define('SITE_BASE', site_base_path());
}

function site_url(string $path = ''): string
{
    $base = rtrim(SITE_BASE, '/');
    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return site_url('assets/' . ltrim($path, '/'));
}

function is_current_page(string $script): bool
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === $script;
}
