<?php
   require_once 'master.php';
   start_page();
   $error = '';
   $user = '';
   $pass = '';
   if (isset($_SESSION['user'])) destroy_session();
   if (isset($_POST['user'])) {
      $user = sanitize_string($_POST['user']);
      $pass = sanitize_string($_POST['pass']);
      if ($user == "" || $pass == "") {
         $error = "User name and password can't be blank.<br><br>";
      } else {
         $result = submit_query("SELECT * FROM accounts WHERE user='$user'");
         if ($result->num_rows) {
            $error = "User name exists.<br><br>";
         } else {
            $hashed = hash_password($pass);
            $result = submit_query("INSERT INTO accounts (user, password, start) VALUES('$user', '$hashed', CURDATE())");
            $_SESSION['user'] = $user;
            $user_id = $db_connection->insert_id;
            $_SESSION['user_id'] = $user_id;
            ?>
            <script type="text/javascript">location.href = 'meta.php';</script>
            <?php
         }
      }
   }
?>
<body>
<script type="text/javascript">
   document.write("Today is " + Date());
   function get_object(object_name) {
      if (typeof object_name == 'object') return object_name
      else return document.getElementById(object_name)
   }
   function check_user(user) {
      if (user.value == '') {
         get_object('info').innerHTML = ''
         return
      }
      parameters = "user=" + user.value
      request = new get_new_ajax_request()
      request.open("POST", "checkuser.php", true)
      request.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
      request.setRequestHeader("Content-length", parameters.length)
      request.setRequestHeader("Connection", "close")
      request.onreadystatechange = function() {
         if (this.readyState == 4)
            if (this.status == 200)
               if (this.responseText != null)
                  get_object('info').innerHTML = this.responseText
      }
      request.send(parameters)
   }
   function get_new_ajax_request() {
      try { var request = new XMLHttpRequest() }
      catch (e1) {
         try { request = new ActiveXObject("Msxml2.XMLHTTP") }
         catch (e2) {
            try { request = new ActiveXObject("Microsoft.XMLHTTP") }
            catch (3) {
               request = false
            }
         }
      }
      return request
   }
</script>
<div class='main'>
   <h3>Gimme data!</h3>
   <form method='post' action='signup.php'><?= $error ?>
   <span class='fieldname'>Username</span>
   <input type='text' maxlength='32' name='user' value='<?= $user ?>'
          onblur='check_user(this)'>
   <span id='info'></span><br>
   <span class='fieldname'>Password</span>
   <input type='text' maxlength='32' name='pass' value='<?= $pass ?>'><br>
   <span class='fieldname'>&nbsp;</span>
   <input type='submit' value='Sign up'>
   <p>Oops, I really wanted to <a href=login.php>log in</a> instead of sign up.</p>
</div>
</body>
</html>
