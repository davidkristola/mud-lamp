<?php
   require_once 'site_specific.php';
   $db_connection = new mysqli($db_host, $db_user, $db_pass, $db_name);
   if ($db_connection->connect_error) die($db_connection->connect_error);
   //
   class EntityTypes {
      const CHARACTER = 0;
      const PLACE = 1;
      const OBJECT = 2;
   }

   function create_table($name, $query) {
      submit_query("CREATE TABLE IF NOT EXISTS $name($query)");
   }
   function submit_query($query) {
      global $db_connection;
      $result = $db_connection->query($query);
      if (!$result) die($db_connection->error);
      return $result;
   }
   function hash_password($password) {
      global $db_pre_salt;
      global $db_post_salt;
      global $db_pw_hash;
      return hash($db_pw_hash, "$db_pre_salt$password$db_post_salt");
   }
   function sanitize_string($the_string) {
      global $db_connection;
      $untagged = strip_tags($the_string);
      $unhtmled = htmlentities($untagged);
      $unslashed = stripslashes($unhtmled);
      return $db_connection->real_escape_string($unslashed);
   }
   function gp_has($name) {
      return (isset($_GET[$name]) || isset($_POST[$name]));
   }
   function gp_get($name) {
      if (isset($_GET[$name])) return sanitize_string($_GET[$name]);
      return sanitize_string($_POST[$name]);
   }
   function get_first_location() {
      $query = "SELECT id FROM entities WHERE type=" . EntityTypes::PLACE . " ORDER BY id LIMIT 1";
      $result = submit_query($query);
      $result->data_seek(0);
      $row = $result->fetch_array(MYSQLI_ASSOC);
      return $row['id'];
   }
   function add_action($etime, $cid, $eloc, $action, $extra) {
      $query = "INSERT INTO events (etime, esrc, eloc, action, eex) VALUES('$etime', $cid, $eloc, '$action', '$extra')";
      $result = submit_query($query);
      return $result;
   }
   function add_locator($what, $where) {
      $query = "INSERT INTO locators (state, kind, what, location, x, y, z) VALUES(1, 1, $what, $where, 1, 1, 1)";
      $result = submit_query($query);
      return $result;
   }
   function place_in_room($what, $where) {
      $query = "SELECT name FROM entities WHERE id = $where AND type = " . EntityTypes::PLACE;
      $result = submit_query($query);
      if ($result->num_rows == 1) {
         add_locator($what, $where);
         add_action(date("Y-m-d H:i:s"), $what, $where, "appears", "out of thin air");
      } else {
         echo "<h1>Room $where not found!</h1>";
      }
   }
   function get_name($id) {
      $query = "SELECT name FROM entities WHERE id=$id";
      $result = submit_query($query);
      $result->data_seek(0);
      $row = $result->fetch_array(MYSQLI_ASSOC);
      return $row['name'];
   }

   // Turn query results into a table
   function echo_query($query, $columns) {
      $result = submit_query($query);
      $number_of_fields = $result->num_rows;
      echo_header($columns);
      for ($field_index = 0; $field_index < $number_of_fields; ++$field_index) {
         $result->data_seek($field_index);
         echo_row($result->fetch_array(MYSQLI_NUM), count($columns));
      }
      echo_footer();
   }
   function echo_header($columns) {
      echo '<table border="1"><tr>';
      foreach($columns as $col) {
         echo_el($col, "th");
      }
      echo "</tr>" . PHP_EOL;
   }
   function echo_row($row, $count) {
      echo "<tr>" . PHP_EOL;
      for ($entry_index = 0; $entry_index < $count; ++$entry_index) {
         echo_el($row[$entry_index], "td");
      }
      echo "</tr>" . PHP_EOL;
   }
   function echo_el($data, $el) {
      echo "<$el>$data</$el>" . PHP_EOL;
   }
   function echo_footer() {
      echo "</table>" . PHP_EOL;
   }
?>
