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

	case 'PUT':
		if (!empty($_GET["vzdevek"])){
			posodobi_igralca($_GET["vzdevek"]);
		}
		else{
			pripravi_odgovor_napaka("nepravilen PUT zahtevek", 111);
			http_response_code(400); //bad reqest
		}
		break;

	case 'DELETE':
		if (!empty($_GET["vzdevek"])){
			izbrisi_igralca($_GET["vzdevek"]);
		}
		else{
			http_response_code(400); //bad reqest
		}
		break;

		
	
	// ******* Dopolnite še z dodajanjem, posodabljanjem in brisanjem igralca



		
	default:
		http_response_code(405);		//Če naredimo zahtevo s katero koli drugo metodo je to 'Method Not Allowed'
		break;
}

mysqli_close($zbirka);					// Sprostimo povezavo z zbirko


// ----------- konec skripte, sledijo funkcije -----------

function pridobi_vse_igralce()
{
	global $zbirka;
	$odgovor=array();
	
	$poizvedba="SELECT vzdevek, ime, priimek, email FROM igralec";	
	
	$rezultat=mysqli_query($zbirka, $poizvedba);
	
	while($vrstica=mysqli_fetch_assoc($rezultat))
	{
		$odgovor[]=$vrstica;
	}
	
	http_response_code(200);		//OK
	echo json_encode($odgovor);
}

function pridobi_igralca($vzdevek)
{
	global $zbirka;
	$vzdevek=mysqli_escape_string($zbirka, $vzdevek);
	
	$poizvedba="SELECT vzdevek, ime, priimek, email FROM igralec WHERE vzdevek='$vzdevek'";
	
	$rezultat=mysqli_query($zbirka, $poizvedba);

	if(mysqli_num_rows($rezultat)>0)	//igralec obstaja
	{
		$odgovor=mysqli_fetch_assoc($rezultat);
		
		http_response_code(200);		//OK
		echo json_encode($odgovor);
	}
	else							// igralec ne obstaja
	{
		http_response_code(404);		//Not found
	}
}


// *********** Dopolnite še z ostalimi funkcijami

function dodaj_uporabnika(){
	
	global $zbirka, $DEBUG;

	$podatki = json_decode(file_get_contents("php://input"),true);
	if(isset($podatki["vzdevek"], $podatki["geslo"],$podatki["ime"], $podatki["priimek"], $podatki["email"]))
	{
		
		$vzdevek=mysqli_escape_string($zbirka, $podatki["vzdevek"]);
		$geslo=mysqli_escape_string($zbirka, $podatki["geslo"]); //!!!!!!!!!!!
		$geslo = password_hash($geslo, PASSWORD_DEFAULT);//zdej smo zaheširal geslo
		$ime=mysqli_escape_string($zbirka, $podatki["ime"]);
		$priimek=mysqli_escape_string($zbirka, $podatki["priimek"]);
		$email=mysqli_escape_string($zbirka, $podatki["email"]);
		
		if(!igralec_obstaja($vzdevek)){
			
			$poizvedba = "INSERT INTO igralec (vzdevek, geslo, ime, priimek, email) VALUES ('$vzdevek', '$geslo', '$ime', '$priimek', '$email')";
		
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
			pripravi_odgovor_napaka("Igralec ze obstaja", 123);
		}
		
	}
	
	else{
		http_response_code(400); //bad reqest
	}
}
	
function posodobi_igralca($vzdevek){

	global $zbirka, $DEBUG;
	
	$podatki = json_decode(file_get_contents("php://input"),true); //preverimo ali igralec obstaja

	if(igralec_obstaja($vzdevek)){
		
		if(isset($podatki["geslo"],$podatki["ime"], $podatki["priimek"], $podatki["email"])){
		
			$geslo=mysqli_escape_string($zbirka, $podatki["geslo"]); //!!!!!!!!!!!
			$geslo = password_hash($geslo, PASSWORD_DEFAULT);
			
			$ime=mysqli_escape_string($zbirka, $podatki["ime"]);
			$priimek=mysqli_escape_string($zbirka, $podatki["priimek"]);
			$email=mysqli_escape_string($zbirka, $podatki["email"]);
		
			$poizvedba = ("UPDATE igralec SET geslo='$geslo', ime='$ime', priimek='$priimek', email='$email' WHERE vzdevek='$vzdevek'");
			
			
			if(mysqli_query($zbirka, $poizvedba)){
				
				http_response_code(204); //OK (no content)
			
			}
			else{
				
				http_response_code(500); //internal server error (ni nujno vedno strežnik kriv)
				
				if($DEBUG){ //pozor - vracanje podatkov o napaki je varnostno tveganje!
					pripravi_odgovor_napaka(mysqli_error($zbirka), 666);
				}
			}
		}
		else{
			http_response_code(400); //bad reqest
		}
	}
	else{
		http_response_code(404);
		pripravi_odgovor_napaka("Igralec ne obstaja", 124);
	}
	
	
	
}

function izbrisi_igralca($vzdevek){
	
	global $zbirka, $DEBUG;
	
	//$vzdevek=mysqli_escape_string($zbirka, $podatki["vzdevek"]);
	
	if(igralec_obstaja($vzdevek)){
		
		$poizvedba = ("DELETE FROM igralec WHERE vzdevek='$vzdevek'");
		
		if(mysqli_query($zbirka, $poizvedba)){
			
			http_response_code(204); //OK (no content)
			
		}
		else{
			
			http_response_code(500); //internal server error (ni nujno vedno strežnik kriv)
			
			if($DEBUG){ //pozor - vracanje podatkov o napaki je varnostno tveganje!
				pripravi_odgovor_napaka(mysqli_error($zbirka), 666);
			}
		}
	}
	else{
		http_response_code(404);
		pripravi_odgovor_napaka("Igralec ne obstaja", 124);
	}
	
}



?>