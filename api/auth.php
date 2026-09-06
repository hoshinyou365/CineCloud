<?php
session_start();

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Please log in first.']);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'Admin') {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Administrator access is required.']);
        exit;
    }
}
