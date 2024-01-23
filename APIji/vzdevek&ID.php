<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}


// Include the Composer autoloader
require 'C:\xampp\htdocs\mojProjekt\zaledje\vendor\autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key; // Import the Key class
$DEBUG = false;

// Your secret key used during token generation
$secretKey = 'your_secret_key';
$headers = getallheaders(); //pridobi glavo http sporočila

if(isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization']; //pridobi vsebino ključa Authorization
    if ($DEBUG) echo $authHeader;
    $token = str_replace('Bearer ', '', $authHeader); //odstrani besedo Bearer iz ključa
    if ($DEBUG) echo $token;

    try {
        // Attempt to decode the token
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        // Access the token data
        $vzdevek = $decoded->vzdevek;
        $ID = $decoded->ID;
    } catch (Exception $e) {
        http_response_code(401); // Unauthorized
    }
} else {
    http_response_code(401); // Unauthorized
    exit;
}
?>
     