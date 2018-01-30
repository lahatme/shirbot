<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../css/log-css.css">
    <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet">
    <link href="https://afeld.github.io/emoji-css/emoji.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title></title>
    <script type="text/javascript">
      function login(){
        document.getElementById('login').style.display = 'block';
        document.getElementById('btns').style.display = 'none';
        document.getElementsByClassName('bot_msg')[0].innerHTML = " טוב שחזרת! נותר רק להתחבר ולהתחיל לכתוב ביחד " + "<i class='em em-smile'></i>"
      }

      function register(){
        document.getElementById('register').style.display = 'block';
        document.getElementById('btns').style.display = 'none';
        document.getElementsByClassName('bot_msg')[0].innerHTML = "עזור לי להכיר אותך יותר "+"<i class='em em-couple'></i>";
      }
      $(function () {
         $('#login').on('submit', function (e) {
           e.preventDefault();
           $.ajax({
             type: 'post',
             url: './user_login.php',
             data: $('#login').serialize(),
             success: function (msg) {
               if(msg==1){
                 window.location.href = "./index.php";
               }
               else{document.getElementsByClassName('bot_msg')[0].innerHTML = " נראה לי שטעית בשם המשתמש או בסיסמה " + " <i class='em em-face_with_monocle'></i>"}
           }});});});

             $(function () {
                $('#register').on('submit', function (e) {
                  e.preventDefault();
                  if(is_form_validate()){
                  $.ajax({
                    type: 'post',
                    url: './register.php',
                    data: $('#register').serialize(),
                    success: function (msg) {
                      if(msg == "known"){
                          document.getElementsByClassName('bot_msg')[0].innerHTML = "שם המשתמש שבחרת תפוס" +"<i class=em em-juggling'></i>"
                          document.getElementsByName('username2')[0].style.borderColor = "red";
                      }
                      else{
                          if(msg == "success"){
                            window.location.href = "./index.php";
                          }
                          else{
                            document.getElementsByClassName('bot_msg')[0].innerHTML ="משום מה יש שגיאה בהרשמה" +"<i class=em em-anguished'></i>";
                            }
                      }

                    }});
                  }
                  });});

            function is_form_validate(){

              var name = document.getElementsByName('name')[0];
              var lastname = document.getElementsByName('lastname')[0];
              var username = document.getElementsByName('username2')[0];
              var password = document.getElementsByName('password2')[0];

              var fields = [name,lastname,username,password];
              for(i = 0; i<fields.length; i++)
              {
                  if(fields[i].value.length<2) {
                    fields[i].style.borderColor = "red";
                    document.getElementsByClassName('bot_msg')[0].innerHTML = "כתבת דברים קצרים מדי " + " <i class='em em-clown_face'> </i> ";
                  }
                  else fields[i].style.borderColor = "#52A8FF";
              }

              var valid_counter = 0;
              for(i = 0; i<fields.length; i++)
              {
                var bordercolor = fields[i].style.borderColor;
                if(bordercolor == "rgb(82, 168, 255)")
                valid_counter++;
              }

              if(valid_counter == fields.length)return true;
              else return false;
            }
    </script>
  </head>
  <body>

        <div class="chat_bubble">
          <div class="bot_avatar"></div>
          <div class="bot_msg">היי, אני שירבוט ואני יכולה לעזור לך להשתפר בכתיבה.  שנתחיל?</div>
        </div>

        <div id="btns">
              <div class="user_btn" onclick="register()">משתמש חדש</div>
              <div class="user_btn" onclick="login()">משתמש קיים </div>
        </div>

        <form class="login" id="login" action="user_login.php" method="post">
              <input type="text" name="username" value="" placeholder="שם משתמש" id="username" class="input"><br>
              <input type="password" name="password" value="" placeholder="סיסמה" id="password" class="input"><br>
              <input type="submit" name="" value="התחבר" class="user_btn" id="submit">
        </form>

        <form class="register" id="register" action="register.php" method="post">
          <label>
              <input type="radio" name="gender" value="male" checked>
              <i class="em em-boy" style="background-size: 40px;height: 50px;width: 50px;"></i>
          </label>
          <label>
              <input type="radio" name="gender" value="female">
              <i class="em em-girl" style="background-size: 40px;height: 50px;width: 50px;"></i><br>
          </label><br>
          <input type="text" name="name" value="" placeholder="שם פרטי" class="input"><br>
          <input type="text" name="lastname" value="" placeholder="שם משפחה" class="input"><br>
          <input type="text" name="username2" value="" placeholder="שם משתמש" class="input"><br>
          <input type="password" name="password2" value="" placeholder="סיסמה" class="input"><br>
          <button type="submit" name="button" class="user_btn">הירשם <i class="em em-point_left"></i></button>

        </form>




  </body>
</html>
