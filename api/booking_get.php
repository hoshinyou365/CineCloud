<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
requireLogin();

$id=(int)($_GET['id'] ?? 0);
if($id<1){ http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid booking ID.']); exit; }

try {
    $stmt=$pdo->prepare("SELECT b.booking_id,b.customer_name,b.customer_email,b.movie_id,
        m.title AS movie,b.show_date,b.show_time,b.ticket_quantity,b.total_amount,
        b.booking_status,b.created_at
        FROM bookings b JOIN movies m ON b.movie_id=m.movie_id WHERE b.booking_id=?");
    $stmt->execute([$id]); $b=$stmt->fetch();
    if(!$b){ http_response_code(404); echo json_encode(['success'=>false,'message'=>'Booking not found.']); exit; }
    echo json_encode(['success'=>true,'booking'=>[
        'bookingId'=>(int)$b['booking_id'],'customerName'=>$b['customer_name'],
        'customerEmail'=>$b['customer_email'],'movie'=>$b['movie'],'movieId'=>(int)$b['movie_id'],
        'showDate'=>$b['show_date'],'showTime'=>$b['show_time'],'quantity'=>(int)$b['ticket_quantity'],
        'total'=>number_format((float)$b['total_amount'],2,'.',''),'status'=>$b['booking_status']
    ]]);
} catch(Throwable $e) {
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Unable to load booking.']);
}
