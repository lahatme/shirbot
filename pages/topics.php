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


$host="127.0.0.1";
$port=3306;
$socket="";
$user="root";
$password="";
$dbname="chat_try";

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
  or die ('Could not connect to the database server' . mysqli_connect_error());
$conn->set_charset("utf8");
//$topic = $_POST["topic"];
  $sql = "SELECT topics.topic, COUNT(DISTINCT tasks.task) as counter FROM chat_try.tasks JOIN chat_try.topics ON topics.ID = tasks.topic GROUP BY topics.topic";
  $result = $conn->query($sql);

  $data_array = array("");
  if ($result->num_rows > 0) {
      // output data of each row
      while($row = $result->fetch_assoc()) {
          $row_array["topic"] = $row["topic"];
          $row_array["coutner"] = $row["counter"];
          array_push($data_array, $row_array);
      }
  } else {
      echo "0 results";
  }
  $conn->close();
  array_shift($data_array);
  $json = json_encode($data_array,JSON_UNESCAPED_UNICODE);
  echo $json;
 ?>
