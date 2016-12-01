<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../includes/functions.php';

$slimConfig = array();
@include '../includes/config.php';

$app = new \Slim\App(["settings" => $slimConfig]);
$container = $app->getContainer();

$app->get('/v1/things', function (Request $request, Response $response) {
  // lists all the things
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "Here are some things",
    "things" => [1,2,3,4,5],
    // "token" => $request->getQueryParam('token')
  ));
});
$app->post('/v1/things', function (Request $request, Response $response) {
  // creates a thing, returns the new thing
  $user = getUserFromToken($request->getAttribute('token'));
  if(!$user){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You need to be logged in to do this",
    ));
  }
  $data = $request->getParsedBody();
  $newId = 6;
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "You created this thing",
    "details" => ["id" => $newId, "user" => $user['username']] + (is_array($data)?$data:array())
  ));
});
$app->get('/v1/things/{id}', function (Request $request, Response $response) {
  // returns a certain thing
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "This is the thing",
    "details" => ["id" => $request->getAttribute('id')]
  ));
});
$app->patch('/v1/things/{id}', function (Request $request, Response $response) {
  // updates specific details on a thing, returns the new thing
  $user = getUserFromToken($request->getAttribute('token'));
  if(!$user){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You need to be logged in to do this",
    ));
  }
  if(!in_array('editor',$user['roles'])){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You don't have permission to do that",
    ));
  }
  $data = $request->getParsedBody();
  $data['id'] = $request->getAttribute('id');
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "You updated some fields on this thing",
    "details" => ["id" => $request->getAttribute('id')] + (is_array($data)?$data:array())
  ));
});
$app->put('/v1/things/{id}', function (Request $request, Response $response) {
  // replaces an entire thing, returns the new thing
  $user = getUserFromToken($request->getAttribute('token'));
  if(!$user){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You need to be logged in to do this",
    ));
  }
  if(!in_array('admin',$user['roles'])){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You don't have permission to do that",
    ));
  }
  $data = $request->getParsedBody();
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "You replaced this thing",
    "details" => ["id" => $request->getAttribute('id')] + (is_array($data)?$data:array())
  ));
});

$app->post('/v1/users', function (Request $request, Response $response) {
  // creates a new user, returns the details (eg. user registration)
  $data = $request->getParsedBody();
  $newId = 3;
  if(isset($data['password'])){
    unset($data['password']); // you wanna update the password, but not return it
  }
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "You created this user",
    "details" => $data
  ));
});
$app->get('/v1/users/{username}', function (Request $request, Response $response) {
  // returns a user's details
  $user = getUserFromToken($request->getAttribute('token'));
  if(!$user){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You need to be logged in to do this",
    ));
  }
  if($user['username'] != $request->getAttribute('username')){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You can't look at other people's profiles for some reason",
    ));
  }
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "This is the user",
    "details" => ["username" => $request->getAttribute('username')] // don't return the password!
  ));
});
$app->patch('/v1/users/{username}', function (Request $request, Response $response) {
  // updates specific details on a user (eg. password), returns the user's details
  $user = getUserFromToken($request->getAttribute('token'));
  if(!$user){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You need to be logged in to do this",
    ));
  }
  if($user['username'] != $request->getAttribute('username') && !in_array('admin',$user['roles'])){
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "You don't have permission to do that",
    ));
  }

  $data = $request->getParsedBody();
  if(isset($data['password'])){
    unset($data['password']); // you wanna update the password, but not return it
  }
  return $response->withStatus(200)->withJson(array(
    "success" => true,
    "message" => "You updated some fields on this user",
    "details" => ["username" => $request->getAttribute('username')] + (is_array($data)?$data:array())
  ));
});

$app->get('/v1/authenticate', function (Request $request, Response $response) {
  // logs in using HTTP Basic Authorisation and returns an auth token
  $token = false;
  if(preg_match('/basic\s(.*?)(\s|\,|$)/i',$request->getHeaderLine('Authorization'),$tokenMatch)){
    if(isset($tokenMatch[1])){
      $credentials = explode(":",base64_decode($tokenMatch[1]));
      if(count($credentials)==2){
        $token = getTokenFromLogin($credentials[0],$credentials[1]); // TODO: make sure there are no invalid characters saved (specifically :)
      }
    }
  }
  if($token){
    return $response->withStatus(200)->withJson(array(
      "success" => true,
      "message" => "You logged in",
      "token" => $token
    ));
  }else{
    return $response->withStatus(403)->withJson(array(
      "success" => false,
      "message" => "Your login details are invlid"
    ));
  }
});

$app->add(function ($request, $response, $next) {
  $token = false;
  if(preg_match('/bearer\s(.*?)(\s|\,|$)/i',$request->getHeaderLine('Authorization'),$tokenMatch)){
    if(isset($tokenMatch[1])){
      $token = $tokenMatch[1];
    }
  }
  return $next($request->withAttribute('token',$token), $response);
});

$app->run();
