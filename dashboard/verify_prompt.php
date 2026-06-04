<?php
/**
 * AJAX endpoint: mark nonprofit verification prompt as seen today.
 */
require_once dirname(__DIR__) . '/includes/site.php';
require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json');

if (!auth_is_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = auth_user();
if (($user['role'] ?? '') !== 'nonprofit') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

auth_mark_verify_prompt_today((int) $user['id']);

echo json_encode(['ok' => true]);
