<?php

      include ('../classes/db_con.php');
      $db = new Database();
      $topics = $_POST['topics'];
      $sql = "SELECT distinct topics.topic as topic, count(distinct tasks.ID) as topic_count FROM chat_try.topics LEFT JOIN chat_try.tasks ON tasks.topic = topics.id WHERE topics.topic in $topics GROUP BY topics.topic ";
      //echo $sql;
      $db->Query($sql);
      echo $db->Rows();
 ?>
