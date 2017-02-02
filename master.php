<?php
   session_start();
   date_default_timezone_set('UTC');
   //echo "<!doctype html>\n<html><head>";
   //if (isset($page_title)) {
   //   echo "<title>$page_title</title></head>";
   //} else {
   //   echo "<title>MUD</title></head>";
   //}
   require_once 'db.php';
   if (isset($_SESSION['user'])) {
      $user = $_SESSION['user'];
      $user_id = $_SESSION['user_id'];
      $logged_in = TRUE;
   } else {
      $logged_in = FALSE;
   }

   function destroy_session() {
      $_SESSION=array();
      if (session_id() != "" || isset($_COOKIE[session_name()])) {
         setcookie(session_name(), '', time()-2592000, '/');
      }
      session_destroy();
   }
   function start_page($title="MUD") {
      echo "<!doctype html>" . PHP_EOL;
      echo "<html><head><title>$title</title></head>" . PHP_EOL;
   }
   function logout_button() {
      echo "<button onclick='redirect_to_logout()'>log out</button>" . PHP_EOL;
   }

   class CreationPaths {
      const ERROR = 0;
      const NEW_ITEM = 1;
      const INSERT_ITEM = 2;
      const EDIT_ITEM = 3;
      const UPDATE_ITEM = 4;

      public $which_path;
      public $maker;
      public $name = "";
      public $description = "";
      public $id = "";
      // Customize these in the subclasses
      public $new_text = "";
      public $edit_text = "";
      public $insert_test = "";
      public $update_text = "";
      public $type = EntityTypes::OBJECT;
      public $post_to = "";

      function __construct($maker) {
         $this->maker = $maker;
         if (gp_has('path')) {
            $option = gp_get('path');
            switch ($option) {
               case "new":
                  $this->which_path = CreationPaths::NEW_ITEM;
                  break;
               case 'insert':
                  $this->which_path = CreationPaths::INSERT_ITEM;
                  break;
               case 'edit':
                  $this->which_path = CreationPaths::EDIT_ITEM;
                  break;
               case 'update':
                  $this->which_path = CreationPaths::UPDATE_ITEM;
                  break;
               default:
                  $this->which_path = CreationPaths::ERROR;
                  break;
            }
         } else {
            $this->which_path = CreationPaths::ERROR;
         }
      }

      function validate() {
         switch ($this->which_path) {
            case CreationPaths::NEW_ITEM:
               break;
            case CreationPaths::INSERT_ITEM:
               $this->validate_insert();
               break;
            case CreationPaths::EDIT_ITEM:
               $this->validate_edit();
               break;
            case CreationPaths::UPDATE_ITEM:
               $this->validate_update();
               break;
            default:
               die("Can't validte an erroneous path");
               break;
         }
      }
      function validate_edit() {
         if (!gp_has('id')) die('Missing id');
      }
      function validate_insert() {
         if (!gp_has('name')) die('Missing name');
         if (!gp_has('description')) die('Missing description');
         if (!gp_has('definite')) die('Missing definite');
      }
      function validate_update() {
         if (!gp_has('id')) die('Missing id');
         $this->validate_insert();
      }

      function process_path() {
         switch ($this->which_path) {
            case CreationPaths::NEW_ITEM:
               $this->on_new();
               break;
            case CreationPaths::INSERT_ITEM:
               $this->on_insert();
               break;
            case CreationPaths::EDIT_ITEM:
               $this->on_edit();
               break;
            case CreationPaths::UPDATE_ITEM:
               $this->on_update();
               break;
            default:
               die("Can't process an erroneous path");
               break;
         }
      }
      function on_new() {
         $this->element("h3", $this->new_text);
      }
      function on_insert() {
         $this->element("h3", $this->insert_text);
         $this->name = gp_get('name');
         $this->description = gp_get('description');
         $this->definite = gp_get('definite');
         $name_maker = $this->c($this->ticked($this->name), $this->maker);
         $type_des = $this->c($this->type, $this->ticked($this->description));
         $list_a = $this->c($name_maker, $type_des);
         $definite_xkey = $this->c($this->definite, '0');
         $list = $this->c($list_a, $definite_xkey);
         $query = "INSERT INTO entities (name, maker, type, description, definite, x_key) VALUES($list)";
         $this->element("p", "query=$query");
         $result = submit_query($query);
         global $db_connection;
         $this->id = $db_connection->insert_id;
         $this->which_path = CreationPaths::EDIT_ITEM;
         $this->additional_insert();
      }
      function additional_insert() {
      }
      function on_edit() {
         $this->element("h3", $this->edit_text);
         $this->id = gp_get('id');
         $result = submit_query("SELECT * FROM entities WHERE id=$this->id AND type=" . $this->type);
         if ($result->num_rows == 1) {
            $result->data_seek(0);
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->definite = $row['definite'];
         } else {
            die('The id must match a single object in the database.');
         }
      }
      function on_update() {
         $this->element("h3", $this->update_text);
         $this->id = gp_get('id');
         $this->name = gp_get('name');
         $this->description = gp_get('description');
         $this->definite = gp_get('definite');
         $query = "UPDATE entities SET description=" . $this->ticked($this->description) . " WHERE id=" . $this->id;
         $result = submit_query($query);
         $query = "UPDATE entities SET definite=" . $this->ticked($this->definite) . " WHERE id=" . $this->id;
         $result = submit_query($query);
         $this->which_path = CreationPaths::EDIT_ITEM;
      }

      function post_next_path() {
         if (CreationPaths::NEW_ITEM == $this->which_path) {
            $this->hidden("path", "insert");
         }
         if (CreationPaths::EDIT_ITEM == $this->which_path) {
            $this->hidden("path", "update");
         }
      }

      function echo_form() {
         $this->form_start($this->post_to);
         $this->post_next_path();
         $this->post_id();
         $this->post_name();
         $this->post_definite();
         $this->post_description();
         $this->additional_post();
         $this->form_end();
      }

      function spin_page() {
         $this->validate();
         $this->process_path();
         $this->echo_form();
      }

      function bracket($tag, $extra="") { echo "<$tag" . $this->sif($extra) . ">" . PHP_EOL; }
      function br() { $this->bracket("br"); }
      function ticked($x) { return "'" . $x . "'"; }
      function sif($x) { // prepend space if not empty
         if ("" == $x) return "";
         if (" " == $x[0]) return $x;
         return " " . $x;
      }
      function kv($k, $v) { return " " . $k . "=" . $this->ticked($v); }
      function element($tag, $body, $extra="") { echo "<$tag" . $this->sif($extra) . ">$body</$tag>" . PHP_EOL; }
      function hidden($name, $value) {
         $this->bracket("input", $this->kv("type", "hidden") . $this->kv("name", $name) . $this->kv("value", $value));
      }
      function text($name, $value) {
         $extra = $this->kv("type", "text") . $this->kv("maxlength", "32") . $this->kv("name", $name) . $this->kv("value", $value);
         $this->bracket("input", $extra);
      }
      function field($name) {
         $this->element("span", $name . ":", $this->kv("class", "fieldname"));
      }
      function description($d="") {
         $extra = $this->kv("maxlength", "200") . $this->kv("name", "description") . $this->kv("wrap", "soft") . $this->kv("rows", "8") . $this->kv("cols", "50");
         $this->element("textarea", $d, $extra);
      }
      function c($a, $b) {
         return $a . ", " . $b;
      }
      function form_start($dest) {
         $this->bracket("form", $this->kv("method", "post") . $this->kv("action", $dest));
      }
      function form_end() {
         $this->br();
         $this->bracket("input", $this->kv("type", "submit") . $this->kv("value", "Submit"));
         $this->br();
         $this->bracket("input", $this->kv("type", "reset") . $this->kv("value", "Reset form"));
         $this->br();
      }

      function post_id() {
         if ("" != $this->id) {
            $this->hidden("id", $this->id);
         }
      }
      function post_name() {
         $this->field("Name");
         if (gp_has('name')) {
            echo " " . $this->name . PHP_EOL;
            $this->hidden("name", $this->name);
         } else {
            $this->text("name", $this->name);
         }
      }
      function post_description() {
         $this->br();
         $this->field("Description");
         $this->br();
         $this->description($this->description);
         $this->br();
      }
      function post_definite() {
         if ($this->definite == "0") {
            $d = " checked";
            $nd = "";
         } else {
            $d = "";
            $nd = " checked";
         }
         $this->br();
         $this->bracket("input", $this->kv("type", "radio") . $this->kv("name", "definite") . $this->kv("value", "0") . $d);
         echo "indefinite (a or an)";
         $this->br();
         $this->bracket("input", $this->kv("type", "radio") . $this->kv("name", "definite") . $this->kv("value", "1") . $nd);
         echo "definite (the)";
         $this->br();
      }

      function additional_post() {
      }

   }
?>
<script>
   "use strict";
   function redirect_to_logout() {
      window.location.href="logout.php";
   }
   function pad_to_two_digits(value) { // positive numbers only
      if (0 <= value && value < 10) {
         return "0" + value.toString();
      }
      return value.toString();
   }

   Date.prototype.toMysqlFormat = function() {
      return this.getUTCFullYear() + "-" +
         pad_to_two_digits(1 + this.getUTCMonth()) + "-" +
         pad_to_two_digits(this.getUTCDate()) + " " +
         pad_to_two_digits(this.getUTCHours()) + ":" +
         pad_to_two_digits(this.getUTCMinutes()) + ":" +
         pad_to_two_digits(this.getUTCSeconds());
   };

   function get_now() {
      var current_time = new Date();
      return current_time.toMysqlFormat();
   }

//   function my_power_constructor(x) {
//      var that = other_maker(x);
//      var secret = f(x);
//      that.pric = function() {
//         // ... secret x that ...
//      };
//      return that;
//   }

</script>
