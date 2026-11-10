<?php
/* Single product: /laptops/dell-inspiron-15-3520 */
require __DIR__ . '/../config/helpers.php';

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'] ?? ''));
$p = $slug !== '' ? get_product_by_slug($slug) : null;
if (!$p) not_found();

// Wrong category in the URL? 301 to the correct one (avoids duplicate pages in Google)
if (($_GET['category'] ?? '') !== $p['category_slug']) {
    redirect($p['category_slug'] . '/' . $p['slug'], 301);
}

$images  = get_product_images((int)$p['id']);
$specs   = specs_of($p);
$related = get_related_products((int)$p['category_id'], (int)$p['id'], 4);
$disc    = discount_pct($p);
$canon   = full_url($p['category_slug'] . '/' . $p['slug']);

$pageTitle = $p['name'];
$metaDesc  = mb_substr(strip_tags($p['description'] ?? ''), 0, 155);
$canonical = $canon;
$jsonLd = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $p['name'],
    'brand'       => ['@type' => 'Brand', 'name' => $p['brand']],
    'description' => $p['description'],
    'image'       => array_map(fn($f) => full_url('uploads/' . rawurlencode($f)), $images),
    'offers'      => [
        '@type' => 'Offer', 'url' => $canon, 'priceCurrency' => 'INR', 'price' => (string)$p['price'],
        'availability' => $p['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
];
require __DIR__ . '/../partials/header.php';
?>
<main class="container">
    <p class="crumbs"><a href="<?= e(url()) ?>">Home</a> / <a href="<?= e(url($p['category_slug'])) ?>"><?= e($p['category_name']) ?></a> / <?= e($p['name']) ?></p>

    <div class="product">
        <div>
            <div class="gallery-main">
                <?php if ($images): ?><img id="galleryMain" src="<?= e(url('uploads/' . rawurlencode($images[0]))) ?>" alt="<?= e($p['name']) ?>">
                <?php else: ?><?= render_image(null, $p['name']) ?><?php endif; ?>
            </div>
            <?php if (count($images) > 1): ?>
            <div class="thumbs">
                <?php foreach ($images as $i => $f): $src = url('uploads/' . rawurlencode($f)); ?>
                    <button type="button" data-gallery-thumb data-src="<?= e($src) ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Image <?= $i + 1 ?>"><img src="<?= e($src) ?>" alt=""></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div>
            <p class="brand muted"><?= e($p['brand']) ?></p>
            <h1><?= e($p['name']) ?></h1>
            <?php $rate = rating_html($p); $war = warranty_label($p); if ($rate || $war): ?>
            <div class="product-meta"><?= $rate ?><?php if ($war): ?><span class="warranty"><?= icon('shield') ?><?= e($war) ?></span><?php endif; ?></div>
            <?php endif; ?>
            <p class="price"><b><?= money($p['price']) ?></b>
                <?php if ($disc): ?><s><?= money($p['old_price']) ?></s> <strong><?= $disc ?>% off</strong><?php endif; ?></p>
            <p><?= nl2br(e($p['description'])) ?></p>

            <?php if ($p['stock'] > 0): ?>
                <p class="stock <?= $p['stock'] <= 5 ? 'low' : '' ?>"><?= $p['stock'] <= 5 ? 'Only ' . (int)$p['stock'] . ' left' : 'In stock' ?></p>
                <form class="buy" method="post" action="<?= e(url('cart')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <div class="qty">
                        <button type="button" data-step="down" aria-label="Less">-</button>
                        <input type="number" name="qty" value="1" min="1" max="<?= min(max_qty(), (int)$p['stock']) ?>" aria-label="Quantity">
                        <button type="button" data-step="up" aria-label="More">+</button>
                    </div>
                    <button class="btn" type="submit">Add to cart</button>
                </form>
            <?php else: ?>
                <p class="stock out">Out of stock</p>
            <?php endif; ?>

            <?php if ($specs): ?>
            <div class="spec-table">
                <h2>Specifications</h2>
                <?php foreach ($specs as $k => $v): ?>
                    <div class="spec-row"><span><?= e($k) ?></span><i></i><b><?= e($v) ?></b></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($related): ?>
    <section class="section">
        <div class="section-head"><h2>You may also like</h2></div>
        <div class="grid"><?php foreach ($related as $r) product_card($r); ?></div>
    </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
