<?php
// Run from a terminal in this folder:  php link_images.php
// Applies link_laptop_images.sql to store.sqlite (safe to run twice).
$db = new PDO('sqlite:' . __DIR__ . '/store.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec(file_get_contents(__DIR__ . '/link_laptop_images.sql'));
echo "Linked. Products with an image: "
   . $db->query('SELECT COUNT(DISTINCT product_id) FROM product_images')->fetchColumn() . "\n";
