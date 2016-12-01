<?php
use Hautelook\Phpass\PasswordHash;
$passwordHasher = new PasswordHash(8,false);

function hashPassword($plainText){
  global $passwordHasher;
  return $passwordHasher->HashPassword($plainText);
}
function checkPassword($password,$hash){
  global $passwordHasher;
  return $passwordHasher->CheckPassword($password, $hash);
}

function dummyUsers(){
  $users = array(
    ["username" => "rick", "password" => 'iH8shaN3*', "roles" => ["admin","editor"], "token" => "275BtEMZHmRP1XRXkf7mOZWaUHcFOP2/Trmk/LhyEU4"],
    ["username" => "carl", "password" => 'Jud!th2011', "roles" => ["editor"], "token" => "+BM4D2OshsgUp+XEcpehD930wCRtvIqOReJA3AINmTs"],
    ["username" => "lori", "password" => 'R1P-me', "roles" => [], "token" => "yfto3jkuJeZCwxlHWOVbvJYakHzBDCMMwvxJswTkWJ4"],
  );
  foreach($users as $i => $user){
    $users[$i]['password'] = hashPassword($user['password']); // just for the demo - in real life you'd hash when you save the password into a database
  }
  return $users;
}

function getUserFromToken($token){
  // Change this to actually look up a user
  $users = dummyUsers();
  $key = array_search($token, array_column($users, 'token'));
  if($key!==false){
    return $users[$key];
  }else{
    return false;
  }
}

function getTokenFromLogin($username,$password){
  // Change this to actually look up a user
  $users = dummyUsers();
  $key = array_search($username, array_column($users, 'username'));
  if($key!==false){
    return checkPassword($password, $users[$key]['password'])?$users[$key]['token']:false;
  }else{
    return false;
  }
}
