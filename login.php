<?php
   require_once 'master.php';
   start_page();
   //TODO: this is nearly same as code from signup.php -- refactor to DRY
   $error = '';
   $user = '';
   $pass = '';
   if (isset($_SESSION['user'])) destroy_session();
   if (isset($_POST['user'])) {
      $user = sanitize_string($_POST['user']);
      $pass = sanitize_string($_POST['pass']);
      $hashed = hash_password($pass);
      if ($user == "" || $pass == "") {
         $error = "User name and password can't be blank.";
      } else {
         $result = submit_query("SELECT id FROM accounts WHERE user='$user' AND password='$hashed'");
         if ($result->num_rows != 1) {
            $error = "User name / password are not in my database.";
         } else {
            ?>
            <p>Welcome, redirecting you to the game...</p>
            <script type="text/javascript">location.href = 'meta.php';</script>
            <?php
            $_SESSION['user'] = $user;
            $result->data_seek(0);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $user_id = $row['id'];
            $_SESSION['user_id'] = $user_id;
         }
      }
   }
?>
<body>
   <form method='post' action='login.php'>
   <?php
      if ($error != "") {
         echo "<h1>$error</h1><br>";
      }
   ?>
   <h3>And who do you think you are?</h3>
   <span class='fieldname'>Username</span>
   <input type='text' maxlength='32' name='user' value='<?= $user ?>'>
   <span id='info'></span><br>
   <span class='fieldname'>Password</span>
   <input type='text' maxlength='32' name='pass' value='<?= $pass ?>'><br>
   <span class='fieldname'>&nbsp;</span>
   <input type='submit' value='Log in'>

   <p>Oops, I really wanted to <a href=signup.php>sign up</a> instead of log in.</p>
</body>
</html>
