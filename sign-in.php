<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config/database.php';
require_once 'config/core.php';
require_once 'object/user.php';
require_once 'object/token.php';
require_once 'libs/php-jwt/src/BeforeValidException.php';
require_once 'libs/php-jwt/src/ExpiredException.php';
require_once 'libs/php-jwt/src/SignatureInvalidException.php';
require_once 'libs/php-jwt/src/JWT.php';

use Firebase\JWT\JWT;

session_start();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$jwtoken = new Token($db);

$data = json_decode(file_get_contents("php://input"));

$user->email = $data->email;
$email_exists = $user->emailExists();

if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array("message" => "Login failed. Email is invalid."));
} elseif (strlen($data->password) < 8 || strlen($data->password) > 20) {
    http_response_code(400);
    echo json_encode(array("message" => "Login failed. Password must be between 8-20 characters."));
} elseif ($email_exists && password_verify($data->password, $user->password)) {
    $token = array(
        "iat" => $issuedAt,
        "exp" => $expirationTime,
        "iss" => $issuer,
        "data" => array(
            "id" => $user->id,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email
        )
    );
    $rftoken = array(
        "iat" => $rfissuedAt,
        "exp" => $rfexpirationTime,
        "iss" => $rfissuer,
        "data" => array(
            "id" => $user->id,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email
        )
    );
    $jwt = JWT::encode($token, $key, "HS256");
    $rfjwt = JWT::encode($rftoken, $rfkey, "HS256");

    $jwtoken->user_id = $user->id;
    $jwtoken->refresh_token = $rfjwt;
    $jwtoken->expires_in = date($datetimeFormat, $rfexpirationTime);
    $jwtoken->updated_at = date($datetimeFormat, $rfissuedAt);
    $jwtoken->created_at = date($datetimeFormat, $rfissuedAt);
    $jwtoken->create();

    $_SESSION['user']['user_id'] = $user->id;
    $_SESSION['user']['email'] = $user->email;
    $_SESSION['user']['first_name'] = $user->first_name;
    $_SESSION['user']['last_name'] = $user->last_name;
    $_SESSION['user']['token'] = $jwt;
    $_SESSION['user']['refresh_token'] = $rfjwt;

    http_response_code(200);
    echo json_encode(
        array(
            "message" => "Login successful.",
            "user" => array(
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "email" => $user->email,
                "displayname" => $user->first_name . ' ' . $user->last_name
            ),
            "token" => $jwt,
            "refresh_token" => $rfjwt
        )
    );
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Login failed."));
}
?>