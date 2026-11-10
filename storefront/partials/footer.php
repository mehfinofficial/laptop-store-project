<?php
/* Which bottom-nav tab is active */
$__path  = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$__route = trim(substr($__path, strlen(BASE_URL)), '/');
if ($__route === '' || $__route === 'index.php')                         $navOn = 'home';
elseif (in_array($__route, ['cart', 'checkout', 'order-success'], true)) $navOn = 'cart';
elseif ($__route === 'products' && !empty($_GET['deals']))               $navOn = 'deals';
else                                                                     $navOn = 'cats';

$__cats  = get_categories();
$__phone = setting('phone');
$__wa    = preg_replace('/\D/', '', setting('whatsapp'));
?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4><?= e(setting('store_name')) ?></h4>
            <p><?= e(setting('tagline')) ?></p>
            <p class="muted"><?= e(setting('footer_text')) ?></p>
        </div>
        <div>
            <h4>Shop</h4>
            <?php foreach ($__cats as $c): ?>
                <a href="<?= e(url($c['slug'])) ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <div>
            <h4>Contact</h4>
            <?php if ($__phone): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $__phone)) ?>"><?= e($__phone) ?></a><?php endif; ?>
            <?php if ($__wa): ?><a href="https://wa.me/<?= e($__wa) ?>">WhatsApp us</a><?php endif; ?>
            <?php if (setting('email')): ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a><?php endif; ?>
            <p class="muted"><?= nl2br(e(setting('address'))) ?><?= setting('city') ? ', ' . e(setting('city')) : '' ?></p>
            <?php if (setting('opening_hours')): ?><p class="muted"><?= e(setting('opening_hours')) ?></p><?php endif; ?>
            <?php if (setting('facebook')): ?><a href="<?= e(setting('facebook')) ?>" rel="noopener">Facebook</a><?php endif; ?>
            <?php if (setting('instagram')): ?><a href="<?= e(setting('instagram')) ?>" rel="noopener">Instagram</a><?php endif; ?>
        </div>
    </div>
    <div class="container copyright">&copy; <?= date('Y') ?> <?= e(setting('store_name')) ?></div>
</footer>

<!-- Mobile bottom navigation (hidden on desktop) -->
<nav class="bottom-nav" aria-label="Main">
    <a href="<?= e(url()) ?>" class="<?= $navOn === 'home' ? 'on' : '' ?>"><?= icon('home') ?><span>Home</span></a>
    <a href="<?= e(url('products')) ?>" data-sheet="catsSheet" class="<?= $navOn === 'cats' ? 'on' : '' ?>"><?= icon('grid') ?><span>Categories</span></a>
    <a href="<?= e(url('products?deals=1')) ?>" class="<?= $navOn === 'deals' ? 'on' : '' ?>"><?= icon('tag') ?><span>Deals</span></a>
    <a href="<?= e(url('cart')) ?>" class="<?= $navOn === 'cart' ? 'on' : '' ?>"><?= icon('cart') ?><span>Cart</span>
        <?php if (cart_count() > 0): ?><b class="bn-badge"><?= cart_count() ?></b><?php endif; ?></a>
    <a href="<?= $__phone ? e('tel:' . preg_replace('/[^0-9+]/', '', $__phone)) : '#' ?>" data-sheet="contactSheet"><?= icon('phone') ?><span>Contact</span></a>
</nav>

<div class="sheet-backdrop" id="sheetBackdrop"></div>

<section class="sheet" id="catsSheet" role="dialog" aria-label="Categories" aria-hidden="true">
    <div class="sheet-handle"></div>
    <h3>Categories</h3>
    <a class="sheet-link" href="<?= e(url('products')) ?>"><?= icon('grid') ?><span>All products</span><?= icon('chevron') ?></a>
    <?php foreach ($__cats as $c): ?>
        <a class="sheet-link" href="<?= e(url($c['slug'])) ?>"><?= cat_icon($c['slug']) ?><span><?= e($c['name']) ?></span><small><?= (int)$c['product_count'] ?></small><?= icon('chevron') ?></a>
    <?php endforeach; ?>
</section>

<section class="sheet" id="contactSheet" role="dialog" aria-label="Contact us" aria-hidden="true">
    <div class="sheet-handle"></div>
    <h3>Contact us</h3>
    <?php if ($__phone): ?><a class="sheet-link" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $__phone)) ?>"><?= icon('phone') ?><span>Call <?= e($__phone) ?></span><?= icon('chevron') ?></a><?php endif; ?>
    <?php if ($__wa): ?><a class="sheet-link" href="https://wa.me/<?= e($__wa) ?>"><?= icon('whatsapp') ?><span>Chat on WhatsApp</span><?= icon('chevron') ?></a><?php endif; ?>
    <?php if (setting('email')): ?><a class="sheet-link" href="mailto:<?= e(setting('email')) ?>"><?= icon('mail') ?><span><?= e(setting('email')) ?></span><?= icon('chevron') ?></a><?php endif; ?>
    <?php if (setting('address')): ?><div class="sheet-link static"><?= icon('pin') ?><span><?= e(setting('address')) ?><?= setting('city') ? ', ' . e(setting('city')) : '' ?><br><small><?= e(setting('opening_hours')) ?></small></span></div><?php endif; ?>
</section>

<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
