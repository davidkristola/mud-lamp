<?php
   // This page has four jobs:
   //   1. construct a new thing (and POST to 2)
   //   2. add the new thing (then auto-GET to 3)
   //   3. edit an existing thing (here via GET, post to 3)
   //   4. update the existing thing (and re-GET to 3)
   require_once 'master.php';
   start_page();
   if (!$logged_in) die("This page is restricted to logged in users. <a href=index.php>start over</a>");

   echo "<body>" . PHP_EOL;

   class ObjectCreationPaths extends CreationPaths {
      public $new_text = "Enter the details for a new object.";
      public $edit_text = "Edit the details for an existing object.";
      public $insert_text = "Inserting new object. Will transform to Edit...";
      public $update_text = "Updating thing. Will transform to Edit...";
      public $type = EntityTypes::OBJECT;
      public $post_to = "thing.php";

      function additional_insert() {
         if (gp_has('room')) {
            place_in_room($this->id, gp_get('room'));
         }
      }

      function additional_post() {
         $this->post_room();
      }
      function post_room() {
         if (CreationPaths::NEW_ITEM == $this->which_path) {
            $a_room = get_first_location();
            $this->field("Room");
            $this->text("room", "$a_room");
            $this->br();
         }
      }

   }

   $path = new ObjectCreationPaths($user_id);
   $path->spin_page();

   echo_query("SELECT id, name, description FROM entities WHERE type = " . EntityTypes::PLACE, array("ID", "Name", "Description"));
?>
   <p><a href=meta.php>Go back</a> instead of doing this.</p>
</body>
</html>
