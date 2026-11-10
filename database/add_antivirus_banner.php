<?php
// Run from a terminal in this folder:  php add_antivirus_banner.php
// Adds image + theme columns to banners (if missing) and the Antivirus slide. Safe to run twice.
$db = new PDO('sqlite:' . __DIR__ . '/store.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$cols = array_column($db->query('PRAGMA table_info(banners)')->fetchAll(PDO::FETCH_ASSOC), 'name');
foreach (['image', 'theme'] as $c) if (!in_array($c, $cols)) $db->exec("ALTER TABLE banners ADD COLUMN $c TEXT");

$st = $db->prepare('INSERT OR IGNORE INTO banners (title, subtitle, cta_text, cta_link, sort_order, image, theme) VALUES (?,?,?,?,?,?,?)');
$st->execute(['Premium Antivirus, now ₹300', 'Advanced protection for your laptop. Flash offer.', 'Get protected', 'products?q=antivirus', 0, 'KasperskyAntivirus', 'green']);
echo "Done. Banners: " . $db->query('SELECT COUNT(*) FROM banners')->fetchColumn() . "\n";
