<?php
require __DIR__ . '/../config/helpers.php';
header('Content-Type: application/xml; charset=utf-8');

$urls = [full_url(''), full_url('products')];
foreach (get_categories() as $c) $urls[] = full_url($c['slug']);
foreach (get_products([], 5000, 0) as $p) $urls[] = full_url($p['category_slug'] . '/' . $p['slug']);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) echo '  <url><loc>' . e($u) . "</loc></url>\n";
echo '</urlset>';
