<?php
require __DIR__ . '/config/helpers.php';

$featured = get_featured_products(8);
$shown    = array_column($featured, 'id');
$laptops  = array_slice(array_filter(get_products(['category' => 'laptops'], 16), fn($x) => !in_array($x['id'], $shown)), 0, 4);
$cats     = get_categories();
$img      = hero_image();

$jsonLd = [
    '@context'  => 'https://schema.org',
    '@type'     => 'Store',
    'name'      => setting('store_name'),
    'url'       => full_url(),
    'telephone' => setting('phone'),
    'address'   => ['@type' => 'PostalAddress', 'streetAddress' => setting('address'), 'addressLocality' => setting('city')],
];
$slides = [[
    'title'    => setting('hero_title'),
    'subtitle' => setting('hero_subtitle'),
    'cta'      => setting('hero_cta', 'Shop now'),
    'link'     => 'products',
    'img'      => $img,
    'theme'    => '',
    'scale'    => max(40, min(160, (int)setting('hero_image_scale', '100'))) / 100,
]];
foreach (get_banners() as $b) {
    $slides[] = ['title' => $b['title'], 'subtitle' => $b['subtitle'], 'cta' => $b['cta_text'], 'link' => $b['cta_link'],
                'img' => !empty($b['image']) ? hero_image($b['image']) : $img,   // own image (placeholder if file missing), else the main hero image
                'theme' => $b['theme'] ?? '',                                 // colour theme, e.g. green (see style.css)
                'scale' => max(40, min(160, (int)(($b['img_scale'] ?? 0) ?: 100))) / 100];   // img_scale = image zoom in %, 100 = normal
}
$preload   = $img['url'] ?? null;
$bodyClass = 'home';
require __DIR__ . '/partials/header.php';
?>
    <section class="container hero">
        <div class="slider" data-slider>
            <div class="slides">
                <?php foreach ($slides as $i => $sl): ?>
                <div class="banner slide"<?= $sl['theme'] ? ' data-theme="' . e($sl['theme']) . '"' : '' ?><?= $sl['scale'] != 1 ? ' style="--img-scale:' . $sl['scale'] . '"' : '' ?>>
                    <div class="banner-text">
                        <?php $tag = $i === 0 ? 'h1' : 'h2'; ?>
                        <<?= $tag ?>><?= e($sl['title']) ?></<?= $tag ?>>
                        <p><?= e($sl['subtitle']) ?></p>
                        <a class="btn pill" href="<?= e(url($sl['link'])) ?>"><?= e($sl['cta']) ?></a>
                    </div>
                    <div class="banner-img">
                        <?php $si = $sl['img']; if ($si): ?>
                            <img src="<?= e($si['url']) ?>" alt="<?= e($sl['title']) ?>"
                                 <?= $si['w'] ? 'width="' . (int)$si['w'] . '" height="' . (int)$si['h'] . '"' : '' ?>
                                 <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                        <?php else: ?>
                            <?= render_image(null, 'Laptop') ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($slides) > 1): ?>
            <div class="dots" aria-label="Banners">
                <?php foreach ($slides as $i => $sl): ?>
                    <button type="button" class="dot<?= $i === 0 ? ' active' : '' ?>" aria-label="Go to slide <?= $i + 1 ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div><!-- /.top-block -->
<main>
    <section class="container section" id="categories">
        <div class="section-head"><h2>Shop by category</h2><a href="<?= e(url('products')) ?>">View all</a></div>
        <div class="cat-row">
            <?php foreach ($cats as $c): ?>
                <a class="cat-tile" href="<?= e(url($c['slug'])) ?>">
                    <?php $ci = cat_image($c['slug']); ?>
                    <span class="cat-ico<?= $ci ? ' has-img' : '' ?>"><?php if ($ci): ?><img src="<?= e($ci) ?>" alt="" loading="lazy" decoding="async"><?php else: ?><?= cat_icon($c['slug']) ?><?php endif; ?></span>
                    <span class="cat-name"><?= e($c['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container section">
        <div class="section-head"><h2>Top picks for you</h2><a href="<?= e(url('products')) ?>">View all</a></div>
        <div class="grid">
            <?php foreach ($featured as $p) product_card($p); ?>
        </div>
        <?php if (!$featured): ?><p class="muted">No featured products yet.</p><?php endif; ?>
    </section>

    <section class="container perks">
        <?php $perkIcons = [1 => 'cash', 2 => 'shield', 3 => 'phone'];
        for ($i = 1; $i <= 3; $i++): if (!setting("feature{$i}_title")) continue; ?>
            <div class="perk">
                <span class="perk-ico"><?= icon($perkIcons[$i]) ?></span>
                <div><h3><?= e(setting("feature{$i}_title")) ?></h3><p><?= e(setting("feature{$i}_text")) ?></p></div>
            </div>
        <?php endfor; ?>
    </section>

    <?php if ($laptops): ?>
    <section class="container section">
        <div class="section-head"><h2>Latest laptops</h2><a href="<?= e(url('laptops')) ?>">View all</a></div>
        <div class="grid">
            <?php foreach ($laptops as $p) product_card($p); ?>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
