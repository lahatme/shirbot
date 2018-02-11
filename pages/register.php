<?php

include '../classes/user.php';
$firstname = $_POST["name"];
$lastname = $_POST["lastname"];
$username = $_POST["username2"];
$pass = $_POST["password2"];
$gender = $_POST["gender"];

$host="127.0.0.1";
$port=3306;
$socket="";
$user="root";
$password="1234";
$dbname="chat_try";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
	or die ('Could not connect to the database server' . mysqli_connect_error());
$conn->set_charset("utf8");
//$con->close();

$sql = "SELECT * FROM users WHERE _username = '$username'";
$result = $conn->query($sql);
if ($result->num_rows > 0) echo "known";
else
{
    $sql = "INSERT INTO users (_name, _last_name, _username, _password, _gender) VALUES ('$firstname', '$lastname', '$username','$pass','$gender')";
    if ($conn->query($sql) === TRUE) {
      $user = new User();
      $sql = "SELECT * FROM users WHERE _username = '$username'";
      $result = $conn->query($sql);
      while($row = $result->fetch_assoc()) {
            $user->SetName($row["_name"]);
            $user->SetID($row["ID"]);
            $user->SetGender($row["_gender"]);
            session_start();
            $_SESSION["user"] = serialize($user);
            echo "success";
            }
    } else {
          echo "Error";
    }
}


$conn->close();
 ?>
