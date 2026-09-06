<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit;
}
$data=json_decode(file_get_contents('php://input'),true) ?: [];
$id=(int)($data['id'] ?? 0);
if($id<1){ http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid movie ID.']); exit; }

try {
    $stmt=$pdo->prepare("DELETE FROM movies WHERE movie_id=?");
    $stmt->execute([$id]);
    if($stmt->rowCount()===0){ http_response_code(404); echo json_encode(['success'=>false,'message'=>'Movie not found.']); exit; }
    echo json_encode(['success'=>true,'message'=>'Movie deleted successfully.']);
} catch(PDOException $e) {
    error_log($e->getMessage());
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'This movie cannot be deleted because it has existing bookings.']);
}
