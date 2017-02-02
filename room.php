<?php
   // This page has three jobs:
   //   1. construct a new room (and POST to 2)
   //   2. add the new room (then auto-GET to 3)
   //   3. edit an existing room (here via GET, post to 3)
   //   4. update the existing room (and re-GET to 3)
   require_once 'master.php';
   start_page();
   if (!$logged_in) die("This page is restricted to logged in users. <a href=index.php>start over</a>");

   echo "<body>" . PHP_EOL;

   class RoomCreationPaths extends CreationPaths {
      public $new_text = "Let the construction begin!";
      public $edit_text = "Edit the details for an existing room.";
      public $insert_text = "Inserting new room. Will transform to Edit...";
      public $update_text = "Updating room. Will transform to Edit...";
      public $type = EntityTypes::PLACE;
      public $post_to = "room.php";
   }

   $path = new RoomCreationPaths($user_id);
   $path->spin_page();

?>
   <p><a href=meta.php>Go back</a> instead of doing this.</p>
</body>
</html>
