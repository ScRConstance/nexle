<?php
error_reporting(E_ALL);

date_default_timezone_set('Asia/Ho_Chi_Minh');

$datetimeFormat = 'Y-m-d H:i:s';

$key = "token_key";
$issuedAt = time();
$expirationTime = $issuedAt + (60 * 60); // valid for 1 hour
$issuer = "http://localhost/app/nexle/";

$rfkey = "refresh_token_key";
$rfissuedAt = time();
$rfexpirationTime = $rfissuedAt + (43200 * 60); // valid for 30 days
$rfissuer = "http://localhost/app/nexle/";
?>