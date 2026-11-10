<?php
// Run from a terminal in this folder:  php add_elitebook_banner.php
// Adds an `image` column to banners (if missing) and the HP EliteBook slide. Safe to run twice.
$db = new PDO('sqlite:' . __DIR__ . '/store.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$cols = array_column($db->query('PRAGMA table_info(banners)')->fetchAll(PDO::FETCH_ASSOC), 'name');
if (!in_array('image', $cols)) $db->exec('ALTER TABLE banners ADD COLUMN image TEXT');

$st = $db->prepare('INSERT OR IGNORE INTO banners (title, subtitle, cta_text, cta_link, sort_order, image) VALUES (?,?,?,?,?,?)');
$st->execute(['HP EliteBook', 'Business-class laptops starting at ₹23,999', 'Shop now', 'laptops', 0, 'Laptop2']);
echo "Done. Banners: " . $db->query('SELECT COUNT(*) FROM banners')->fetchColumn() . "\n";
