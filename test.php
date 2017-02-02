<!DOCTYPE html>
<html>
   <head>
      <title>MUD Test Page</title>
   </head>
   <body>
      <p>Running Tests...</p>

<?php
   require_once 'db.php';
   $scambled = hash_password('Qwerty63');
   echo "<p>Qwerty63 hashes to $scambled</p>";
   $scambled = hash_password('Qwerty64');
   echo "<p>Qwerty64 hashes to $scambled</p>";
   $scambled = hash_password('Qwerty65');
   echo "<p>Qwerty65 hashes to $scambled</p>";
   $cleaned = sanitize_string('<a>href=setup.php\n</a>');
   echo "<p>The cleaned up string is ---$cleaned---</p>";
?>
      <p>...done</p>
   </body>
</html>
