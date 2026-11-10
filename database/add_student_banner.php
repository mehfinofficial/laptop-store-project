<?php
// Run from a terminal in this folder:  php add_student_banner.php
// Adds the Student banner as slide 2, removes "Deals you'll love", keeps the rest in order. Safe to run twice.
$db = new PDO('sqlite:' . __DIR__ . '/store.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$cols = array_column($db->query('PRAGMA table_info(banners)')->fetchAll(PDO::FETCH_ASSOC), 'name');
foreach (['image' => 'TEXT', 'theme' => 'TEXT', 'img_scale' => 'INTEGER'] as $c => $type)
    if (!in_array($c, $cols)) $db->exec("ALTER TABLE banners ADD COLUMN $c $type");

$db->beginTransaction();
$db->exec("DELETE FROM banners WHERE title = 'Deals you''ll love'");

$db->prepare('INSERT OR IGNORE INTO banners (title, subtitle, cta_text, cta_link, sort_order, image, theme, img_scale) VALUES (?,?,?,?,?,?,?,?)')
   ->execute(['Student discount on laptops', 'Special prices for students. Get your offer today.', 'Shop laptops', 'laptops', 0, 'student', null, 100]);

// slide order after the main hero (smaller number = earlier)
$order = ['Student discount on laptops', 'HP EliteBook', 'Premium Antivirus, now ₹300', 'Built for work and study', 'Pay when it arrives'];
$up = $db->prepare('UPDATE banners SET sort_order = ? WHERE title = ?');
foreach ($order as $i => $title) $up->execute([$i, $title]);
$db->commit();

echo "Done. Slides after the main hero:\n";
foreach ($db->query('SELECT title FROM banners WHERE is_active = 1 ORDER BY sort_order, id') as $i => $r) echo ($i + 2) . '. ' . $r['title'] . "\n";
