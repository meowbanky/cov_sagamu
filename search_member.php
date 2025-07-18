<?php

// Database connection
$db = new mysqli('localhost', 'emmaggic_root', 'Oluwaseyi', 'emmaggic_cofv');

$suggestions = []; // Initialize the array for storing suggestions

if ($db->connect_error) {
    echo 'ERROR: Could not connect to the database.';
} else {
    // Check if a query string has been posted
    if (isset($_POST['term'])) {

        $queryString = $db->real_escape_string($_POST['term']); // Sanitize input

        // Query to fetch matching results
        $query = $db->query("SELECT memberid, Fname, Mname, Lname, MobilePhone FROM tbl_personalinfo WHERE patientid LIKE '%$queryString%' OR Fname LIKE '%$queryString%' OR Mname LIKE '%$queryString%' OR Lname LIKE '%$queryString%' OR MobilePhone LIKE '%$queryString%' LIMIT 5");

        if ($query) {
            // Fetching results and preparing the JSON response
            while ($result = $query->fetch_object()) {
                $suggestions[] = [
                    'label' => $result->Lname . ', ' . $result->Fname .  ' - ' . $result->memberid.' - ' . $result->MobilePhone, // The text to display in the autocomplete dropdown
                    'value' => $result->memberid, // The value to be put in the textbox when this entry is selected
                    'name' => $result->Lname . ', ' . $result->Fname,
                ];
            }
        } else {
            // Handle query failure
            echo json_encode(['error' => 'There was a problem with the query.']);
            exit;
        }
    }
}

echo json_encode($suggestions); // Return the JSON-encoded array of suggestions

?>
