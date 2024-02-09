<?php
$DEBUG = true;  // Enable detailed error messages (during testing)

include("orodja.php"); // Include 'tools'
include("vzdevek&ID.php");

$zbirka = dbConnect(); // Establish connection to the database

header('Content-Type: application/json'); // Set MIME type for JSON response

// Handle API requests based on HTTP method
switch ($_SERVER["REQUEST_METHOD"]) {
    case 'GET':
        pridobi_namene();
        break;
    default:
        http_response_code(405); // Method Not Allowed for other HTTP methods
        break;
}

mysqli_close($zbirka); // Close database connection


// API to fetch options for namen
function pridobi_namene() {
    global $zbirka;

    $poizvedba = "SELECT ID_namen, kategorija FROM namen";
    $rezultat = mysqli_query($zbirka, $poizvedba);

    if ($rezultat) {
        $nameni = mysqli_fetch_all($rezultat, MYSQLI_ASSOC);
        http_response_code(200); // OK
        echo json_encode($nameni);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("message" => "Error fetching nameni"));
    }
}
?>
