<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "iwanderph_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "connected";

$travelerID = "10012";

$travelerQuery = "SELECT * FROM traveler WHERE TravelerID = '$travelerID'";
$travelerResult = mysqli_query($conn, $travelerQuery);

if ($travelerResult === false) {
    echo "Error: " . mysqli_error($conn);
} else {
    $traveler = mysqli_fetch_assoc($travelerResult);
    foreach ($traveler as $key => $value) {
        echo "$key: $value<br>";
    }
}
?>