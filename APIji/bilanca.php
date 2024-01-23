<?php
$DEBUG = true;							// Priprava podrobnejših opisov napak (med testiranjem)

include("orodja.php"); 					// Vključitev 'orodij'
include("vzdevek&ID.php");

$zbirka = dbConnect();					// Pridobitev povezave s podatkovno zbirko

header('Content-Type: application/json');	// Nastavimo MIME tip vsebine odgovora

switch($_SERVER["REQUEST_METHOD"])		// Glede na HTTP metodo v zahtevi izberemo ustrezno dejanje nad virom
{
	case 'GET':
        
		pridobi_bilanco_uporabnika();		// Če odjemalec posreduje vzdevek, mu vrnemo podatke bilanco uporabnika
		break;
        /*
		if(!empty($_GET["ID_uporabnika"]))
		{
			pridobi_bilanco_uporabnika($_GET["ID_uporabnika"]);		// Če odjemalec posreduje vzdevek, mu vrnemo podatke bilanco uporabnika
		}
		else
		{
            //pridobi_vse_bilance();                                  // Če odjemalec ne posreduje vzdevka, mu vrnemo podatke vseh bilanc
		}
		break;
        */
	default:
		http_response_code(405);
		break;
}

mysqli_close($zbirka);					// Sprostimo povezavo z zbirko

/**
 * Funkcija vrne podatke o bilanci uporabnika
 *
 * @param $ID ID uporabnika
 */ 

function pridobi_bilanco_uporabnika(){

    global $zbirka, $DEBUG, $ID;

    $ID=mysqli_escape_string($zbirka, $ID);

    $poizvedba="SELECT * FROM bilanca WHERE ID_uporabnika='$ID'";

    $rezultat=mysqli_query($zbirka, $poizvedba);

    if(mysqli_num_rows($rezultat) > 0)
    {
        $bilanca=mysqli_fetch_all($rezultat, MYSQLI_ASSOC);
        echo json_encode($bilanca);
    }
    else
    {
        if ($DEBUG){
            pripravi_odgovor_napaka("Uporabnik nima vnosev v bilanco", 111);
        }
        http_response_code(404); //not found
    }

}

function pridobi_vse_bilance(){

    global $zbirka, $DEBUG;

    $poizvedba="SELECT * FROM bilanca";

    $rezultat=mysqli_query($zbirka, $poizvedba);

    if(mysqli_num_rows($rezultat) > 0)
    {
        $bilance=mysqli_fetch_all($rezultat, MYSQLI_ASSOC);
        echo json_encode($bilance);
    }
    else
    {   
        if ($DEBUG) {
            pripravi_odgovor_napaka("Ni bilanc", 111);
        }
        http_response_code(404); //not found
    }

}