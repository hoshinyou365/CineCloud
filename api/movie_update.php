<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit;
}
$data=json_decode(file_get_contents('php://input'),true) ?: [];
$id=(int)($data['id'] ?? 0);
$title=trim($data['title'] ?? '');
$genre=trim($data['genre'] ?? '');
$duration=(int)($data['duration'] ?? 0);
$price=(float)($data['price'] ?? 0);
$image=trim($data['image'] ?? '');

if ($id<1 || $title==='' || $genre==='' || $duration<1 || $price<=0) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Please enter valid movie details.']); exit;
}
try {
    $stmt=$pdo->prepare("UPDATE movies SET title=?,genre=?,duration_minutes=?,ticket_price=?,image_url=?
                         WHERE movie_id=?");
    $stmt->execute([$title,$genre,$duration,$price,$image ?: null,$id]);
    if($stmt->rowCount()===0){
        $check=$pdo->prepare("SELECT movie_id FROM movies WHERE movie_id=?"); $check->execute([$id]);
        if(!$check->fetch()){ http_response_code(404); echo json_encode(['success'=>false,'message'=>'Movie not found.']); exit; }
    }
    echo json_encode(['success'=>true,'message'=>'Movie updated successfully.']);
} catch(Throwable $e) {
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Unable to update movie.']);
}
