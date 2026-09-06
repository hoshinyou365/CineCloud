<?php
require __DIR__ . '/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT movie_id, title, genre, duration_minutes, ticket_price, image_url
                             FROM movies ORDER BY movie_id ASC");
        echo json_encode(['success'=>true,'movies'=>$stmt->fetchAll()]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed.']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Unable to load movies.']);
}
