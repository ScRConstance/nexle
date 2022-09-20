<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config/database.php';
require_once 'config/core.php';
require_once 'object/user.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

$user->first_name = $data->first_name;
$user->last_name = $data->last_name;
$user->email = $data->email;
$user->password = $data->password;
$user->updated_at = date($datetimeFormat, $issuedAt);
$user->created_at = date($datetimeFormat, $issuedAt);

if (empty($data->first_name) || empty($data->last_name) || empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Data is empty."));
} elseif ($user->emailExists()) {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Email is exist."));
} elseif (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Email is invalid."));
} elseif (strlen($user->password) < 8 || strlen($user->password) > 20) {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Password must be between 8-20 characters."));
} else {
    $row = $user->create();
    if ($row) {
        http_response_code(200);
        echo json_encode(
            array(
                "message" => "User was created.",
                "user" => $row
            )
        );
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to create user."));
    }
}
?>