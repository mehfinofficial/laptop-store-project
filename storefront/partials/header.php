<?php
$store     = setting('store_name', 'My Store');
$title     = isset($pageTitle) ? $pageTitle . ' | ' . $store : $store . ' - ' . setting('tagline');
$desc      = $metaDesc ?? setting('meta_description');
$canonical = $canonical ?? full_url(ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/'));
$cats      = get_categories();
$logo      = setting('logo');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="<?= e(setting('color_primary', '#6D3CD9')) ?>">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e(fonts_url()) ?>">
<style><?= theme_css() ?></style>
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<?php if (!empty($preload)): ?><link rel="preload" as="image" href="<?= e($preload) ?>" fetchpriority="high"><?php endif; ?>
<?php if (!empty($jsonLd)): ?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<?php endif; ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<?php if (($bodyClass ?? '') === 'home'): ?><div class="top-block"><?php endif; ?>
<header class="site-header">
    <div class="container header-row">
        <a class="logo" href="<?= e(url()) ?>">
            <?php if ($logo): ?><img src="<?= e(url('uploads/' . rawurlencode($logo))) ?>" alt="<?= e($store) ?>">
            <?php else: ?><?= e($store) ?><?php endif; ?>
        </a>
        <form class="search" action="<?= e(url('products')) ?>" method="get" role="search">
            <button type="submit" aria-label="Search"><?= icon('search') ?></button>
            <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(setting('hero_search_placeholder', 'Search products')) ?>" aria-label="Search products">
        </form>
        <div class="hdr-actions">
            <button type="button" class="hdr-icon" aria-label="Wishlist" title="Wishlist"><?= icon('heart') ?></button>
            <a class="hdr-icon" href="<?= e(url('cart')) ?>" aria-label="Cart (<?= cart_count() ?>)"><?= icon('cart') ?>
                <?php if (cart_count() > 0): ?><b class="hdr-badge"><?= cart_count() ?></b><?php endif; ?></a>
        </div>
    </div>
    <nav class="container cat-nav" aria-label="Categories">
        <a href="<?= e(url('products')) ?>">All products</a>
        <?php foreach ($cats as $c): ?>
            <a href="<?= e(url($c['slug'])) ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
        <a href="<?= e(url('products?deals=1')) ?>">Deals</a>
    </nav>
</header>