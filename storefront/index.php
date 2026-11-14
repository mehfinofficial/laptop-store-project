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
/**
 * Text highlighter for slide titles and subtitles.
 *  - Wrap words in *stars* to colour them:  "*Student* discount on laptops"
 *  - Highlights alternate between two colour sets (a, b) that change per slide (see style.css).
 *  - If a title has no stars, any price like ₹14,999 is highlighted automatically.
 */
function hl_title(string $text): string {
    $t = e($text);
    if (strpos($t, '*') !== false) {
        $i = 0;
        return preg_replace_callback('/\*(.+?)\*/u', function ($m) use (&$i) {
            return '<span class="hl hl-' . (($i++ % 2) ? 'b' : 'a') . '">' . $m[1] . '</span>';
        }, $t);
    }
    return preg_replace('/₹\s?[\d,]+/u', '<span class="hl hl-b">$0</span>', $t);
}
/** Subtitle: prices (₹23,999) are shown bold, so they stand out. */
function hl_sub(string $text): string {
    return preg_replace('/₹\s?[\d,]+/u', '<b class="sub-price">$0</b>', e($text));
}

$slides = [[
    'title'    => setting('hero_title'),
    'subtitle' => setting('hero_subtitle'),
    'cta'      => setting('hero_cta', 'Shop now'),
    'link'     => 'products',
    'hero'     => true,                    // only this first slide gets the trust row
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
                <?php $isHero = !empty($sl['hero']); ?>
                <div class="banner slide<?= $isHero ? ' hero-slide' : '' ?>"<?= $sl['theme'] ? ' data-theme="' . e($sl['theme']) . '"' : '' ?><?= $sl['scale'] != 1 ? ' style="--img-scale:' . $sl['scale'] . '"' : '' ?>>
                    <div class="banner-text">
                        <?php $tag = $i === 0 ? 'h1' : 'h2'; ?>
                        <<?= $tag ?>><?= hl_title($sl['title']) ?></<?= $tag ?>>
                        <p><?= hl_sub($sl['subtitle']) ?></p>
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
                    <?php if ($isHero): ?>
                    <ul class="hero-trust">
                        <li><?= icon('cash') ?><?= e(setting('feature1_title', 'Cash on delivery')) ?></li>
                        <li><?= icon('shield') ?><?= e(setting('feature2_title', 'Brand warranty')) ?></li>
                        <li><?= icon('tag') ?>Student discount</li>
                    </ul>
                    <?php endif; ?>
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
    <?php
    // Compact "Free with every laptop" banner. Image file names live in assets/image/.
    $gifts = [
        ['img' => 'bkeybordmouse', 'name' => 'Keyboard & Mouse', 'cls' => 'g-kb'],
        ['img' => 'badapter',      'name' => 'Laptop Adapter',   'cls' => 'g-ad'],
        ['img' => 'bbackpack',     'name' => 'Backpack',         'cls' => 'g-bp'],
    ];
    ?>
    <section class="container section">
        <a class="gifts" href="<?= e(url('laptops')) ?>">
            <div class="gifts-text">
                <span class="gifts-tag">FREE GIFTS</span>
                <h2>Free with every laptop</h2>
                <p>Keyboard, mouse, adapter &amp; backpack</p>
            </div>
            <div class="gifts-imgs">
                <?php $n = 0; foreach ($gifts as $g): $gi = gift_image($g['img']); if (!$gi) continue; ?>
                    <?php if ($n++): ?><span class="g-plus" aria-hidden="true">+</span><?php endif; ?>
                    <img class="<?= e($g['cls']) ?>" src="<?= e($gi) ?>" alt="<?= e($g['name']) ?>" loading="lazy" decoding="async">
                <?php endforeach; ?>
            </div>
        </a>
    </section>

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

    <?php $fi = gift_image('studenthelp'); ?>
    <section class="container section">
        <div class="finder">
            <div class="finder-text">
                <h2>Not sure which laptop to buy?</h2>
                <p>Tell us your budget and use. We'll help you pick.</p>
            </div>
            <?php if ($fi): ?><img class="finder-img" src="<?= e($fi) ?>" alt="" loading="lazy" decoding="async"><?php endif; ?>
        </div>
    </section>

    <?php if ($laptops): ?>
    <section class="container section">
        <div class="section-head"><h2>Latest laptops</h2><a href="<?= e(url('laptops')) ?>">View all</a></div>
        <div class="grid">
            <?php foreach ($laptops as $p) product_card($p); ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="container perks">
        <?php $perkIcons = [1 => 'cash', 2 => 'shield', 3 => 'phone'];
        for ($i = 1; $i <= 3; $i++): if (!setting("feature{$i}_title")) continue; ?>
            <div class="perk">
                <span class="perk-ico"><?= icon($perkIcons[$i]) ?></span>
                <div><h3><?= e(setting("feature{$i}_title")) ?></h3><p><?= e(setting("feature{$i}_text")) ?></p></div>
            </div>
        <?php endfor; ?>
    </section>

    <?php if (setting('address')):
        $mapUrl = preg_match('#^https?://#i', setting('map_link'))
            ? setting('map_link')
            : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(trim(setting('address') . ' ' . setting('city'))); ?>
    <section class="container section">
        <div class="visit">
            <div>
                <h2><?= e(setting('store_visit_title', 'Visit our store')) ?></h2>
                <p class="visit-addr"><?= icon('pin') ?><span><?= nl2br(e(setting('address'))) ?><?= setting('city') ? ', ' . e(setting('city')) : '' ?></span></p>
                <?php if (setting('opening_hours')): ?><p class="visit-hours"><?= e(setting('opening_hours')) ?></p><?php endif; ?>
            </div>
            <a class="btn pill" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener">Get directions</a>
        </div>
    </section>
    <?php endif; ?>

    <?php $reviews = get_reviews(6); if ($reviews): ?>
    <section class="container section">
        <div class="section-head"><h2><?= e(setting('reviews_title', 'What customers say')) ?></h2></div>
        <div class="reviews">
            <?php foreach ($reviews as $r): $n = max(1, min(5, (int)$r['rating'])); ?>
            <figure class="review">
                <span class="stars" role="img" aria-label="<?= $n ?> out of 5"><?= str_repeat('★', $n) ?></span>
                <p><?= e($r['text']) ?></p>
                <figcaption><?= e($r['name']) ?></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php $faqs = get_faqs(); if ($faqs): ?>
    <section class="container section">
        <div class="section-head"><h2><?= e(setting('faq_title', 'Questions, answered')) ?></h2></div>
        <div class="faq">
            <?php foreach ($faqs as $q): ?>
            <details><summary><?= e($q['question']) ?></summary><p><?= nl2br(e($q['answer'])) ?></p></details>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>