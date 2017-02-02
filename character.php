<?php
   require_once 'master.php';
   start_page();
   echo "<body>";
   if (!$logged_in) die("This page is restricted to logged in users. <a href=index.php>start over</a>");
   class Paths {
      const ERROR = 0;
      const NEW_CHAR = 1;
      const UPDATE_CHAR = 4;
      const SUBMIT_CHAR = 2;
      const EDIT_CHAR = 3;
   }
   $which_version = CreationPaths::ERROR;
   $name = '';
   $maker = $user_id;
   $race = 'Human';
   $description = '';
   $result = '';
   $selected = array('Human' => '', 'Hobbit' => '', 'Elf' => '', 'Dwarf' => '');

   if (gp_has('name')) {
      $name = gp_get('name');
      if (gp_has('id')) {
         $which_version = CreationPaths::UPDATE_ITEM;
      } else {
         $which_version = CreationPaths::INSERT_ITEM;
      }
   } elseif (gp_has('id')) {
      $which_version = CreationPaths::EDIT_ITEM;
   } else {
      $which_version = CreationPaths::NEW_ITEM;
   }

   switch ($which_version):
      case CreationPaths::NEW_ITEM:
         echo "<p>CreationPaths::NEW_ITEM</p>";
         echo '<h3>Enter the details for a new character.</h3>';
         break;
      case CreationPaths::EDIT_ITEM:
         echo "<p>CreationPaths::EDIT_ITEM</p>";
         echo '<h3>Edit the details for an existing character.</h3>';
         $id = gp_get('id');
         $result = submit_query("SELECT * FROM entities WHERE id=$id");
         if ($result->num_rows == 1) {
            $result->data_seek(0);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $name = $row['name'];
            $description = $row['description'];

            $result = submit_query("SELECT * FROM attributes WHERE entity=$id AND attr='Race'");
            $result->data_seek(0);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $race = $row['value'];
            $selected[$race] = 'selected';
            echo "<p>Race=" . $race . "</p>";
         } else {
            echo "<h1>Error: Query for character '$name' returned " . $result->num_rows . " rows (expected 1)!</h1>";
            echo "<br><a href=meta.php>go back</a><br>";
            die("<h1>Could not edit character.</h1>");
         }
         break;
      case CreationPaths::INSERT_ITEM:
         echo "<p>CreationPaths::INSERT_ITEM</p>";
         if (isset($_POST['race']) && isset($_POST['description'])) {
            $name = sanitize_string($_POST['name']);
            $race = $_POST['race'];
            $description = sanitize_string($_POST['description']);

            //TODO: validate the character
            $query = "INSERT INTO entities (name, maker, type, description) VALUES('$name', $user_id, " . EntityTypes::CHARACTER . ", '$description')";
            echo "<p>query=$query</p>";
            $result = submit_query($query);
            $insert_id = $db_connection->insert_id;
            $query = "INSERT INTO attributes (entity, attr, value) VALUES($insert_id, 'Race', '$race')";
            echo "<p>query=$query</p>";
            $result = submit_query($query);
            //TODO: check result

            redirect_to_meta();
         } else {
            echo "<a href=meta.php>go back</a><br>";
            die("INSERT_ITEM: missing required post field(s)");
         }
         break;
      case CreationPaths::UPDATE_ITEM:
         echo "<p>CreationPaths::UPDATE_ITEM</p>";
         if (isset($_POST['race']) && isset($_POST['description'])) {
            $name = gp_get('name');
            $race = gp_get('race');
            $description = gp_get('description');
            $id = gp_get('id');

            //TODO: validate the character
            $query = "UPDATE attributes SET value='$race' WHERE entity=$id AND attr='Race'";
            echo "<p>query=$query</p>";
            $result = submit_query($query);
            $query = "UPDATE entities SET description='$description' WHERE id=$id";
            echo "<p>query=$query</p>";
            $result = submit_query($query);
            //TODO: check result

            redirect_to_meta();
         } else {
            echo "<a href=meta.php>go back</a><br>";
            die("UPDATE_ITEM: missing required post field(s)");
         }
         break;
      default:
      case CreationPaths::ERROR:
         echo "<a href=meta.php>go back</a><br>";
         die("Something went horribly wrong!");
         break;
   endswitch;

   function redirect_to_meta() {
      ?>
      <script type="text/javascript">location.href = 'meta.php';</script>
      <body>
      <?php
   }


?>
<body>
<div class='main'>
   <form method='post' action='character.php'>

   <?php
      if (CreationPaths::EDIT_ITEM == $which_version) {
         echo "<input type='hidden' name='id' value='" . $id . "'>";
      } // new items don't have an id yet
   ?>

   <span class='fieldname'>Name</span>
   <?php
      if (CreationPaths::EDIT_ITEM == $which_version) {
         echo "<input type='hidden' name='name' value='" . $name . "'>";
         echo " " . $name;
      } else {
         echo "<input type='text' maxlength='32' name='name' value='" . $name . "'>";
      }
   ?>
   <br>

   <span class='fieldname'>Race</span>
   <select name='race'>
   <?php
      foreach($selected as $val => $sel) {
         echo "<option value='$val' $sel >$val</option>" . PHP_EOL;
      }
   ?>
   </select>
   <br>

   <span class='fieldname'>Description</span><br>
   <textarea maxlength='200' name='description' wrap='soft' rows='8' cols="50"><?= $description ?></textarea>
   <br>

   <input type='submit' value='Submit'>
   <br>
   <input type='reset' value='Reset form'>
   <br>
   <p><a href=meta.php>Go back</a> instead of doing this.</p>
</div>
</body>
</html>

