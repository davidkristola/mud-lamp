<?php
   header('Content-Type: text/xml');
   session_start();
   date_default_timezone_set('UTC');
   require_once 'db.php';
   if (!gp_has('id')) die("no id");
   if (!gp_has('action')) die("no action");

   $id = gp_get('id'); // item or character
   $action = gp_get('action');
   // Action could be:
   //  1. enter (create a new locator),
   //  2. move (update an existing locator), or
   //  3. exit (delete a locator)

   switch ($action) {
      case "enter":
         $where = gp_get('where');
         $query = "INSERT INTO locators (state, kind, what, location, x, y, z) VALUES(1, 1, $id, $where, 1, 1, 1)";
         break;
      case "move":
         $destination = gp_get('destination');
         $query = "UPDATE locators SET location=$destination WHERE what=$id";
         break;
      case "exit":
         $query = "DELETE FROM locators WHERE what=$id";
         break;

      case "take":
         $kind = gp_get('kind');
         $who = gp_get('who');
         $query = "UPDATE locators SET location=$who, kind=$kind WHERE what=$id";
         break;

      case "put":
         $where = gp_get('where');
         $query = "UPDATE locators SET location=$where, kind=1 WHERE what=$id";
         break;

      default:
         die('bad action');
         break;
   }
   $result = submit_query($query);

//id
//state TINYINT NOT NULL,
//kind TINYINT UNSIGNED NOT NULL,
//what INT UNSIGNED NOT NULL,
//location INT UNSIGNED NOT NULL,
//x INT,
//y INT,


?>
<status>Good</status>
