<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}



/**
 * Funkcija vzpostavi povezavo z zbirko podatkov na proceduralni način
 *
 * @return $conn objekt, ki predstavlja povezavo z izbrano podatkovno zbirko
 */
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

/**
 * Funkcija pripravi odgovor v obliki JSON v primeru napake
 *
 * @param $vsebina Znakovni niz, ki opisuje napako
 */
function pripravi_odgovor_napaka($vsebina)
{
	$odgovor=array(
		'status' => 0,
		'error_message'=>$vsebina
	);
	echo json_encode($odgovor);
}

/**
 * Funkcija preveri, če podan igralec obstaja v podatkovni zbirki
 *
 * @param $vzdevek Vzdevek igralca
 * @return true, če igralec obstaja, v nasprotnem primeru false
 */
function uporabnik_obstaja($vzdevek)
{	
	global $zbirka;
	$vzdevek=mysqli_escape_string($zbirka, $vzdevek);
	
	$poizvedba="SELECT * FROM uporabniki WHERE vzdevek='$vzdevek'";
	
	if(mysqli_num_rows(mysqli_query($zbirka, $poizvedba)) > 0)
	{
		return true;
	}
	else
	{
		return false;
	}	
}


function ID_uporabnika_obstaja($ID_uporabnika) {
    global $zbirka;
	$ID_uporabnika=mysqli_escape_string($zbirka, $ID_uporabnika);
	
	$poizvedba="SELECT * FROM uporabniki WHERE ID='$ID_uporabnika'";
	
	if(mysqli_num_rows(mysqli_query($zbirka, $poizvedba)) > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}




/**
 * Funkcija pripravi URL podanega vira
 *
 * @param $vir Ime vira
 * @return $url URL podanega vira
 */
function URL_vira($vir)
{
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
	{
		$url = "https"; 
	}
	else
	{
		$url = "http"; 
	}
	$url .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $vir;
	
	return $url; 
}
?>