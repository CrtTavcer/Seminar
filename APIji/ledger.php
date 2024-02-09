<?php
$DEBUG = true;							// Priprava podrobnejših opisov napak (med testiranjem)

include("orodja.php"); 					// Vključitev 'orodij'
include("vzdevek&ID.php");

$zbirka = dbConnect();					// Pridobitev povezave s podatkovno zbirko

header('Content-Type: application/json');	// Nastavimo MIME tip vsebine odgovora

switch($_SERVER["REQUEST_METHOD"])		// Glede na HTTP metodo v zahtevi izberemo ustrezno dejanje nad virom
{
    case 'GET':

        pridobi_vnose_uporabnika(); // Default case for getting all entries
    
        break;

    case 'POST':
        dodaj_vnos();
        break;

    //case PUT se izogibamo, zaradi triggers v bazi (ni še/ne bo triggerja za posodabljanje bilance)

    case 'DELETE':  
        if (!empty($_GET["vzdevek"])){
            izbris_vnosa($_GET["vzdevek"]);
        }
        else{
            http_response_code(400); //bad reqest
        }
        break;

	default:
		http_response_code(405);		//Če naredimo zahtevo s katero koli drugo metodo je to 'Method Not Allowed'
		break;
}

mysqli_close($zbirka);					// Sprostimo povezavo z zbirko

function pridobi_vnose_uporabnika(){
    global $zbirka, $DEBUG, $ID;

    $ID = mysqli_escape_string($zbirka, $ID);
  
    $poizvedba = "SELECT vsota, vrsta.vrsta, namen.kategorija, datum
    FROM ledger
    INNER JOIN vrsta ON ledger.ID_vrsta = vrsta.ID_vrsta
    INNER JOIN namen ON ledger.ID_namen = namen.ID_namen
    WHERE ID_uporabnika='$ID'
    ORDER BY ID_ledger DESC";

    $rezultat = mysqli_query($zbirka, $poizvedba);

    if(mysqli_num_rows($rezultat) > 0)
    {
        $vnosi = array();
        while($vnos = mysqli_fetch_assoc($rezultat))
        {
            $vnosi[] = $vnos;
        }
        echo json_encode($vnosi);
    }
    else
    {
        if ($DEBUG){
            pripravi_odgovor_napaka("Uporabnik nima vnosev", 111);
        }
        http_response_code(404); //not found
    }
}

function pridobi_vse_vnose(){
    global $zbirka, $DEBUG;

    $poizvedba="SELECT * FROM ledger";

    $rezultat=mysqli_query($zbirka, $poizvedba);

    if(mysqli_num_rows($rezultat) > 0)
    {
        $vnosi=array();
        while($vnos=mysqli_fetch_assoc($rezultat))
        {
            $vnosi[]=$vnos;
        }
        echo json_encode($vnosi);
    }
    else
    {
        if ($DEBUG){
            pripravi_odgovor_napaka("Ni vnosov", 111);
        }
        http_response_code(404); //not found
    }
}

function dodaj_vnos(){

    global $zbirka, $ID, $DEBUG;

    $podatki = json_decode(file_get_contents("php://input"),true);
    if(isset($podatki["vsota"], $podatki["ID_vrsta"], $podatki["ID_namen"]))
    {
        $vsota = mysqli_escape_string($zbirka, $podatki["vsota"]);
        $namen = mysqli_escape_string($zbirka, $podatki["ID_namen"]);
        $vrsta = mysqli_escape_string($zbirka, $podatki["ID_vrsta"]);
    
        // Get user ID from JWT
        $ID_uporabnika = $ID; 
        $poizvedba = "INSERT INTO ledger (vsota, ID_uporabnika, ID_vrsta, ID_namen) VALUES ('$vsota', '$ID_uporabnika', '$vrsta', '$namen')";
        if(mysqli_query($zbirka, $poizvedba))
        {
            http_response_code(201); //created
        }
        else
        {
            http_response_code(400); //bad request
            echo json_encode("Napaka pri dodajanju podatkov");
        }
    }
    else
    {
        if ($DEBUG){
            pripravi_odgovor_napaka("Napaka pri dodajanju podatkov", 111);
        }
        http_response_code(400); //bad request
        echo json_encode($podatki);
    }
}




function izbris_vnosa($ID_ledger){
    global $zbirka, $DEBUG;

    $vzdevek=mysqli_escape_string($zbirka, $vzdevek);

    $poizvedba="DELETE FROM ledger WHERE vzdevek='$vzdevek'";

    if(mysqli_query($zbirka, $poizvedba))
    {
        http_response_code(204); //no content
    }
    else
    {
        if ($DEBUG){
            pripravi_odgovor_napaka("Napaka", 111);
        }
        http_response_code(400); //bad request
    }
}

function izbris_vnosev_uporabnika($ID_uporabnika){
    global $zbirka, $DEBUG;

    $ID_uporabnika=mysqli_escape_string($zbirka, $ID_uporabnika);

    $poizvedba="DELETE FROM ledger WHERE ID_uporabnika='$ID_uporabnika'";

    if(mysqli_query($zbirka, $poizvedba))
    {
        http_response_code(204); //no content
    }
    else
    {
        if ($DEBUG){
            pripravi_odgovor_napaka("Napaka", 111);
        }
        http_response_code(400); //bad request
    }
}
