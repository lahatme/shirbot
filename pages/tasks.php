
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

      $host="127.0.0.1";
      $port=3306;
      $socket="";
      $user="root";
      $password="1234";
      $dbname="chat_try";

      $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
      	or die ('Could not connect to the database server' . mysqli_connect_error());
      $conn->set_charset("utf8");
      $topic = $_POST["topic"];
        $sql = "SELECT tsk.title, tsk.task FROM chat_try.tasks tsk Left Join chat_try.topics tpc ON tsk.topic = tpc.ID WHERE tpc.topic = '$topic'";
        $result = $conn->query($sql);

        $data_array = array("");
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $row_array["title"] = $row["title"];
                $row_array["task"] = $row["task"];
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
