<?php

function to_js_array($arr)
{
  $output = "";
  foreach ($arr as $value) {
      $output = $output.$value.",";
  }
$output = substr($output,0,-1);
return $output;
}

$host="127.0.0.1";
$port=3306;
$socket="";
$user="root";
$password="1234";
$dbname="chat_try";



$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
	or die ('Could not connect to the database server' . mysqli_connect_error());


  $sql = "SELECT * FROM chat_try.topics";
  $result = $conn->query($sql);
  $topics_array = array("");
  if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
          array_push($topics_array, $row["topic"]);
      }
  } else {
      echo "0 results";
  }
  $topics = to_js_array($topics_array);
  $conn->close();
 ?>
