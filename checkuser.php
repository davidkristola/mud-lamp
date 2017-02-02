<?php
   require_once 'db.php';
   if (isset($_POST['user'])) {
      $user = sanitize_string($_POST['user']);
      $result = submit_query("SELECT * FROM accounts WHERE user='$user'");
      if ($result->num_rows) {
         echo "<span class='taken'> The user name '$user' is already in use.";
      } else {
         echo "<span class='available'> The user name '$user' is available.";
      }
   }
?>
