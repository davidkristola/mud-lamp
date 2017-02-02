<!DOCTYPE html>
<html>
   <head>
      <title>MUD Database Setup Page</title>
   </head>
   <body>
      <p>Creating tables...</p>

<?php
   require_once 'db.php';
   echo "<p>accounts...</p>";
   create_table('accounts', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             user VARCHAR(32),
                             password CHAR(32),
                             start TIMESTAMP,
                             INDEX(user(16)),
                             PRIMARY KEY (id)');
   describe_table('accounts');

   echo "<p>entities...</p>";
   create_table('entities', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             name VARCHAR(32),
                             type TINYINT UNSIGNED,
                             definite TINYINT UNSIGNED,
                             maker INT UNSIGNED NOT NULL,
                             x_key INT UNSIGNED,
                             created TIMESTAMP,
                             description TEXT(200),
                             PRIMARY KEY (id)');
   describe_table('entities');

   echo "<p>attributes...</p>";
   create_table('attributes', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             entity INT UNSIGNED NOT NULL,
                             attr VARCHAR(32),
                             value TEXT(200),
                             PRIMARY KEY (id)');
   describe_table('attributes');

   echo "<p>events...</p>";
   create_table('events', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             etime TIMESTAMP,
                             esrc INT UNSIGNED NOT NULL,
                             eloc INT UNSIGNED NOT NULL,
                             action VARCHAR(16),
                             eex VARCHAR(64),
                             PRIMARY KEY (id)');
   describe_table('events');

   echo "<p>geometry...</p>";
   create_table('geometry', "id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             name VARCHAR(32),
                             shape VARCHAR(32),
                             size_x FLOAT NOT NULL DEFAULT '3.00',
                             size_y FLOAT NOT NULL DEFAULT '3.00',
                             size_z FLOAT NOT NULL DEFAULT '3.00',
                             rotation FLOAT NOT NULL DEFAULT '0.00',
                             PRIMARY KEY (id)");
   describe_table('geometry');

   echo "<p>traits...</p>";
   create_table('traits', "id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             species TINYINT UNSIGNED DEFAULT '1',
                             ethnicity TINYINT UNSIGNED DEFAULT '1',
                             calling TINYINT UNSIGNED DEFAULT '1',
                             height FLOAT NOT NULL DEFAULT '1.70',
                             weight FLOAT NOT NULL DEFAULT '70.00',
                             health_max TINYINT UNSIGNED DEFAULT '10',
                             health_now TINYINT UNSIGNED DEFAULT '10',
                             brawn TINYINT UNSIGNED DEFAULT '9',
                             balance TINYINT UNSIGNED DEFAULT '7',
                             quickness TINYINT UNSIGNED DEFAULT '8',
                             dexterity TINYINT UNSIGNED DEFAULT '7',
                             fortitude TINYINT UNSIGNED DEFAULT '9',
                             stamina TINYINT UNSIGNED DEFAULT '8',
                             PRIMARY KEY (id)");
   describe_table('traits');

   echo "<p>portals...</p>";
   create_table('portals', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             state TINYINT UNSIGNED NOT NULL,
                             direction TINYINT NOT NULL,
                             source INT UNSIGNED NOT NULL,
                             destination INT UNSIGNED NOT NULL,
                             PRIMARY KEY (id)');
   describe_table('portals');

   echo "<p>locators...</p>";
   create_table('locators', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                             state TINYINT NOT NULL,
                             kind TINYINT UNSIGNED NOT NULL,
                             what INT UNSIGNED NOT NULL,
                             location INT UNSIGNED NOT NULL,
                             x INT,
                             y INT,
                             z INT,
                             PRIMARY KEY (id)');
   describe_table('locators');

   function describe_table($table_name) {
      echo_query("DESCRIBE $table_name", array("Column", "Type", "NULL", "Key", "Default", "Extra"));
   }

?>
      <p>...done</p>
   <p><a href=meta.php>meta</a></p>
   <p><a href=index.php>index</a></p>
   </body>
</html>
