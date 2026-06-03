<?php
/**
 * Legacy URL — redirect to the public login page.
 */
require_once dirname(__DIR__) . '/includes/site.php';

$redirect = $_GET['redirect'] ?? '';
$target = site_url('login.php');
if ($redirect !== '') {
    $target .= '?redirect=' . urlencode($redirect);
}

header('Location: ' . $target, true, 301);
exit;
