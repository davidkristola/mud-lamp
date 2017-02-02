<?php
   session_start();
   require_once 'db.php';
   if (!gp_has('id')) die("no id"); // char id
   $id = gp_get('id');
   header('Content-Type: text/xml');

   $xml = new XMLWriter();

   $xml->openURI("php://output");
   $xml->startDocument();
   $xml->setIndent(true);

   $query = "SELECT name, description FROM entities WHERE id='$id' AND type=" . EntityTypes::CHARACTER;
   $result = submit_query($query);
   $result->data_seek(0);
   $row = $result->fetch_array(MYSQLI_ASSOC);
   $xml->startElement('character');
   $xml->startElement("name");
   $xml->writeRaw($row['name']);
   $xml->endElement();
   $xml->startElement("description");
   $xml->writeRaw($row['description']);
   $xml->endElement();

   $query = "SELECT e.id, e.name, e.description, e.type FROM entities e JOIN locators l ON (e.id=l.what) WHERE l.location='$id'";
   $result = submit_query($query);
   $xml->startElement('contents');
   $result->data_seek(0);
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $xml->startElement("content");

      $xml->writeAttribute('id', $row['id']);
      $xml->writeAttribute('name', $row['name']);
      $xml->writeAttribute('type', $row['type']);
      $xml->writeRaw($row['description']);

      $xml->endElement();
   }
   $xml->endElement();

   $xml->endElement();

   $xml->flush();
?>
