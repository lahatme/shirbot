<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../css/style.css" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Assistant" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet">
    <link href="../css/progress-wizard.min.css" rel="stylesheet">
    <link href="https://afeld.github.io/emoji-css/emoji.css" rel="stylesheet">
    <script src="../js/mark.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="../js/randomcolor.js"></script>
    <script type="text/javascript">
    <?php
    include '../classes/user.php';
     session_start();
    if(!isset($_SESSION["user"]))
    {
      header("Location: login.php"); exit();
    }
    ?>
    <?php include ('../classes/db_con.php'); ?>
    var intent_collected = [];
    window.onload = setTimeout(function(){
      <?php
      $user = unserialize($_SESSION['user']);
      $name = $user->GetName();
      echo "append_chat('היי $name, אני שירבוט (אפשר לקרוא לי גם שיר) ואני יכולה לעזור לך להשתפר בכתיבה! ביחד נעבור כמה שלבים עד שנקבל טקסט שלם ומושלם :)','bot_comment');";

      $gender = $user->GetGender();
      ?>
      var gender = '<?php echo $gender; ?>';
      if(gender == "male") setTimeout(function(){append_chat("כדי שנתחיל <b> תלחץ </b> על הנושא המועדף עליך <i class='em em-star'></i> ","bot_comment");},1200);
      if(gender == "female") setTimeout(function(){append_chat("כדי שנתחיל <b> תלחצי </b> על הנושא המועדף עלייך <i class='em em-star'></i> ","bot_comment");},2000);
      setTimeout(function(){content_show("wrap_topics");},2800);
    },1200);

    var mapi= new Map();

    function elem(id){
        //מקבלת מזהה של אלמנט ומחזירה את האלמנט
        return document.getElementById(id);
    }

    function OnInput(){
        //ראה פונקצית write_content
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';

    }

    function keyboard_size_change(e){
        //פונקציה שרצה בעת הקלדה בתיבת הטקסט, משנה את גודל המקלדת

        var tx = elem("user_input_text");

        tx.addEventListener("input", OnInput, false);
        var keycode = (e.keyCode ? e.keyCode : e.which);

        if (keycode == 13){
          if (!e.shiftKey) {

            user_input();
          }
        }

    }

    function is_item_in_array(item, array){//הפעולה בודקת אם עצם נמצא במערך, מחזירה ערך בוליאני
            var flag = false;
            array_index = 0;
            while(array_index<array.length)
            {
              if(array[array_index] == item)
              {
                flag = true;
                array_index = array.length;
              }
              array_index++;
            }
            return flag;
          }

    function user_input(){
      //פעולה מתבצעת לאחר הזמנת קלט של המשתמש לבוט
      var user_comment = elem("user_input_text").value;
      append_chat(user_comment, "user_comment");
      bot_proccess(user_comment);

    }

    function append_chat(text, className){
      //מוסיף דיב לתוך הצ'אט לפי קלאס וטקסט'
        var answer = document.createElement("div");
        answer.innerHTML = text;
        answer.setAttribute("class", className);
        var parentDiv = elem("answers");
        if(className == "bot_comment"){

            var avatar_wrapper = document.createElement("DIV");
            avatar_wrapper.setAttribute("class","bot_avatar");
            parentDiv.appendChild(avatar_wrapper);
        }

        parentDiv.appendChild(answer);
        parentDiv.scrollTop = parentDiv.scrollHeight;
        elem("user_input_text").value="";
        elem("user_input_text").focus();
        var current_height = elem("user_input_text").clientHeight;
        if(current_height==52) elem("user_input_text").style.height = 'auto';
    }

    function bot_proccess(text){//הפעולה מקבלת טקסט ומפענחת אותו בווט, שולחת לפעולה -bot_response
      $.ajax({//get wit-ai Intention
          method: "POST",
          url: "./wit_config.php",
          data: {input: text},
          beforeSend: function(){
               var parentDiv = elem("answers");
               parentDiv.scrollTop = parentDiv.scrollHeight+50;
               //var load = document.createElement("div");
               //load.id = "loading";
               //השורות הבאות מסופיות את האפקט של הבוט שמקליד, אנימציה קלילה
               var spinner = document.createElement("div");
               spinner.id = "loading";
               spinner.setAttribute("class","spinner");
               var bounce1 = document.createElement("div");
               bounce1.setAttribute("class","spinner");
               var bounce2 = document.createElement("div");
               bounce2.setAttribute("class","spinner");
               var bounce3 = document.createElement("div");
               bounce3.setAttribute("class","spinner");
               spinner.appendChild(bounce1);
               spinner.appendChild(bounce2);
               spinner.appendChild(bounce3);

               document.getElementById('answers').appendChild(spinner);
               parentDiv.scrollTop = parentDiv.scrollHeight+50;
           }
          }).done(function(msg) {
          var intent = 'default;'
          var obj = JSON.parse(msg);
          if(Object.keys(obj.entities).length>0)
          {
            var main_intent = Object.keys(obj.entities)[0];
            var conf = obj.entities[main_intent][0].confidence;
            if(conf>0.7) {intent = main_intent; intent_collected.push(main_intent);}
            bot_response(intent);
          }
    });    }

    function bot_response(intent){//מקבלת אינטנט ומבצעת אקשן לפיו - אם כתגובה בצ'ט או אם במסך'

      $.ajax({
          method: "POST",
          url: "./bot_responses.php",
          data: {intent0: intent},
          success: function(html){
                document.getElementById("loading").remove();
           }
          }).done(function(msg) {
          bot_answer_from_options(msg, intent);
      });
    }

    function bot_answer_from_options(opts, intent){//מקבלת אינטנט ואת התשובות האפשריות לאינטנט המסוים
        opts = opts.split("&");
        var x_op = Math.floor((Math.random() * (opts.length - 1)) + 1);
        randomanswer = opts[x_op];
        append_chat(randomanswer, "bot_comment");
        switch(intent){
          case "pick_a_topic":
            var timeline = document.getElementsByClassName("progress-indicator")[0].childNodes;
            timeline[3].setAttribute("class","active"); //משנה בציר ההתקדמות את הצבע לכחול
            content_show("wrap_topics");
            break;
          case "positive":
            if(intent_collected[intent_collected.length-2] == "howru"){
              bot_proccess("shirletsmoveon");
            }
        }
    }

    function content_show(content_div_id){
        switch (content_div_id) {
          case "wrap_topics":
            document.getElementById('wrap_tasks').style.display = 'none';
            elem("wrap_topics").style.display = "flex";
            var topics = <?php $db = new Database();
                                $db->Query("SELECT * FROM chat_try.topics");
                                echo $db->Rows();
            ?>;
            var topics_to_ajax = "";
            random_hash_array = [];
            var elements = document.getElementsByClassName("topic_item");
            while(elements.length > 0)
               elements[0].parentNode.removeChild(elements[0]);

              var t_op = Math.floor((Math.random() * (topics.length - 1)) + 1);
              while(random_hash_array.length<6)
              {
                if(!is_item_in_array(t_op,random_hash_array))
                  random_hash_array.push(t_op);
                  t_op = Math.floor((Math.random() * (topics.length - 1)));
              }
              for (var i = 0; i < random_hash_array.length; i++) {
              random_topic = topics[random_hash_array[i]].topic;
              var topic = document.createElement("div");
              topic.innerText = random_topic;
              topics_to_ajax += "'"+random_topic+"',";
              topic.setAttribute("class","topic_item");
              topic.addEventListener("click",function(){topic_click(this)});
              var parentDiv = elem("topics");
              var topic_counter = document.createElement("DIV");
              topic_counter.id = "t-c"+i;
              topic_counter.setAttribute("class", "topic_counter");

              topic.appendChild(topic_counter);
              parentDiv.appendChild(topic);

              var timeline = document.getElementsByClassName("progress-indicator")[0].childNodes;
              timeline[1].setAttribute("class","completed");
            }
            topics_to_ajax = topics_to_ajax.slice(0, -1);
            topics_to_ajax = "("+topics_to_ajax+")";
            $.ajax({
                method: "POST",
                url: "./tasks_count.php",
                data: {topics: topics_to_ajax}
                }).done(function(msg) {
                  var obj = JSON.parse(msg);
                  fill_tasks_counter_with_data(obj);
              });

              function fill_tasks_counter_with_data(json){
                var topic_items = document.getElementsByClassName("topic_item");
                for(i = 0; i<json.length; i++){
                  for(j = 0; j< topic_items.length; j++)
                  {
                    var topic_text = topic_items[j].innerText;
                    var json_text = json[i].topic
                    if(topic_text == json_text){
                      var child = topic_items[j].children[0];
                      child.innerText = json[i].topic_count;
                    }
                }
              }
            }


            elem("wrap_topics").style.opacity = "1";

            var colors = ["#E25365", "#0F74A8", "#E2AF32","#7E5887", "#26A882", "#3FA9F5"];
            var topics = document.getElementsByClassName("topic_item");
            for(i = 0; i<topics.length; i++)
            {
              topics[i].style.background = colors[i];
            }
           var anchors = document.getElementsByClassName('topic_item');
            for(var i = 0; i < anchors.length; i++) {
            var anchor = anchors[i];
          }
            break;
        }
    }

    function topic_click(elm){
      //בחירת נושא!
        append_chat("בחרתי בנושא "+elm.childNodes[0].data,"user_comment");
        var timeline = document.getElementsByClassName("progress-indicator")[0].childNodes;
          timeline[3].setAttribute("class","completed");
        document.getElementById('wrap_topics').style.display = 'none';
        document.getElementById('wrap_tasks').style.display = 'flex';
        show_task(elm, elm.childNodes[0].data);
      }



      function show_task(elm,topic_name){
        $.ajax({
            method: "POST",
            url: "tasks.php",
            data: {topic: topic_name}
            }).done(function(msg) {
            var obj = JSON.parse(msg);
            var elements = document.getElementsByClassName("task_item");
            while(elements.length > 0)
               elements[0].parentNode.removeChild(elements[0]);
            for (var i = 0; i < obj.length; i++) {
              var task_i_title = obj[i].title;
              var task_i_task = obj[i].task;
              var task = document.createElement("TABLE");
              var table_row = document.createElement("TR");

              var task_title_td = document.createElement("TD");
              task_title_td.appendChild(document.createTextNode(task_i_title));
              task_title_td.setAttribute("class","task_title_td");
              task_title_td.style.background= elm.style.background;
              var task_task_td = document.createElement("TD");
              task_task_td.setAttribute("class","task_task_td");
              task_task_td.appendChild(document.createTextNode(task_i_task));

              table_row.appendChild(task_title_td);
              table_row.appendChild(task_task_td);

              task.appendChild(table_row);
              task.setAttribute("class","task_item");
              var parentDiv = elem("tasks");
              parentDiv.appendChild(task);
              //task.style.background= elm.style.background;
              /*
              task.innerText = task_i;

              task.setAttribute("task", obj[i].task);
              task.setAttribute("title", obj[i].title);
              var parentDiv = elem("tasks");
              parentDiv.appendChild(task);
              task.style.background= elm.style.background;
              task.addEventListener("mouseenter",function(){task_hover(this,"in")},false);
              task.addEventListener("mouseleave",function(){task_hover(this,"out")},false);*/
              task.addEventListener("click",function(){task_click(this)});
              }

        });
      }
      function task_hover(e,direction){
       if(direction == "in"){
         var task_text =  e.getAttribute("task");
          e.innerText = task_text;
          e.style.fontSize = "22px";
          e.style.textAlign = "justify";
          e.style.lineHeight = "120%";
          e.style.padding = "20px";
          e.style.boxSizing = "border-box";
          e.style.display = "flex";
          e.style.justifyContent = "center";
          e.style.flexDirection = "column";
       }
       else {
         var title_text =  e.getAttribute("title");
         e.innerText = title_text;
         e.style.fontSize = "30px";
         e.style.textAlign = "center";
         //alert("hi");
       }}


     var hash_ind=0;

     function show_aspect(){
      document.getElementsByClassName("large_shir")[0].style.display = "block";
      elem("arguing_proccess_btns").style.display = "block";
      var gender = '<?php echo $gender; ?>';
      var large_bot_msg_txt = "<B>עכשיו הגיע הזמן לחשוב על טיעונים בעד ונגד!</B> בוא נראה על כמה טיעונים אתה יכול לחשוב <i class='em em-thinking_face'></i>";
      if(gender == "male") document.getElementsByClassName("large_bot_msg_txt")[0].innerHTML = large_bot_msg_txt;
      else{
        large_bot_msg_txt = "<B>עכשיו הגיע הזמן לחשוב על טיעונים בעד ונגד!</B> בואי נראה על כמה טיעונים את יכולה לחשוב <i class='em em-thinking_face'></i>";
        document.getElementsByClassName("large_bot_msg_txt")[0].innerHTML = large_bot_msg_txt;
      }


                                       /*
       var timeline = document.getElementsByClassName("progress-indicator")[0].childNodes;
       timeline[5].setAttribute("class","active");

        var aspects = <?php #$db = new Database();
                             #$db->Query("SELECT  distinct aspect FROM chat_try.aspects");
                             #echo $db->Rows();
         ?>;

         var x_op =  Math.floor(Math.random() * (aspects.length)+1)-1;
          while (mapi.has(x_op)) {
              x_op =  Math.floor(Math.random() * (aspects.length)+1)-1;
              if(hash_ind==aspects.length){
                document.getElementById("aspect_item").innerText= "אין יותר היבטים";
                return;
              }

         }

         mapi.set(x_op);
         hash_ind++;

         console.log( mapi);
         document.getElementById("aspect_item").innerText= "טיעון " +  aspects[x_op].aspect;
         var colors = ["#E25365", "#0F74A8", "#E2AF32","#7E5887", "#26A882", "#3FA9F5"];
         var aspect_color =  Math.floor(Math.random() * (colors.length - 1) + 1);
         console.log(aspect_color);
         //document.getElementById("aspect_item").style.background = colors[aspect_color];
         $.ajax({
             method: "POST",
             url: "aspect.php",
             data: {aspect: aspects[x_op].aspect}
             }).done(function(msg) {
                    var aspects = JSON.parse(msg);
                    console.log(aspects)
      //  var topics_arr = topics.split(',');
                    var elements = document.getElementsByClassName("word_item");
                    while(elements.length > 0)
                      elements[0].parentNode.removeChild(elements[0]);
                    if(document.getElementsByClassName("word_item").length == 0)
                    for (var i = 0; i < 9; i++) {
                      var aspect = document.createElement("div");
                      aspect.innerText = aspects[i];
                      //aspect.style.background = colors[aspect_color];
                      aspect.setAttribute("class","word_item");
                      var parentDiv = elem("aspect");
                      parentDiv.appendChild(aspect);
                    }
                    elem("wrap_aspect").style.opacity = "1";
                    elem("aspect_card").style.background =  colors[aspect_color];
                  });*/
      }




        function task_click(elm){
          document.getElementById('wrap_tasks').style.display = 'none';
          //document.getElementById('wrap_aspect').style.display = 'flex';
          show_aspect();

        }

        function i_finish(){
          var timeline = document.getElementsByClassName("progress-indicator")[0].childNodes;
          timeline[5].setAttribute("class","completed");
          timeline[7].setAttribute("class","active")
          document.getElementById('wrap_large_tiunim').style.display = 'none';
          document.getElementsByClassName('large_shir')[0].style.display = 'none';

          document.getElementById('wrap_table').style.display = 'flex';
          elem("wrap_table").style.opacity = "1";
          //elem("wrap_aspect").style.opacity = "0";
        }

        /*function check_argument_length(e){
              var lines = document.getElementsByClassName('argument_input')[0].innerText.split("\n");
              if(lines.length>3)
                  e.preventDefault();
        }*/


        function self_argue(){
          elem("arguing_proccess_btns").style.display = "none";
          var box = document.getElementsByClassName('tiun_insert_box')[0];
          box.classList.remove("against_back");
          box.classList.remove("for_back");
          box.style.display = "block";
          box.innerHTML = "אני רוצה להוסיף טיעון <button class='for_against' id='for' onclick='add_self_tiun(this)'> בעד </button> <button class='for_against' id='against' onclick='add_self_tiun(this)'> נגד </button> שילוב שחקנים זרים בנבחרות";
          if(document.getElementsByClassName('triger_btns').length>0){
            var container = document.getElementsByClassName('large_shir')[0];
            container.removeChild(document.getElementsByClassName('triger_btns')[0]);
          }
          if(document.getElementsByClassName('aspect_card').length > 0)
          elem("wrap_large_tiunim").innerHTML += "<div class='triger_btns'> <button id='add_shir_tiun' onclick='self_argue()'> יש לי טיעון! </button> <button id='next_card' onclick='shir_argue()'> היבט אחר </button> <button id='finish_tiunim' onclick='i_finish()'> סיימתי להעלות טיעונים </button> </div>";
          else elem("wrap_large_tiunim").innerHTML += "<div class='triger_btns'> <button id='finish_tiunim' onclick='i_finish()'> סיימתי להעלות טיעונים </button> <button id='next_card' onclick='shir_argue()'> שיר תעזרי לי <i class='em em-bulb'></i></button> </div>";
        }

        function add_self_tiun(el){//הוספת טיעון באופן עצמאי
          if(document.getElementsByClassName("tiun_input_text").length == 0){
          if(el.id == "for") $("#against").hide();
          else $("#for").hide();
          //document.getElementsByClassName('tiun_insert_box_txt')[0].innerHTML += " הטיעון הוא: "
          /*var tiun_input = document.createElement("textarea");
          tiun_input.setAttribute("class","tiun_input_text");
          tiun_input.setAttribute("rows","1");*/
          document.getElementsByClassName('tiun_insert_box')[0].innerHTML = document.getElementsByClassName('tiun_insert_box')[0].innerHTML.replace("רוצה להוסיף טיעון","") + " כי: ";
          document.getElementsByClassName("tiun_insert_box")[0].innerHTML += "<table id='tiun_input_table'><tr style='width:100%'><td style='width:85%'><textarea id='tiun_input_text' class='tiun_input_text' placeholder='לכובע שלי שלוש פינות...' spellcheck='true' rows='1' cols='1'></textarea></td><td style='text-align: center;vertical-align: middle;width: 8%;'><button id='tiun_btn' onclick='tiun_collection(this)'>טען</button></td></tr></table>"
          document.getElementById('tiun_input_text').addEventListener("input", OnInput, false);
          if(el.id =="for"){document.getElementsByClassName('tiun_insert_box')[0].classList.remove("against_back"); document.getElementsByClassName('tiun_insert_box')[0].classList.add("for_back"); document.getElementsByClassName("tiun_input_text")[0].style.borderColor = "#0BCDB4"; elem("tiun_btn").classList.add("for_back");}
          else {elem("tiun_btn").classList.add("against_back"); document.getElementById("tiun_input_table").style.borderColor = "#E25365"; document.getElementsByClassName('tiun_insert_box')[0].classList.remove("for_back"); document.getElementsByClassName('tiun_insert_box')[0].classList.add("against_back");}
          //document.getElementsByClassName('tiun_insert_box')[0].innerHTML += "<button id='next_tiun_more'>שלח והוסף טיעון</button> <button id='next_tiun_done'>אין לי עוד טיעונים</button>";
          }
        }
        var used_aspects = [];

        function shir_argue(){//העלאת טיעונים ביחד עם שיר - כרטיסיות ומילים
          document.getElementsByClassName('tiun_insert_box')[0].style.display = "none";
          elem("arguing_proccess_btns").style.display = "none";
          document.getElementsByClassName('large_bot_msg_txt')[0].innerHTML = "<?php echo $name. " עלו לי כמה רעיונות לטיעון שלך <i class='em em-wink'></i> נסי לחשוב על: " ?>";
          ///קטע זה של הקוד שואב מתוך מסד הנתונים את תחומי החיים והמילים
          var aspects = <?php $db = new Database();
                              $db->Query("SELECT  distinct aspect FROM chat_try.aspects");
                              echo $db->Rows();
           ?>;
          var raw_json_words = <?php $db = new Database();
                              $db->Query("SELECT distinct aspect, word FROM chat_try.aspects");
                              echo $db->Rows();
           ?>;
           words_array = [];
           words_final_json = [];
           for(i = 0; i< aspects.length ; i++){
             var aspect = aspects[i].aspect;
               for(j = 0; j<raw_json_words.length ; j++){
                  var raw_aspect = raw_json_words[j].aspect;
                  if(raw_aspect == aspect)
                    words_array.push(raw_json_words[j].word);
               }
               words_final_json.push({"aspect": aspect, "words":words_array});
               words_array = [];
           }
          //var used_aspects = [];

          if(used_aspects.length < words_final_json.length){ //כל עוד יש כרטיסיות שעוד לא ראינו, שיר רצה עליהן
                  var random_card_id = getRandomInt(0,words_final_json.length-1);
                  if(!is_item_in_array(random_card_id,used_aspects))
                    used_aspects.push(random_card_id);
                    else {
                      while(is_item_in_array(random_card_id, used_aspects) && words_final_json.length > used_aspects.length){
                        random_card_id = getRandomInt(0,words_final_json.length-1);

                      }
                     if(!is_item_in_array(random_card_id,used_aspects)) used_aspects.push(random_card_id);
                    }
                  var apsect_title = words_final_json[random_card_id].aspect;



                  var container = document.getElementsByClassName('large_shir')[0];
                  if(document.getElementsByClassName('aspect_card').length >0)
                  {
                  var child = document.getElementsByClassName('aspect_card')[0];
                  child.parentNode.removeChild(child);
                  }
                  if(document.getElementsByClassName('triger_btns').length >0)
                  {
                    var child = document.getElementsByClassName('triger_btns')[0];
                    child.parentNode.removeChild(child);
                  }
                  container.innerHTML += "<div class='aspect_card'> <div class='aspect_card_title'> היבט  " + apsect_title + " </div> <div class='aspect_card_sub_title'> נסה להשתמש באחת המילים הבאות בכתיבת הטיעון:   </div> </div>";
                  var card = document.getElementsByClassName('aspect_card')[0];
                  card.innerHTML += "<div class='aspect_card_words_wrap'></div>";
                  var words_list = words_final_json[random_card_id].words;
                  for(i = 0; i< 10 ; i++)
                      document.getElementsByClassName('aspect_card_words_wrap')[0].innerHTML += "<div class='aspect_word'>"+words_list[i]+"</div>";
                  container.innerHTML += "<div class='triger_btns'> <button id='add_shir_tiun' onclick='self_argue()'> יש לי טיעון! </button> <button id='next_card' onclick='shir_argue()'> היבט אחר </button> <button id='finish_tiunim' onclick='i_finish()'> סיימתי להעלות טיעונים </button> </div>";
              }

              else{//כשניגמרו הכרטיסיות
              document.getElementsByClassName('large_bot_msg_txt')[0].innerHTML = "אין לי עוד רעיונות " + "<i class='em em-disappointed_relieved'></i>";

              }
          }

        function tiun_collection(b){
              var user_opinion = "";
              if(b.className == "for_back") user_opinion = "positive";
              else user_opinion = "negative";
              var opinion_text = elem("tiun_input_text").value;


              var arg = document.createElement("div");
              arg.setAttribute("class",user_opinion+"_item");
              arg.innerHTML="<div>"+ opinion_text+"</div>";
              arg.id = user_opinion+"_item"+document.getElementsByClassName(user_opinion+"_item").length;
              var parentDiv = elem(user_opinion+"_wrapper");
              parentDiv.appendChild(arg);
              //document.getElementsByClassName("argument_input")[0].value="";
              arg.addEventListener("dragstart",function(){drag(event)});
              arg.setAttribute('draggable', true);
              self_argue();
        }

        function Select_opinion(no_opt,opt,arrange,num){
       elem(no_opt).style.opacity = "0";
       elem(no_opt).style.display = "none";
       elem(opt).style.height= "80vh";
       elem(arrange).style.opacity= "1";
       elem(arrange).style.display="flex";
       document.getElementsByClassName('user_finish_arrange')[num].style.opacity="1";


       //show_writing();

     }

     function allowDrop(ev) {
       ev.preventDefault();
     }

     function drag(ev) {
         ev.dataTransfer.setData("text", ev.target.id);
     }
     var Arrangement= [];

     function drop(ev,i) {
         ev.preventDefault();
         var data = ev.dataTransfer.getData("text");
         // console.log(typeof(data));
         ev.target.appendChild(document.getElementById(data));
         Arrangement[i]={_text:data};
     }

     var direct=[];
     var opt1;
     var number_of_instruction =0;
     function show_writing(opt){
         opt1=opt;
         document.getElementById('wrap_table').style.display = 'none';
         document.getElementById('wrap_writing').style.display = 'block';
         elem("wrap_writing").style.opacity = "1";
         elem("wrap_table").style.opacity = "0";
         direct = <?php $db = new Database();
                             $db->Query("SELECT * FROM chat_try.direct");
                             echo $db->Rows();
         ?>;
         append_chat(direct[0].text,"bot_comment");
         number_of_instruction = document.getElementsByClassName(opt).length  ;
         var text1= "פסקת פתיחה";
       //  var text1 = document.getElementById(Arrangement[n]._text).childNodes[1].innerText;
         elem('wrting_area').innerText= text1;
       }


       function check_text(){
            elem("wrap_after_text").innerHTML = "";
            var text_elemnt = document.getElementById("wrting_area");
            var text = "";
            if(text_elemnt.tagName == "TEXTAREA") text = document.getElementById("wrting_area").value;
            else text = document.getElementById("wrting_area").innerText;

              var div_to_add_after_check = document.createElement("div");
              div_to_add_after_check.id= "feedback_area";
              div_to_add_after_check.contentEditable = "true";
              //var textarea = document.getElementById("wrting_area");
              //var paernt = document.getElementById("wrap_before_text");
              //paernt.removeChild(textarea);

              //הפרדת שורות כמו שנקלטו
              var lines = text.split("\n");
              for(i = 0; i< lines.length; i++){
                var paragraph_text = lines[i];
                var node = document.createTextNode(paragraph_text);
                var p = document.createElement("P");
                p.setAttribute("class", "p"+i);
                p.appendChild(node);
                div_to_add_after_check.appendChild(p);
              }
              //מציאת המשפט הארוך ביותר
              var text_without_entres = text.replace(/\n/g,"");
              var sentences = get_sentences_json(text_without_entres);
              var sorted_sentences = sort_sentences_json(sentences);
              var longest_sentences = get_longest_sentences(sorted_sentences);

              //מציאת מילים שחזרו על עצמם
              var text_without_punctuations = get_rid_off_punctuations(text);
              var word_counter = text_without_punctuations.split(" ");
              var words_json = diff_words(text_without_punctuations);
              var sorted_words_json = sort_words_json(words_json);
              var most_common_words = get_most_used_words(sorted_words_json);
          //מציאת היחס הקטן בייותר של אורך משפט מול מספר הפסיקים בו

              sentences = get_sentences_json(text);
              var count_comma = get_count_comma(sentences);
              var most_cumbersome= find_proportion(sentences,count_comma, sorted_sentences );
              console.log(most_cumbersome);


              var other_popular = [];
              for(i = 1; i<5;i++)
              {
                if(sorted_words_json[i] != undefined) other_popular.push(sorted_words_json[i]._word);
              }

              var contrary = ['אך','לעומת זאת','אבל','למרות','להפך','אלא','אדרבא','אולם','בניגוד','מול'];
              var reasoning = ['כי','בגלל','מפני','כיוון','היות','משום','עקב','לרגל','הואיל'];
              var resulting = ['לכן','משום כך','לפיכך','כתוצאה','בעקבות','על כן','עקב'];
              var contrasts = ['על אף','למרות','אף על פי','אם כי','בכל אופן','חרף'];
              var comparing = ['בדומה','כמו','במקביל','בהשוואה','כשם','כפי','במידה','כך'];
              var timing = ['בזמן','לפני','אחרי','מאז','לאחרונה','בינתיים','בטרם','לפי שעה','בו בזמן'];
              var aiming = ['כדי','על מנת','לשם','למען','במטרה','בשביל','לבל','פן'];
              var addition = ['גם','בנוסף','וכן','יתר על כן'];
              var detailing = ['חוץ','פרט','רק','מלבד','למעט'];
              var choice = ['או'];
              var condition = ['אם','לו','אילולא','בתנאי','אלמלא'];


              var parent = document.getElementById("wrap_after_text");
              parent.insertBefore(div_to_add_after_check,document.getElementById("feedback_area"));
              //parent.innerHTML += "<button id='to_next_p' onclick='next_p()'> סיימתי לתקן </button>"
              document.getElementById("wrap_before_text").style.display = "none";
              document.getElementById("wrap_after_text").style.display = "block";
              document.getElementById("wrap_after_text").style.opacity = 1;
              //הוספת הדגשות בתוך הטקסט
              var instance = new Mark(div_to_add_after_check);
              instance.mark(most_common_words, {"className": "repeat", "accuracy": "exactly"});

             instance.mark(most_cumbersome[0]._sentence, {"className":"complicated", "accuracy": "exactly","separateWordSearch": false});

              var start = text_without_entres.indexOf(longest_sentences[0]);
              var chars_len = longest_sentences[0].length;
              instance.markRanges([{start:start, length:chars_len}],{"className": "longest"});

              start = text_without_entres.indexOf(most_cumbersome[0]._sentence);
              chars_len = most_cumbersome[0]._sentence.length;
              instance.markRanges([{start:start, length:chars_len}],{"className": "complicated"});

              instance.mark(contrary, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(reasoning, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(resulting, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(contrasts, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(comparing, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(timing, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(aiming, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(addition, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(detailing, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(choice, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});
              instance.mark(condition, {"className":"connectors", "accuracy": "exactly","separateWordSearch": false});

              var connectors_data = [];
              var connectors_in_text = document.getElementsByClassName("connectors");
              for(i = 0; i < connectors_in_text.length; i++)
              {
                var counter = 0;
                for(j = 0; j<connectors_in_text.length;j++)
                {
                  if(connectors_in_text[i].innerText == connectors_in_text[j].innerText) counter++;
                }
                if(!is_connector_in_json(connectors_in_text[i].innerText, connectors_data) && connectors_in_text[i].innerText!="") connectors_data.push({_connector:connectors_in_text[i].innerText, _counter: counter});
              }
              console.log("DATA");
              console.log(connectors_data);

              //var parent = document.getElementById("wrap_after_text");
              //parent.insertBefore(div_to_add_after_check,document.getElementById("feedback_area"));
              parent.innerHTML += "<button id='to_next_p' onclick='next_p()'> סיימתי לתקן </button>"



             feed_word = <?php $db = new Database();
                                 $db->Query("SELECT * FROM chat_try.feedback");
                                 echo $db->Rows();
             ?>;
             var feedback1 = feed_word[0].text + "<br>" + feed_word[1].text +  word_counter.length+ "<br>"+ feed_word[2].text + sentences.length + "<br>" + feed_word[3].text +(Math.round(words_json.length / sentences.length * 100) / 100) + feed_word[4].text;
             var feedback2 = feed_word[5].text +  most_common_words[0] +  feed_word[6].text +  sorted_words_json[0]._count+" פעמים " + feed_word[7].text + "<br>" + feed_word[8].text + most_common_words + "<br>" + feed_word[9].text + document.getElementsByClassName("connectors").length + feed_word[10].text ;
             var feedback3 = feed_word[11].text +  connectors_data.length + feed_word[12].text + "<br>" + feed_word[13].text +  sorted_sentences[0]._word_count + feed_word[14].text + "<br>" + feed_word[15].text;
             append_chat( feedback1 +"<br>" +feedback2+ "<br>" +feedback3 ,"bot_comment");
             }

             var k =1;
             function next_p(){

             //var wrting_area = document.createElement("div");
             //wrting_area.setAttribute("class","wrting_area");
             append_chat(direct[k].text,"bot_comment");
             k++;
             var text_elemnt = document.getElementById("feedback_area");
             var text = "";
             if(text_elemnt.tagName == "TEXTAREA") text = document.getElementById("feedback_area").value;
             else text = document.getElementById("feedback_area").innerText;
             var argi = document.createElement("div");
             argi.setAttribute("class","texts");
             argi.innerHTML= text;
             number_of_instruction--;
             //console.log(n);
             if (number_of_instruction>= 0){
             var text1 = document.getElementById(Arrangement[number_of_instruction]._text).childNodes[0].innerText;
             console.log(text1);
             elem('wrting_area').value= text1;

             elem('wrting_area').innerText= text1;

             //elem('writing_comp').innerText= text;
           }
             var parentDiv = elem("writing_comp");
             parentDiv.appendChild(argi);
             elem('wrting_area').style.display="block";
             document.getElementById("wrap_before_text").style.display= "block";
             document.getElementById("wrap_before_text").style.opacity = "1";
             document.getElementById("wrap_after_text").style.opacity = "0";

             }


          function get_rid_off_punctuations(text){
             var punctuationless = text.replace(/[.,\/#!$%\^&\*;:{}=\--_`~()]/g," ");
             punctuationless = punctuationless.replace(/\n/g," ");
             punctuationless = punctuationless.replace(/<[^>]+>/g," ");
             return punctuationless.replace(/\s{2,}/g," ");
           }

       function is_word_in_json(word, json){
           var flag = false;
           json_index = 0;
           while(json_index<json.length)
           {
             if(json[json_index]._word == word)
             {
               flag = true;
               json_index = json.length;
             }
             json_index++;
           }
           return flag;
       }

       function is_connector_in_json(connector, json){
         var flag = false;
         json_index = 0;
         while(json_index<json.length)
         {
           if(json[json_index]._connector == connector)
           {
             flag = true;
             json_index = json.length;
           }
           json_index++;
         }
         return flag;
       }

       /*function is_item_in_array(item, array){
         var flag = false;
         array_index = 0;
         while(array_index<array.length)
         {
           if(array[array_index] == item)
           {
             flag = true;
             array_index = array.length;
           }
           array_index++;
         }
         return flag;
       }*/

       function diff_words(text_no_punctuations){
         words_raw = text_no_punctuations.split(" ");
         words_proccessed = [];
         var words = [];

         for(i = 0; i<words_raw.length;i++)if(words_raw[i]!="") words_proccessed.push(words_raw[i]);
         for(i = 0; i<words_proccessed.length; i++){
           var counter = 0;
           for(j=0; j< words_proccessed.length; j++){
             if(words_proccessed[i]==words_proccessed[j]) counter++;
           }
           if(!is_word_in_json(words_proccessed[i], words)) words.push({_word:words_proccessed[i], _count:counter});
         }
         return words;
       }

       function sort_words_json(json){
         var numeric_values_array = [];
         json.forEach(function(e){if(!is_item_in_array(e._count, numeric_values_array))numeric_values_array.push(e._count)});
         numeric_values_array = numeric_values_array.sort(function(a, b){return b-a});
         sorted_json = [];
         for(i = 0; i<numeric_values_array.length;i++)
         {
           for(j=0;j<json.length;j++)
           {
             if(json[j]._count==numeric_values_array[i]){
               if(!is_word_in_json(json[j]._word, sorted_json))sorted_json.push(json[j]);
             }
           }
         }
         return sorted_json;
       }

       function get_most_used_words(json){
         //json is sorted top-down
         var top_words = [];
         for(i = 0; i<json.length;i++)
         {
           if(json[i]._count == json[0]._count) top_words.push(json[i]._word);
           else i=json.length;
         }
         return top_words;
       }

       function get_sentences_json(text){
         var sentences_raw = text.split(/[\\.!\?]/);
         var sentences = [];
         for(i=0; i< sentences_raw.length; i++){
           var words_in_setnece_raw = sentences_raw[i].split(" ");
           var words_in_setnece =[];
             for(x=0; x<words_in_setnece_raw.length;x++) if(words_in_setnece_raw[x]!="" && words_in_setnece_raw[x] != "-") words_in_setnece.push(words_in_setnece_raw[x]);
               if(sentences_raw[i] != "") sentences.push({_sentence: sentences_raw[i], _word_count : words_in_setnece.length});
         }
         return sentences;
       }

       function sort_sentences_json(json){
         var numeric_values_array = [];
         json.forEach(function(e){if(!is_item_in_array(e._word_count, numeric_values_array))numeric_values_array.push(e._word_count)});
         numeric_values_array = numeric_values_array.sort(function(a, b){return b-a});
         sorted_json = [];
         for(i = 0; i<numeric_values_array.length;i++)
         {
           for(j=0;j<json.length;j++)
           {
             if(json[j]._word_count==numeric_values_array[i]){
               if(!is_word_in_json(json[j]._sentence, sorted_json))sorted_json.push(json[j]);
             }
           }
         }
         return sorted_json;
       }

       function get_longest_sentences(json){
         //json is sorted top-down
         var longes_sentences = [];
         for(i = 0; i<json.length;i++)
         {
           if(json[i]._word_count == json[0]._word_count) longes_sentences.push(json[i]._sentence);
           else i=json.length;
         }
         return longes_sentences;
       }

       function get_count_comma(sentences){
        var count_comma= [];
        var index1= -1;
        for(x=0; x<sentences.length;x++){
          var count_comma_s=0;
          cur_sentences= sentences[x]._sentence;
          console.log(cur_sentences);
          index1 = cur_sentences.indexOf(",", 0);
          while (index1!= -1) {
            cur_sentences= cur_sentences.substring(index1+1);
            index1 =  cur_sentences.indexOf(",");
            count_comma_s=count_comma_s+1;
          }
          count_comma.push({_sentence: sentences[x], count_comma_s : count_comma_s});
          console.log(count_comma);
        }
        return count_comma;
           }

      function find_proportion(sentences,count_comma,sorted_sentences){
        var proportion = [];
        var p =0;
        var min_p =1;
        var sentences_min_p=[];
        var p_zero=[];
        for(x=0; x<sentences.length; x++){
          p= (count_comma[x].count_comma_s/sentences[x]._word_count);
          proportion.push({_sentence: sentences[x], proportion_s : p});
          if (p==0){
            p_zero.push({_sentence:sentences[x] , _word_count : sentences[x]._word_count});
          }
          if (p<min_p){min_p=p;}
          sentences_min_p =sentences[x]._sentence;
        }
        console.log(proportion);
        console.log(sentences_min_p);
        if (min_p==0){
          var sorted_sentences_zero = sort_sentences_json(p_zero);
          console.log(sorted_sentences_zero);
          sentences_min_p= get_longest_sentences(sorted_sentences_zero);
        }
        console.log(sentences_min_p);
        return sentences_min_p;
      }




        function getRandomInt(min, max) {//קבלת מספר רנדומלי בין מין למקס
          return Math.floor(Math.random() * (max - min + 1)) + min;
        }
    </script>
    <title></title>
  </head>
  <body>
      <div id="container">

          <div id="content">
            <div id="upper">
              <a href="logout.php" id="logout">התנתק</a>
            <div id="wrap_topics">
                <div id="topics">
                </div>
              </div>

              <div id="wrap_tasks">
               <div id="tasks">

               </div>
             </div>

             <div class="large_shir">
              <!-- <div class="large_bot_avatar"></div> !-->
               <div class="large_bot_msg"><span class="large_bot_msg_txt"></span></div>

             </div>
             <div id="wrap_large_tiunim">
               <div id="arguing_proccess_btns">
                 <button id="alone" onclick="self_argue()">יש לי טיעונים משלי</button>
                 <button id="together" onclick="shir_argue()">בואי נחשוב ביחד</button>
               </div>



             <div class="tiun_insert_box">
               <p class="tiun_insert_box_txt"></p>
             </div>
            </div>



              <div id="wrap">


                <div id="wrap_table">
                                    <div id="positive1" onclick="Select_opinion('negative1','positive1','arrange_pos',0)">
                                      <div id=title_p>
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="50px" height="50px">
                                        <g>	<g><path d="M512,304c0-12.821-5.099-24.768-13.888-33.579c9.963-10.901,15.04-25.515,13.653-40.725    c-2.496-27.115-26.923-48.363-55.637-48.363H324.352c6.528-19.819,16.981-56.149,16.981-85.333c0-46.272-39.317-85.333-64-85.333    c-22.144,0-37.995,12.48-38.656,12.992c-2.539,2.027-4.011,5.099-4.011,8.341v72.341L173.205,237.44l-2.539,1.301v-4.075    c0-5.888-4.779-10.667-10.667-10.667H53.333C23.915,224,0,247.915,0,277.333V448c0,29.419,23.915,53.333,53.333,53.333h64    c23.061,0,42.773-14.72,50.197-35.264C185.28,475.2,209.173,480,224,480h195.819c23.232,0,43.563-15.659,48.341-37.248    c2.453-11.136,1.024-22.336-3.84-32.064c15.744-7.915,26.347-24.192,26.347-42.688c0-7.552-1.728-14.784-4.992-21.312    C501.419,338.752,512,322.496,512,304z M467.008,330.325c-4.117,0.491-7.595,3.285-8.917,7.232    c-1.301,3.947-0.213,8.277,2.816,11.136c5.419,5.099,8.427,11.968,8.427,19.307c0,13.461-10.176,24.768-23.637,26.325    c-4.117,0.491-7.595,3.285-8.917,7.232c-1.301,3.947-0.213,8.277,2.816,11.136c7.019,6.613,9.835,15.893,7.723,25.451    c-2.624,11.904-14.187,20.523-27.499,20.523H224c-17.323,0-46.379-8.128-56.448-18.219c-3.051-3.029-7.659-3.925-11.627-2.304    c-3.989,1.643-6.592,5.547-6.592,9.856c0,17.643-14.357,32-32,32h-64c-17.643,0-32-14.357-32-32V277.333c0-17.643,14.357-32,32-32    h96V256c0,3.691,1.92,7.125,5.077,9.088c3.115,1.877,7.04,2.069,10.368,0.448l21.333-10.667c2.155-1.067,3.883-2.859,4.907-5.056    l64-138.667c0.64-1.408,0.981-2.944,0.981-4.48V37.781C260.437,35.328,268.139,32,277.333,32C289.024,32,320,61.056,320,96    c0,37.547-20.437,91.669-20.629,92.203c-1.237,3.264-0.811,6.955,1.173,9.856c2.005,2.88,5.291,4.608,8.789,4.608h146.795    c17.792,0,32.896,12.736,34.389,28.992c1.131,12.16-4.715,23.723-15.189,30.187c-3.264,2.005-5.205,5.632-5.056,9.493    s2.368,7.317,5.781,9.088c9.024,4.587,14.613,13.632,14.613,23.573C490.667,317.461,480.491,328.768,467.008,330.325z" fill="#FFF"/></g>
                                  </g><g>	<g>	<path d="M160,245.333c-5.888,0-10.667,4.779-10.667,10.667v192c0,5.888,4.779,10.667,10.667,10.667s10.667-4.779,10.667-10.667    V256C170.667,250.112,165.888,245.333,160,245.333z" fill="#FFF"/>
                                  </g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
                                      </div>
                                      <input type="button" name="" value="סיימתי לסדר" class="user_finish_arrange" onclick="show_writing('positive_item')"  tabindex="0">
                                      <div id="arrange_pos">
                                         <div id="div1" class="div_pos" ondrop="drop(event,2)" ondragover="allowDrop(event)">פסקה ראשונה</div>
                                         <div id="div2" class="div_pos" ondrop="drop(event,1)" ondragover="allowDrop(event)">פסקה שניה</div>
                                         <div id="div3" class="div_pos" ondrop="drop(event,0)" ondragover="allowDrop(event)">פסקה שלישית</div>
                                      </div>

                                      <div id="positive_wrapper"></div>
                                    </div>
                                    <div id="negative1"  onclick="Select_opinion('positive1','negative1','arrange_neg',1) ">
                                      <div id=title_n>
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="50px" height="50px">
                                        <g><g><path d="M512,304c0-12.821-5.099-24.768-13.888-33.579c9.963-10.901,15.04-25.515,13.653-40.725    c-2.496-27.115-26.923-48.363-55.637-48.363H324.352c6.528-19.819,16.981-56.149,16.981-85.333c0-46.272-39.317-85.333-64-85.333    c-22.144,0-37.995,12.48-38.656,12.992c-2.539,2.027-4.011,5.099-4.011,8.341v72.341L173.205,237.44l-2.539,1.301v-4.075    c0-5.888-4.779-10.667-10.667-10.667H53.333C23.915,224,0,247.915,0,277.333V448c0,29.419,23.915,53.333,53.333,53.333h64    c23.061,0,42.773-14.72,50.197-35.264C185.28,475.2,209.173,480,224,480h195.819c23.232,0,43.563-15.659,48.341-37.248    c2.453-11.136,1.024-22.336-3.84-32.064c15.744-7.915,26.347-24.192,26.347-42.688c0-7.552-1.728-14.784-4.992-21.312    C501.419,338.752,512,322.496,512,304z M467.008,330.325c-4.117,0.491-7.595,3.285-8.917,7.232    c-1.301,3.947-0.213,8.277,2.816,11.136c5.419,5.099,8.427,11.968,8.427,19.307c0,13.461-10.176,24.768-23.637,26.325    c-4.117,0.491-7.595,3.285-8.917,7.232c-1.301,3.947-0.213,8.277,2.816,11.136c7.019,6.613,9.835,15.893,7.723,25.451    c-2.624,11.904-14.187,20.523-27.499,20.523H224c-17.323,0-46.379-8.128-56.448-18.219c-3.051-3.029-7.659-3.925-11.627-2.304    c-3.989,1.643-6.592,5.547-6.592,9.856c0,17.643-14.357,32-32,32h-64c-17.643,0-32-14.357-32-32V277.333c0-17.643,14.357-32,32-32    h96V256c0,3.691,1.92,7.125,5.077,9.088c3.115,1.877,7.04,2.069,10.368,0.448l21.333-10.667c2.155-1.067,3.883-2.859,4.907-5.056    l64-138.667c0.64-1.408,0.981-2.944,0.981-4.48V37.781C260.437,35.328,268.139,32,277.333,32C289.024,32,320,61.056,320,96    c0,37.547-20.437,91.669-20.629,92.203c-1.237,3.264-0.811,6.955,1.173,9.856c2.005,2.88,5.291,4.608,8.789,4.608h146.795    c17.792,0,32.896,12.736,34.389,28.992c1.131,12.16-4.715,23.723-15.189,30.187c-3.264,2.005-5.205,5.632-5.056,9.493    s2.368,7.317,5.781,9.088c9.024,4.587,14.613,13.632,14.613,23.573C490.667,317.461,480.491,328.768,467.008,330.325z" fill="#FFF"/>
                                        </g></g><g><g><path d="M160,245.333c-5.888,0-10.667,4.779-10.667,10.667v192c0,5.888,4.779,10.667,10.667,10.667s10.667-4.779,10.667-10.667    V256C170.667,250.112,165.888,245.333,160,245.333z" fill="#FFF"/>
                                        	</g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g>
                                        </g><g></g><g></g><g></g><g></g></svg>
                                    </div>
                                    <div id="arrange_neg">
                                      <div id="div1" class="div_neg" ondrop="drop(event,2)" ondragover="allowDrop(event)">פסקה ראשונה</div>
                                      <div id="div2" class="div_neg" ondrop="drop(event,1)" ondragover="allowDrop(event)">פסקה שניה</div>
                                      <div id="div3" class="div_neg" ondrop="drop(event,0)" ondragover="allowDrop(event)">פסקה שלישית</div>
                                    </div>
                                    <input type="button" name="" value="סיימתי לסדר" class="user_finish_arrange" onclick="show_writing('negative_item')"  tabindex="0">

                                    <div id="negative_wrapper"></div>
                                </div>
                              </div>
                              <div id="wrap_writing">
                                                             <div id="writing_comp" >

                                                             </div>
                                                             <div id="wrap_before_text">
                                                             <textarea rows="10" cols="100" id="wrting_area">
                                                             </textarea>
                                                             <input type="button" name="" value="תבדקי אותי" id="to_check" onclick="check_text()"  tabindex="0">
                                                             </div>
                                                             <div id="wrap_after_text">

                                                           </div>
                                                     <!--        <div class="answer">
                                                                 הנה כמה דברים על הטקסט שכתבת <br>
                                                                  כמות המילים: <span id="words_count"></span><br>
                                                                 כמות המשפטים:  <span id="sentences_count"></span><br>
                                                                 ובממוצע, יש <span id="avg_words_in_sentence"></span> מילים במשפט<br>

                                                                 המילה הנפוצה ביותר היא: <span id="common_word"  class="repeat"></span> ונכתבה <span id="common_word_counter"></span>  נסה לשנות אותה במילים חלופיות <br>
                                                                   עוד מילים שהרבית להשתמש בהן הן: <span id="other_common_words"></span><br>
                                                                  השתמשת ב <span id="connectors_count" class="connectors0"></span> מילות קישור <br>
                                                                  ואם כבר מדברים על מילות קישור, השתמשת ב <span id="dif_connectors"></span> מילות קישור שונות <br>
                                                                  המשפט הארוך ביותר (מסומן בצהוב) מונה:  <span id="longes_sentenct" class="longest"></span> מילים - תנסה לקצר אותו או לפצל אותו לשני משפטים קצרים יותר<br>
                                                                  שים לב! המשפט שמסומן <span class = "complicated">בירוק</span> עשוי להיות מעט מסובך לקריאה, נסה לפשט אותו (למשל על ידי הוספת סימני פיסוק)
                                                             </div> -->
                                                           </div>

              </div>
             </div>
             <div id="timeline">
                  <img src="../imgs/shir.png" alt="" id="shir">
                  <div id="progressline" style="width:90%;float: right;">
                      <ul class="progress-indicator">
                        <li class="completed"> <span class="bubble"></span> כניסה </li>
                        <li> <span class="bubble"></span> בחירת נושא </li>
                        <li> <span class="bubble"></span> העלאת טיעונים </li>
                        <li> <span class="bubble"></span> סידור טיעונים </li>
                        <li> <span class="bubble"></span> פסקת פתיחה </li>
                        <li> <span class="bubble"></span> פסקות טיעון </li>
                        <li> <span class="bubble"></span> תיקונים </li>
                        <li> <span class="bubble"></span> סיום </li>
                      </ul>
                  </div>

           </div>

         </div>
           <div id="chatbot">
                 <div id="chat_data">
                    <div id="answers">
                    </div>
                 </div>
                 <div id="user_input_div">
                    <form id="user_input_form"  method="post">
                     <table id="form_wrapper">
                       <tr>
                         <td id="form_wrapper_1">
                             <textarea placeholder="כתוב כאן..."  spellcheck="true" id="user_input_text" rows="1" cols="1" onkeypress="keyboard_size_change(event)"></textarea>
                         </td>
                         <td id="form_wrapper_2">
                             <input type="button" name="" value="שלח" id="user_input_button" onclick="user_input()"  tabindex="0">
                         </td>
                       </tr>
                     </table>
                   </form>
                 </div>
           </div>
          </div>
  </body>
</html>
