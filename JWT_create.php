<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
//header("Access-Control-Allow-Headers: Content-Type");
$DEBUG = false;							// Priprava podrobnejših opisov napak (med testiranjem)


$zbirka = dbConnect();		
header('Content-Type: application/json');	// Nastavimo MIME tip vsebine odgovora

// Include the Composer autoloader
require 'vendor\autoload.php';

use \Firebase\JWT\JWT;

// Your secret key for signing the token (keep this secret and secure)
$secretKey = 'your_secret_key';

// Assuming $username and $password are obtained from user input
$username = $_POST['upime'];
$password = $_POST['geslo'];
if ($DEBUG) {
	echo "Uporabniško ime: $username<br/>Geslo: $password<br/>";
}

$isValidUser = uporabnik_obstaja($username, $password);

if ($isValidUser) {
	//echo "Welcome, $username!";
    $tokenId = base64_encode(random_bytes(32)); // Unique identifier for the token

    // Create token payload as an array
    $payload = [
        'iat' => time(), // Issued at: time when the token was generated
        'exp' => time() + (60 * 60 * 5), // Expiration time (1 hour from now)
        'vzdevek' => $username,
		'ID' => ID_uporabnika($username)
    ];

	mysqli_close($zbirka);
	
	if ($DEBUG) echo $payload['ID'];
    // Generate JWT
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
	//echo $jwt;
    // Return the token as JSON
    echo json_encode(['token' => $jwt]);
	//http_response_code(200); // OK
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'Authentication failed']);
}

function uporabnik_obstaja($vzdevek, $geslo)
{	
	global $zbirka;

	$vzdevek=mysqli_escape_string($zbirka, $vzdevek);
	$geslo=mysqli_escape_string($zbirka, $geslo);
	
	$poizvedba="SELECT * FROM uporabniki WHERE vzdevek='$vzdevek'";
	//echo "$vzdevek, $geslo";
	if(mysqli_num_rows(mysqli_query($zbirka, $poizvedba)) > 0) //če obstaja uporabnik z podanim vzdevkom in geslom
	{	
		//echo "Uporabnik obstaja";
		$rezultat = mysqli_query($zbirka, $poizvedba);
		$vrstica=mysqli_fetch_assoc($rezultat);
		$shranjenoGeslo = $vrstica['geslo'];
		//echo $shranjenoGeslo;
		if (password_verify($geslo, $shranjenoGeslo)) { //preverimo, če se gesli ujemata
			return true;
		}
	else
	{
		return false;
	}
}
}

if ($DEBUG) {
	echo "skripta uspešno povezana";
}


function dbConnect()
{
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "moj_projekt";

	// Ustvarimo povezavo do podatkovne zbirke
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	mysqli_set_charset($conn,"utf8");
	
	// Preverimo uspeh povezave
	if (mysqli_connect_errno())
	{
		printf("Povezovanje s podatkovnim strežnikom ni uspelo: %s\n", mysqli_connect_error());
		exit();
	} 	
	return $conn;
}

function ID_uporabnika($vzdevek) //funkcija vrne ID uporabnika, ki ima podan vzdevek
{
	global $zbirka;
	$vzdevek=mysqli_escape_string($zbirka, $vzdevek);
	
	$poizvedba="SELECT ID FROM uporabniki WHERE vzdevek='$vzdevek'";
	
	$rezultat=mysqli_query($zbirka, $poizvedba);
	$vrstica=mysqli_fetch_assoc($rezultat);
	
	return $vrstica['ID'];
}	
?>
