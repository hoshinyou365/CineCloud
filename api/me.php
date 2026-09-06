<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if(isset($_SESSION['user_id'])){
 echo json_encode(['success'=>true,'loggedIn'=>true,'user'=>[
  'id'=>(int)$_SESSION['user_id'],'name'=>$_SESSION['full_name'],
  'email'=>$_SESSION['email'],'role'=>$_SESSION['role']
 ]]);
}else echo json_encode(['success'=>true,'loggedIn'=>false]);
