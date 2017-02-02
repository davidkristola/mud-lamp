<?php
   require_once 'master.php';
   start_page();
   if (!$logged_in) die("This page is restricted to logged in users. <a href=index.php>start over</a>");
?>
<body>
<h1>META page (you are logged in as <?=$user?>)</h1>

<?php
   $query = "SELECT e.id, e.name, a.value, e.description FROM entities e " .
            "JOIN attributes a ON (e.id=a.entity) " .
            "WHERE e.maker=$user_id AND e.type=" . EntityTypes::CHARACTER .  " AND a.attr='Race'";
   echo "<p>query=$query</p>";
   $result = submit_query($query);
   if ($result->num_rows) {
      echo '<form action="">';
      echo '<table border="1">';
      echo '<tr><th>Name</th><th>Race</th><th>Description</th><th>Action</th></tr>';
      $number_of_rows = $result->num_rows;
      for ($j = 0; $j < $number_of_rows; ++$j) {
         $result->data_seek($j);
         $row = $result->fetch_array(MYSQLI_NUM);
         echo "<tr>";
         echo "<td>" . $row[1] . "</td>";
         echo "<td>" . $row[2] . "</td>";
         echo "<td>" . $row[3] . "</td>";
         make_buttons($row[1], $row[0]);
         echo "</tr>";
      }
      echo "</table>";
      echo '</form>';
   }

   function make_buttons($name, $char_id) {
      ?>
      <td>
      <input type="button" value="Play" onclick="window.location.href='play.php?char_id=<?=$char_id?>';"/>
      <input type="button" value="Update" onclick="window.location.href='character.php?action=update&id=<?=$char_id?>';"/>
      </td>
      <?php
   }
?>

<p><a href=character.php?action=new>Create a new character</a></p>
<p><a href=room.php?path=new&action=new>Create a new room</a></p>
<p><a href=portals.php?action=new>Create a new portal</a></p>
<p><a href=thing.php?path=new>Create a new thing</a></p>
<p><a href=setup.php>Setup the database</a></p>
</body>
</html>
