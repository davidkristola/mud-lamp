<?php
   session_start();
   date_default_timezone_set('UTC');
   require_once 'db.php';
   if (!gp_has('char_id')) die("no char_id");
   if (!gp_has('eloc')) die("no eloc");
   if (!gp_has('etime')) die("no etime");
   if (!gp_has('action')) die("no action");
   if (!gp_has('extra')) die("no extra");
   $etime = gp_get('etime');
   $cid = gp_get('char_id');
   $eloc = gp_get('eloc');
   $action = gp_get('action');
   $extra = gp_get('extra');
   header('Content-Type: text/xml');
   add_action($etime, $cid, $eloc, $action, $extra);
   //$result = submit_query("INSERT INTO events (etime, esrc, eloc, action, eex) VALUES('$etime', $cid, $eloc, '$action', '$extra')");
?>
<status>Good</status>
