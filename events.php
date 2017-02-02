<?php
   session_start();
   date_default_timezone_set('UTC');
   require_once 'db.php';
   if (!gp_has('etime')) die("no etime");
   if (!gp_has('eloc')) die("no eloc");
   $etime = gp_get('etime');
   $eloc = gp_get('eloc');
   header('Content-Type: text/xml');
   $query = "SELECT events.id, name, etime, action, eex FROM events JOIN entities ON entities.id=events.esrc WHERE etime >= '$etime' AND eloc = '$eloc'";
   $result = submit_query($query);

   $xml = new XMLWriter();

   $xml->openURI("php://output");
   $xml->startDocument();
   $xml->setIndent(true);

   $xml->startElement('events');

   $result->data_seek(0);
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $xml->startElement("event");

      $xml->writeAttribute('seq', $row['id']);
      $xml->writeAttribute('etime', $row['etime']);
      $xml->writeAttribute('name', $row['name']);
      $xml->writeAttribute('action', $row['action']);
      $xml->writeRaw($row['eex']);

      $xml->endElement();
   }

   $xml->endElement();
   $xml->flush();
?>
