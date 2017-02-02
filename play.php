<?php
   require_once 'master.php';
   if (!$logged_in) die("This page is restricted to logged in users. <a href=index.php>start over</a>");
   if (isset($_GET['char_id'])) {
      $char_id = $_GET['char_id'];
      $char = get_name($char_id);
      start_page($char);
   } else die("Somehow you managed to get here without picking a character to play. <a href=meta.php>try again</a>");
   $page_load_time = date("Y-m-d H:i:s");
   $start_location = get_first_location(); //TODO get "camp" location for character from db?
   add_locator($char_id, $start_location);
   add_action($page_load_time, $char_id, $start_location, 'enter', "$char pops in out of thin air.");

   $query = "SELECT name, description FROM entities WHERE id='$start_location' AND type=" . EntityTypes::PLACE;
   $result = submit_query($query);
   $result->data_seek(0);
   $row = $result->fetch_array(MYSQLI_ASSOC);
   $room = $row['name'];
   $desc = $row['description'];
?>

<body onLoad="setTimeout('processTimeout()', 1000); make_preliminary_requests()">

<style>
@import url('mud.css');
</style>

<script type="text/javascript">
// PHP-to-JavaScript transfer of data
char_name = "<?=$char?>";
previous_request_time = "<?=$page_load_time?>";
current_location = <?=$start_location?>;
char_id = <?=$char_id?>;
</script>

<script type="text/javascript" src="mud.js">
</script>

<span id="LastUpdateTime"><?=$page_load_time?></span>

<div id="room_info">
   <h1 id="room_name"> <?=$room?> </h1>
   <p id="room_desc"> <?=$desc?> </p>
</div>

<div class="clearfix">
<div class="pri_info menu">
   <h3>Stats</h3>
   <div id="stats">
   </div>
   <hr>
   <h3>Things</h3>
   <div class="dropzone" id="my_stuff" ondrop="drop_to_char(event)" ondragover="allow_char_drop(event)">
   </div>
</div>

<div class="pri_info content" id="main">
</div>

<div class="pri_info menu">
   <h3>Exits</h3>
   <div id="exits">
   </div>
   <hr>
   <h3>People</h3>
   <div id="people">
   </div>
   <hr>
   <h3>Things</h3>
   <div class="dropzone" id="things" ondrop="drop_to_room(event)" ondragover="allow_room_drop(event)">
   </div>
</div>
</div>

<hr>

<div id="main_input">
<form name="dothis" onsubmit="return never_submit()">
   <input type="button" id="DoSay" value="Say" onclick="send_say_event()">
   <input type='text' maxlength='64' size="64" name='saying' onkeydown="return process_key(event)" autofocus='autofocus' required='required'>
</form>
</div>

<div class="clearfix">
   <div id="move_input">
      <table>
         <tr><td><button onclick="move(14)" id="NW">NW</button></td>
            <td><button onclick="move(0)" id="North">North</button>
               </td><td><button onclick="move(2)" id="NE">NE</button></td></tr>
         <tr><td><button onclick="move(12)" id="West">West</button></td>
            <td><button onclick="move(-1)" id="Up">Up</button><button onclick="move(-2)" id="Down">Down</button></td>
               <td><button onclick="move(4)" id="East">East</button></td></tr>
         <tr><td><button onclick="move(10)" id="SW">SW</button></td>
            <td><button onclick="move(8)" id="South">South</button></td>
               <td><button onclick="move(6)" id="SE">SE</button></td></tr>
      </table>
   </div>

<!--
   <div class="handdiv">
      <span class="handname">left hand</span><br>
      <div id="left_hand" ondrop="drop_to_hand(event)" ondragover="allow_hand_drop(event)" ondragleave="leave_hand(event)">
      </div>
   </div>

   <div class="handdiv">
      <span class="handname">right hand</span><br>
      <div id="right_hand" ondrop="drop_to_hand(event)" ondragover="allow_hand_drop(event)" ondragleave="leave_hand(event)">
      </div>
   </div>
-->
</div>

<hr>


<button onclick='stop_playing()'>stop playing</button><br>
<button onclick='redirect_to_logout()'>log out</button><br>

<p>Alert 1: <span id="alert_1"></span></p>
<p>Debug 1: <span id="debug_1"></span></p>
<p>Debug 2: <span id="debug_2"></span></p>
<p>Debug 3: <span id="debug_3"></span></p>
<p>Debug 4: <span id="debug_4"></span></p>

</body>
</html>
