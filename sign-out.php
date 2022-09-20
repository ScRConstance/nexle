<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config/database.php';
require_once 'object/token.php';

session_start();

$database = new Database();
$db = $database->getConnection();

$user = $_SESSION['user'];
$jwtoken = new Token($db);

$jwtoken->user_id = $user['user_id'];
$delete_token = $jwtoken->deleteOldToken();

if ($delete_token) {
    unset($_SESSION['user']);

    http_response_code(200);
    echo json_encode(
        array(
            "message" => "Logout successful."
        )
    );
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Logout failed."));
}
?>