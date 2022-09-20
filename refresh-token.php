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

$user = $_SESSION['user'];
$jwtoken = new Token($db);

$data = json_decode(file_get_contents("php://input"));

$jwtoken->refresh_token = $data->jwt;

$token_exists = $jwtoken->tokenExists();

if ($token_exists) {
    $jwtoken->deleteOldToken();

    $token = array(
        "iat" => $issuedAt,
        "exp" => $expirationTime,
        "iss" => $issuer,
        "data" => array(
            "id" => $user['id'],
            "first_name" => $user['first_name'],
            "last_name" => $user['last_name'],
            "email" => $user['email']
        )
    );
    $rftoken = array(
        "iat" => $rfissuedAt,
        "exp" => $rfexpirationTime,
        "iss" => $rfissuer,
        "data" => array(
            "id" => $user['id'],
            "first_name" => $user['first_name'],
            "last_name" => $user['last_name'],
            "email" => $user['email']
        )
    );
    $jwt = JWT::encode($token, $key, "HS256");
    $rfjwt = JWT::encode($rftoken, $rfkey, "HS256");

    $jwtoken->user_id = $user['user_id'];
    $jwtoken->refresh_token = $rfjwt;
    $jwtoken->expires_in = date($datetimeFormat, $rfexpirationTime);
    $jwtoken->updated_at = date($datetimeFormat, $rfissuedAt);
    $jwtoken->created_at = date($datetimeFormat, $rfissuedAt);
    $jwtoken->create();

    http_response_code(200);
    echo json_encode(
        array(
            "message" => "Refresh Token successful.",
            "token" => $jwt,
            "refresh_token" => $rfjwt
        )
    );
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Token doesnt exist."));
}
?>