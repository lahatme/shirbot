<?php
    session_start();
    if(isset($_POST["s_topic"]))
      $_SESSION["s_topic"] = $_POST["s_topic"];
      if(isset($_POST["s_task"]))
        $_SESSION["s_task"] = $_POST["s_task"];

  $json = json_encode($_SESSION,JSON_UNESCAPED_UNICODE);
  echo $json;
?>
