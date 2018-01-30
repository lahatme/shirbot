<?php

include '../classes/user.php';

$host="127.0.0.1";
$port=3306;
$socket="";
$user="root";
$password="1234";
$dbname="chat_try";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
	or die ('Could not connect to the database server' . mysqli_connect_error());

//$con->close();


$username = /*"omergafla@gmail.com";//*/$_POST["username"];
$password = /*"oregon";//*/$_POST["password"];



  $sql = "SELECT * FROM users WHERE _username = '$username' AND _password = '$password'";
  $result = $conn->query($sql);
  $user = new User();
  if ($result->num_rows == 1) {
      // output data of each row
      echo $result->num_rows;
      while($row = $result->fetch_assoc()) {
            $user->SetName($row["_name"]);
            $user->SetID($row["ID"]);
						$user->SetGender($row["_gender"]);
      }
    session_start();
    $_SESSION["user"] = serialize($user);

  } else {
      echo "0 results";
  }


 ?>
