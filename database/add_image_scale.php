<?php
// Run from a terminal in this folder:  php add_image_scale.php
// Adds banners.img_scale (image zoom in %, 100 = normal). Safe to run twice.
$db = new PDO('sqlite:' . __DIR__ . '/store.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$cols = array_column($db->query('PRAGMA table_info(banners)')->fetchAll(PDO::FETCH_ASSOC), 'name');
if (!in_array('img_scale', $cols)) $db->exec('ALTER TABLE banners ADD COLUMN img_scale INTEGER');
$db->exec("UPDATE banners SET img_scale = 105 WHERE image = 'Laptop2' AND img_scale IS NULL");
echo "Done.\n";
