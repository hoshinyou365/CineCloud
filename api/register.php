<?php
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit;
}
$data=json_decode(file_get_contents('php://input'),true) ?: [];
$name=trim($data['name'] ?? '');
$email=trim($data['email'] ?? '');
$password=$data['password'] ?? '';

if(strlen($name)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<8){
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Name, valid email and password of at least 8 characters are required.']); exit;
}
try{
    $hash=password_hash($password,PASSWORD_DEFAULT);
    $stmt=$pdo->prepare("INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,'Customer')");
    $stmt->execute([$name,$email,$hash]);
    echo json_encode(['success'=>true,'message'=>'Account created successfully.']);
}catch(PDOException $e){
    if($e->getCode()==='23000'){
        http_response_code(409); echo json_encode(['success'=>false,'message'=>'This email is already registered.']); exit;
    }
    error_log($e->getMessage()); http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Unable to create account.']);
}
