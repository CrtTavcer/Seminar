<?php
$DEBUG = true;  // Enable detailed error messages (during testing)

include("orodja.php"); // Include 'tools'
include("vzdevek&ID.php");

$zbirka = dbConnect(); // Establish connection to the database

header('Content-Type: application/json'); // Set MIME type for JSON response

// Handle API requests based on HTTP method
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
        pridobi_vrste();
        break;
    default:
        http_response_code(405); // Method Not Allowed for other HTTP methods
        break;
}

mysqli_close($zbirka); // Close database connection


// API to fetch options for vrsta
function pridobi_vrste() {
    global $zbirka;

    $poizvedba = "SELECT ID_vrsta, vrsta FROM vrsta"; 
    $rezultat = mysqli_query($zbirka, $poizvedba);

    if ($rezultat) {
        $vrste = mysqli_fetch_all($rezultat, MYSQLI_ASSOC);
        http_response_code(200); // OK
        echo json_encode($vrste);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("message" => "Error fetching vrste"));
    }
}
?>


