<?php
$servername = "localhost";
$dbusername = "root";   // change if needed
$dbpassword = "";       // change if needed
$dbname = "parkside_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_GET['username']) and isset($_GET['password'])){
    $username = $_GET['username'];
    $password = $_GET['password'];
    $sql = "SELECT * FROM felhasznalok WHERE username = '$username' AND password = '$password'";
    // Execute the query
    if(!($result = mysqli_query($conn, $sql))){
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
    }else{
        $resultCheck = mysqli_num_rows($result);
        if($resultCheck > 0){
            while ($row = mysqli_fetch_assoc($result)){
                echo $row['name'];
                header('Location: http://localhost/bejelentkezo/parkside.html?name='.$row['name']);
            }
        }else{
            echo "nincs találat";
            //header('Location: http://localhost/bejelentkezo/parkside.html');
        }
    }

    // Close the database connection
    mysqli_close($conn);
    
    

}
?>