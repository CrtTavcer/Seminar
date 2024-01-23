<?php
$DEBUG = true;							// Priprava podrobnejših opisov napak (med testiranjem)

include("orodja.php"); 					// Vključitev 'orodij'

$zbirka = dbConnect();					// Pridobitev povezave s podatkovno zbirko

header('Content-Type: application/json');	// Nastavimo MIME tip vsebine odgovora

switch($_SERVER["REQUEST_METHOD"])		// Glede na HTTP metodo v zahtevi izberemo ustrezno dejanje nad virom
{

	case 'POST':
	
		dodaj_uporabnika();
		
		break;
		/*
			//primer podatkov, ki jih mora odjemalec poslati v zahtevi
			{
			"vzdevek": "exampleVzdevek",
			"geslo": "examplePassword",
			"ime": "exampleName",
			"priimek": "exampleSurname",
			"email": "example@email.com"
			}
		*/

	default:
		http_response_code(405);		//Če naredimo zahtevo s katero koli drugo metodo je to 'Method Not Allowed'
		break;
}

mysqli_close($zbirka);					// Sprostimo povezavo z zbirko



function dodaj_uporabnika(){	
	global $zbirka, $DEBUG;
	$podatki = json_decode(file_get_contents("php://input"),true);
	if(isset($podatki["vzdevek"], $podatki["geslo"],$podatki["ime"], $podatki["priimek"], $podatki["email"]))
	{
		
		$vzdevek=mysqli_escape_string($zbirka, $podatki["vzdevek"]);
		$geslo=mysqli_escape_string($zbirka, $podatki["geslo"]);
		$geslo = password_hash($geslo, PASSWORD_DEFAULT);  //hash gesla
		$ime=mysqli_escape_string($zbirka, $podatki["ime"]);
		$priimek=mysqli_escape_string($zbirka, $podatki["priimek"]);
		$email=mysqli_escape_string($zbirka, $podatki["email"]);
		
		if(!uporabnik_obstaja($vzdevek)){			
			$poizvedba = "INSERT INTO uporabniki (vzdevek, geslo, ime, priimek, email) VALUES ('$vzdevek', '$geslo', '$ime', '$priimek', '$email')";		
			if(mysqli_query($zbirka, $poizvedba)){
				http_response_code(201);
				$odgovor = URL_vira($vzdevek);
				echo json_encode($odgovor);
			}
			else{
				http_response_code(500); //internal server error				
				if($DEBUG){ //pozor - vracanje podatkov o napaki je varnostno tveganje!
					pripravi_odgovor_napaka(mysqli_error($zbirka), 666);
				}			
			}	
		}
		else{
			http_response_code(409);
			pripravi_odgovor_napaka("uporabnik ze obstaja", 123);
		}		
	}
	else{
		http_response_code(400); //bad reqest
	}
}
	


?>