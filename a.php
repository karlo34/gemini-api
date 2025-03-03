<?php
ob_start();
$name = "";
$email = "";
$conn;
$email2;
$password2;
$confirmPassword;
$username2;

    // IN CASE COOKIE INSISTS IT WILL GO TO MAIN PAGE WITHOUT LOGING IN

if (isset($_COOKIE["email"]) && $_COOKIE["lozinka"] && $_COOKIE["username"]) {
    header("Location: ./home.php");

}

// BASIC CONNECTION TO THE XAMPP (MY ADMIN) DATABASE
function Connect()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "zavrsni";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //login
    $name = htmlspecialchars($_POST['password']);
    $email = htmlspecialchars($_POST['email']);
    //sign up


    echo "<h2>Submitted Data:</h2>";
    echo "<p>Name: $name</p>";
    echo "<p>Email: $email</p>";
}

$phpVariable = [];

if (isset($_GET['json']) && $_GET['json'] == 'true') {
    $conn = Connect();
    $sql = "SELECT username, email, password AS sifra FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $phpVariable[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['Users' => $phpVariable]);
    exit();
}

$conn = Connect();
$sql = "SELECT username, email, password AS sifra FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $phpVariable[] = $row;
    }
}

include('./login1.html');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usernames</title>
</head>

<body>
    <h2>Usernames from Database:</h2>
    <?php

    // Assuming that you want to re-fetch usernames in this section.
    // Alternatively, you can retain the usernames already in $phpVariable.
    
    // Display only usernames
    
    if (!empty($email) && !empty($name)) {
        $postoji = false;
        $hashedPassword0 = hash('sha256', $name);
        // Check if the email and password match any user
        foreach ($phpVariable as $user) {
            // echo $hashedPassword0;
            if ($email == $user['email'] && $hashedPassword0 == $user['sifra']) {
                $postoji = true;
                $da = $user['username'];
                break;
            }
        }

        if ($postoji) {
            echo "POSTOJI";
            
            $conn = Connect();
            $sql = "SELECT username FROM users WHERE email = :email";
            $result = $conn->query($sql);
            setcookie("email", $email, time() + 1000, "/");
            setcookie("lozinka", $hashedPassword0, time() + 1000, "/");
            setcookie("username", $user['username'], time() + 1000, "/");
            header("Location: ./home.php");


            exit; // Stop script execution after redirect
        } else {
            echo "Login failed."; // Display error message if login fails
        }
    } else {
        echo "Fields are empty."; // Display message if fields are empty
    }


    if (isset($_POST['submit2'])) {
        echo "\t graaaaaaaaaaaaaa";
        $username2 = htmlspecialchars($_POST["username2"]);
        $email2 = htmlspecialchars($_POST["email2"]);
        $password2 = htmlspecialchars($_POST["password2"]);
        $confirmPassword = htmlspecialchars($_POST["confirmPassword"]);

        if (!empty($username2) && !empty($email2) && !empty($password2) && !empty($confirmPassword) && $password2 == $confirmPassword) {
            echo "</br>";
            echo "SVE JE UNSESENO";

            // Check connection
            if ($conn->connect_error) {
                echo "ERROR BAZA: " . $conn->connect_error;
                exit();
            }

            // Hash the password before storing it
            // $hashedPassword = password_hash($password2, PASSWORD_DEFAULT);
            $hashedPassword = hash('sha256', $password2);


            // SQL query to insert username, email, and password
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username2, $email2, $hashedPassword);

            // Execute the statement and check for success
            if ($stmt->execute()) {
                echo "<br>User registered successfully!";
            } else {
                echo "<br>Error: " . $stmt->error;
            }

            // Close the statement and connection
            $stmt->close();
            $conn->close();
        } else {
            echo "</br>";
            echo "NESTO NIJE UNSESENO";
            echo "</br>";
            echo $email2 . " " . $password2 . " " . $confirmPassword . " " . $username2 . " ";
        }
    }

    ?>
</body>

</html>