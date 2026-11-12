<?php
// Run from a terminal in this folder:  php update_trust_banners.php
// Replaces the last two generic slides ("Built for work and study", "Pay when it arrives")
// with "Quality checked" and "Warranty", using assets/image/qualitycheck.png and warranty.png.
// Safe to run twice.
$db = new PDO('sqlite:' . __DIR__ . '/store.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$cols = array_column($db->query('PRAGMA table_info(banners)')->fetchAll(PDO::FETCH_ASSOC), 'name');
foreach (['image' => 'TEXT', 'theme' => 'TEXT', 'img_scale' => 'INTEGER'] as $c => $t)
    if (!in_array($c, $cols)) $db->exec("ALTER TABLE banners ADD COLUMN $c $t");

// [old title, title, subtitle, cta text, cta link, sort order, image, img_scale]
$rows = [
    ['Built for work and study', 'Every laptop is quality checked', 'Tested and inspected before it reaches you.', 'Shop laptops', 'laptops',  3, 'qualitycheck', 110],
    ['Pay when it arrives',      'Warranty on every laptop',        'Buy with confidence and get support after you buy.', 'Shop now', 'products', 4, 'warranty', 110],
];
$upd = $db->prepare('UPDATE banners SET title=?, subtitle=?, cta_text=?, cta_link=?, sort_order=?, image=?, img_scale=? WHERE title=?');
$ins = $db->prepare('INSERT INTO banners (title, subtitle, cta_text, cta_link, sort_order, is_active, image, img_scale) VALUES (?,?,?,?,?,1,?,?)');
$has = $db->prepare('SELECT COUNT(*) FROM banners WHERE title=?');
foreach ($rows as [$old, $title, $sub, $cta, $link, $sort, $img, $scale]) {
    $has->execute([$title]);
    if ($has->fetchColumn()) continue;                                   // already done
    $upd->execute([$title, $sub, $cta, $link, $sort, $img, $scale, $old]);
    if (!$upd->rowCount()) $ins->execute([$title, $sub, $cta, $link, $sort, $img, $scale]);   // old slide was deleted: add fresh
}
echo "Done. Banners:\n";
foreach ($db->query('SELECT sort_order, title, image FROM banners ORDER BY sort_order') as $b) echo "  {$b['sort_order']}  {$b['title']}  [{$b['image']}]\n";
