<?php
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit;
}
$data=json_decode(file_get_contents('php://input'),true) ?: [];

$name=trim($data['customerName'] ?? '');
$email=trim($data['customerEmail'] ?? '');
$movieId=(int)($data['movieId'] ?? 0);
$date=$data['showDate'] ?? '';
$time=trim($data['showTime'] ?? '');
$qty=(int)($data['quantity'] ?? 0);

if($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || $movieId<1 ||
   !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date) || $time==='' || $qty<1 || $qty>10){
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Please enter valid booking details.']); exit;
}

try {
    $pdo->beginTransaction();
    $stmt=$pdo->prepare("SELECT movie_id,title,ticket_price FROM movies WHERE movie_id=?");
    $stmt->execute([$movieId]); $movie=$stmt->fetch();
    if(!$movie){ $pdo->rollBack(); http_response_code(404); echo json_encode(['success'=>false,'message'=>'Movie not found.']); exit; }

    $total=round((float)$movie['ticket_price']*$qty,2);
    $insert=$pdo->prepare("INSERT INTO bookings
        (customer_name,customer_email,movie_id,show_date,show_time,ticket_quantity,total_amount,booking_status)
        VALUES(?,?,?,?,?,?,?,'Confirmed')");
    $insert->execute([$name,$email,$movieId,$date,$time,$qty,$total]);
    $bookingId=$pdo->lastInsertId();
    $pdo->commit();

    echo json_encode([
        'success'=>true,
        'message'=>'Booking confirmed successfully.',
        'booking'=>[
            'bookingId'=>(int)$bookingId,'customerName'=>$name,'customerEmail'=>$email,
            'movie'=>$movie['title'],'movieId'=>$movieId,'showDate'=>$date,'showTime'=>$time,
            'quantity'=>$qty,'total'=>number_format($total,2,'.','')
        ]
    ]);
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Unable to save booking.']);
}
