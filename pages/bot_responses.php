<?php

function to_js_array($arr)
{
  $output = "";
  foreach ($arr as $value) {
      $output = $output.$value."&";
  }
$output = substr($output,0,-1);
return $output;
}

if(isset($_POST["intent0"])) {
$host="127.0.0.1";
$port=3306;
$socket="";
$user="root";
$password="1234";
$dbname="chat_try";

$intent = $_POST["intent0"];
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
or die ('Could not connect to the database server' . mysqli_connect_error());
$conn->set_charset("utf8");
$sql = "SELECT * FROM chat_try.bot_outputs WHERE intent = '$intent'";
$data_array = array("");
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        array_push($data_array, $row['output_text']);
    }
} else {
  $sql = "SELECT * FROM chat_try.bot_outputs WHERE intent = 'default'";
  $result = $conn->query($sql);
  while($row = $result->fetch_assoc()) {
      array_push($data_array, $row['output_text']);
}
}
$conn->close();

echo to_js_array($data_array);
}
 ?>
