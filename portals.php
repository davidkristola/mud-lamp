<?php
   // This page has four jobs:
   //   1. construct a new portal (and POST to 2)
   //   2. add the new portal (then auto-GET to 3)
   //   3. edit an existing portal (here via GET, post to 3)
   //   4. update the existing portal (and re-GET to 3)
   require_once 'master.php';
   start_page();
   if (!$logged_in) die("This page is restricted to logged in users. <a href=index.php>start over</a>");
   $which_path = CreationPaths::ERROR;
?>
<body>
<?php
   $state = 0;
   $direction = 0;
   $source = 0;
   $destination = 0;
   $selected_state = array('', '', '', '');
   $selected_dir = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',);

   if (gp_has('id')) {
      $id = gp_get('id');
      // EDIT_ITEM or UPDATE_ITEM
      if (gp_has('state')) {
         $which_path = CreationPaths::UPDATE_ITEM;

         $state = gp_get('state');
         $direction = gp_get('direction');
         $source = gp_get('source');
         $destination = gp_get('destination');

         $result = submit_query("UPDATE portals SET state=$state WHERE id = $id");
         $result = submit_query("UPDATE portals SET direction=$direction WHERE id = $id");
         $result = submit_query("UPDATE portals SET source=$source WHERE id = $id");
         $result = submit_query("UPDATE portals SET destination=$destination WHERE id = $id");
         echo "<script>location.href = 'portals.php?id=$id';</script>";
      } else {
         echo "<h1>Update</h1>";
         $which_path = CreationPaths::EDIT_ITEM;
         $result = submit_query("SELECT * FROM portals WHERE id = $id");
         $result->data_seek(0);
         $row = $result->fetch_array(MYSQLI_ASSOC);

         $state = $row['state'];
         $selected_state[$state] = 'selected';
         $direction = $row['direction'];
         $selected_dir[$direction+2] = 'selected';
         $source = $row['source'];
         $destination = $row['destination'];
      }
   } else {
      // NEW_ITEM or INSERT_ITEM
      if (gp_has('state')) {
         $which_path = CreationPaths::INSERT_ITEM;
         $state = gp_get('state');
         $direction = gp_get('direction');
         $source = gp_get('source');
         $destination = gp_get('destination');
         // TODO validate (name must be unique!!!)
         $query = "INSERT INTO portals (state, direction, source, destination) VALUES($state, $direction, $source, $destination)";
         echo "<p> query: $query</p>";
         $result = submit_query($query);

         if ($direction < 0) {
            if ($direction == -1) {
               $opposite_direction = -2;
            } else {
               $opposite_direction = -1;
            }
         } else {
            $opposite_direction = ($direction + 8);
            if ($opposite_direction > 15) {
               $opposite_direction = $opposite_direction - 16;
            }
         }
         $query = "INSERT INTO portals (state, direction, source, destination) VALUES($state, $opposite_direction, $destination, $source)";
         echo "<p> query: $query</p>";
         $result = submit_query($query);
         $insert_id = $db_connection->insert_id;

         //echo "<script>location.href = 'portals.php?id=$insert_id';</script>";
      } else {
         $which_path = CreationPaths::NEW_ITEM;
         echo "<h1>Create</h1>";
      }
   }




?>

   <form method='post' action='portals.php'>

   <?php
      if (CreationPaths::EDIT_ITEM == $which_path) {
         echo "<input type='hidden' name='id' value='" . $id . "'>";
      }
   ?>

   <span class='fieldname'>State</span>
   <select name='state'>
      <option value=0 <?=$selected_state[0]?> >Unclosable</option>
      <option value=1 <?=$selected_state[1]?> >Open</option>
      <option value=2 <?=$selected_state[2]?> >Closed</option>
      <option value=3 <?=$selected_state[3]?> >Locked</option>
   </select>
   <br>

   <span class='fieldname'>Direction</span>
   <select name='direction'>
      <option value=-2 <?=$selected_dir[0]?> >Down</option>
      <option value=-1 <?=$selected_dir[1]?> >Up</option>
      <option value=0 <?=$selected_dir[2]?> >North</option>
      <option value=1 <?=$selected_dir[3]?> >NNE</option>
      <option value=2 <?=$selected_dir[4]?> >North East</option>
      <option value=3 <?=$selected_dir[5]?> >ENE</option>
      <option value=4 <?=$selected_dir[6]?> >East</option>
      <option value=5 <?=$selected_dir[7]?> >ESE</option>
      <option value=6 <?=$selected_dir[8]?> >South East</option>
      <option value=7 <?=$selected_dir[9]?> >SSE</option>
      <option value=8 <?=$selected_dir[10]?> >South</option>
      <option value=9 <?=$selected_dir[11]?> >SSW</option>
      <option value=10 <?=$selected_dir[12]?> >South West</option>
      <option value=11 <?=$selected_dir[13]?> >WSW</option>
      <option value=12 <?=$selected_dir[14]?> >West</option>
      <option value=13 <?=$selected_dir[15]?> >WNW</option>
      <option value=14 <?=$selected_dir[16]?> >North West</option>
      <option value=15 <?=$selected_dir[17]?> >NNW</option>
   </select>
   <br>

   <span class='fieldname'>Source</span><br>
   <input type='text' name='source' value='<?=$source?>'>
   <br>

   <span class='fieldname'>Destination</span><br>
   <input type='text' name='destination' value='<?=$destination?>'>
   <br>

   <input type='submit' value='Submit'>
   <br>
   <input type='reset' value='Reset form'>
   <br>

   </form>

<?php
   echo_query("SELECT id, name, description FROM entities WHERE type = " . EntityTypes::PLACE, array("ID", "Name", "Description"));
?>

   <p><a href=meta.php>Go back</a> instead of doing this.</p>
</body>
</html>
