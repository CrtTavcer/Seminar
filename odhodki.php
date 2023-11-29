<?php
$DEBUG = true;							// Priprava podrobnejših opisov napak (med testiranjem)

include("orodja.php"); 					// Vključitev 'orodij'

$zbirka = dbConnect();					// Pridobitev povezave s podatkovno zbirko

header('Content-Type: application/json');	// Nastavimo MIME tip vsebine odgovora

switch($_SERVER["REQUEST_METHOD"])		// Glede na HTTP metodo v zahtevi izberemo ustrezno dejanje nad virom
{
	case 'GET':
		if(!empty($_GET["vzdevek"]))
		{
			pridobi_igralca($_GET["vzdevek"]);		// Če odjemalec posreduje vzdevek, mu vrnemo podatke izbranega igralca
		}
		else
		{
			pridobi_vse_igralce();					// Če odjemalec ne posreduje vzdevka, mu vrnemo podatke vseh igralcev
		}
		break;
	
	default:
		http_response_code(405);		//Če naredimo zahtevo s katero koli drugo metodo je to 'Method Not Allowed'
		break;
}

mysqli_close($zbirka);					// Sprostimo povezavo z zbirko

