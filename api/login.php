<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit;
}
$data=json_decode(file_get_contents('php://input'),true) ?: [];
$email=trim($data['email'] ?? '');
$password=$data['password'] ?? '';

if(!filter_var($email,FILTER_VALIDATE_EMAIL) || $password===''){
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Please enter a valid email and password.']); exit;
}

try{
    $stmt=$pdo->prepare("SELECT user_id,full_name,email,password_hash,role FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]); $user=$stmt->fetch();
    if(!$user || !password_verify($password,$user['password_hash'])){
        // Demo database may contain the seeded classroom passwords.
        if(!$user || $user['password_hash'] !== $password){
            http_response_code(401); echo json_encode(['success'=>false,'message'=>'Invalid email or password.']); exit;
        }
    }
    session_regenerate_id(true);
    $_SESSION['user_id']=(int)$user['user_id'];
    $_SESSION['full_name']=$user['full_name'];
    $_SESSION['email']=$user['email'];
    $_SESSION['role']=$user['role'];
    echo json_encode(['success'=>true,'message'=>'Login successful.','user'=>[
        'id'=>(int)$user['user_id'],'name'=>$user['full_name'],'email'=>$user['email'],'role'=>$user['role']
    ]]);
}catch(Throwable $e){
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Unable to login.']);
}
