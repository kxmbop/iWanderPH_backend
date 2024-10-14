<?php
include 'encryption.php';

$encryption_result = '';
$decryption_result = '';
$traveler_decryption_result = '';

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "iwanderph_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['encrypt'])) {
        $traveler_id = $_POST['travelerid'];
        $username = $_POST['username'];
        $role_type = $_POST['roletype'];
        $key = $_POST['encryption_key'];
        
        if (!empty($traveler_id) && !empty($username) && !empty($role_type) && !empty($key)) {
            $text_to_encrypt = $traveler_id . " - " . $username . " - " . $role_type; 
            $encryption_result = encrypt($text_to_encrypt, $key);
        } else {
            $encryption_result = "Please provide Traveler ID, Username, Role Type, and a key.";
        }
    }

    if (isset($_POST['decrypt'])) {
        $encrypted_text = $_POST['text_to_decrypt'];
        $key = $_POST['decryption_key'];
        
        if (!empty($encrypted_text) && !empty($key)) {
            $decryption_result = decrypt($encrypted_text, $key);
            if ($decryption_result === false) {
                $decryption_result = "Decryption failed. Check the key or the input.";
            }
        } else {
            $decryption_result = "Please provide both the encrypted text and a key.";
        }
    }

    // Decrypt TravelerID from the database
    if (isset($_POST['decrypt_traveler'])) {
        $traveler_key = $_POST['traveler_key'];
        
        $sql = "SELECT TravelerUUID FROM traveler";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $traveler_decryption_result .= "<ul>";
            while ($row = $result->fetch_assoc()) {
                $encryptedID = $row['TravelerUUID'];
                $decryptedID = decrypt($encryptedID, $traveler_key);
                if ($decryptedID !== false) {
                    $traveler_decryption_result .= "<li>$decryptedID</li>"; 
                } else {
                    $traveler_decryption_result .= "<li>Decryption failed for ID: $encryptedID</li>";
                }
            }
            $traveler_decryption_result .= "</ul>";
        } else {
            $traveler_decryption_result = "No traveler IDs found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt / Decrypt Traveler UUID</title>
</head>
<body>
    <h1>Encrypt and Decrypt Traveler UUID (TravelerID - Username - RoleType)</h1>
    
    <!-- Form for Encryption -->
    <h2>Encrypt Traveler UUID</h2>
    <form method="post">
        <label for="travelerid">Traveler ID:</label><br>
        <input type="text" id="travelerid" name="travelerid" required><br><br>
        
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required><br><br>
        
        <label for="roletype">Role Type:</label><br>
        <input type="text" id="roletype" name="roletype" required><br><br>
        
        <label for="encryption_key">Encryption Key:</label><br>
        <input type="text" id="encryption_key" name="encryption_key" required><br><br>
        
        <input type="submit" name="encrypt" value="Encrypt">
    </form>

    <!-- Display encryption result -->
    <?php if (!empty($encryption_result)) { ?>
        <h3>Encrypted Traveler UUID:</h3>
        <p><?php echo $encryption_result; ?></p>
    <?php } ?>

    <!-- Form for Decryption -->
    <h2>Decrypt Traveler UUID</h2>
    <form method="post">
        <label for="text_to_decrypt">Encrypted Traveler UUID:</label><br>
        <input type="text" id="text_to_decrypt" name="text_to_decrypt" required><br><br>
        
        <label for="decryption_key">Decryption Key:</label><br>
        <input type="text" id="decryption_key" name="decryption_key" required><br><br>
        
        <input type="submit" name="decrypt" value="Decrypt">
    </form>

    <!-- Display decryption result -->
    <?php if (!empty($decryption_result)) { ?>
        <h3>Decrypted Traveler UUID (TravelerID - Username - RoleType):</h3>
        <p><?php echo $decryption_result; ?></p>
    <?php } ?>

    <!-- Form for Decrypting Traveler IDs from Database -->
    <h2>Decrypt Traveler IDs from Database</h2>
    <form method="post">
        <label for="traveler_key">Decryption Key:</label><br>
        <input type="text" id="traveler_key" name="traveler_key" required><br><br>
        
        <input type="submit" name="decrypt_traveler" value="Decrypt Traveler IDs">
    </form>

    <!-- Display traveler decryption result -->
    <?php if (!empty($traveler_decryption_result)) { ?>
        <h3>Decrypted Traveler IDs:</h3>
        <p><?php echo $traveler_decryption_result; ?></p>
    <?php } ?>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
