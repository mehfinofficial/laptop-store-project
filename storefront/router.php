<?php
/* Dev only, for PHP's built-in server (it ignores .htaccess):
   cd storefront && php -S localhost:8000 router.php   */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));   // file names with spaces arrive as %20
if ($path !== '/' && is_file(__DIR__ . $path)) return false;   // css, js, uploads, index.php

if ($path === '/' || $path === '/index.php') { require __DIR__ . '/index.php'; return true; }

$fixed = ['products' => 'products', 'cart' => 'cart', 'checkout' => 'checkout', 'order-success' => 'order-success'];
$p = trim($path, '/');
if ($p === 'sitemap.xml') { require __DIR__ . '/pages/sitemap.php'; return true; }
if (isset($fixed[$p]))    { require __DIR__ . '/pages/' . $fixed[$p] . '.php'; return true; }

if (preg_match('#^([a-z0-9-]+)$#', $p, $m))                { $_GET['category'] = $m[1]; require __DIR__ . '/pages/products.php'; return true; }
if (preg_match('#^([a-z0-9-]+)/([a-z0-9-]+)$#', $p, $m))   { $_GET['category'] = $m[1]; $_GET['slug'] = $m[2]; require __DIR__ . '/pages/product.php'; return true; }

http_response_code(404);
echo 'Not found';
