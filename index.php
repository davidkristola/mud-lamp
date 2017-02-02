<?php
   require_once 'master.php';
   start_page();
   if ($logged_in) {
      echo "<body><p>Welcome $user!</p>";
?>
   <p>You are logged in.</p>
   <p>First step to <a href=meta.php>playing</a>.</p>
<?php
   } else {
      echo "<body><p>Welcome Friend!</p>";
?>
   <p>You must be logged in to play.</p>
   <p><a href=login.php>Log In</a></p>
   <p><a href=signup.php>Sign Up</a></p>
<?php
   }
?>
</body>
</html>
