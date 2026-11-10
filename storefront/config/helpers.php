<?php
/* Shared helpers. Every page starts with: require __DIR__ . '/../config/helpers.php'; */

require_once __DIR__ . '/db.php';

/* ---------- Base URL (works in a subfolder like /laptop-store-project/storefront) ---------- */
$__doc  = rtrim(str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$__root = str_replace('\\', '/', (string)realpath(__DIR__ . '/..'));
define('BASE_URL', ($__doc !== '' && stripos($__root, $__doc) === 0) ? substr($__root, strlen($__doc)) : '');
unset($__doc, $__root);

/* ---------- Session ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_name('store_sid');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
    session_start();
}
require_once __DIR__ . '/cart.php';

/* ---------- Output / URLs ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }

function full_url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url($path);
}

function asset(string $path): string {
    $file = __DIR__ . '/../' . $path;
    return url($path) . (file_exists($file) ? '?v=' . filemtime($file) : '');
}

function redirect(string $path, int $code = 302): void {
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)), true, $code);
    exit;
}

/* ---------- Money / text ---------- */
function money($n): string {
    $s = (string)(int)round((float)$n);
    if (strlen($s) > 3) {                       // Indian grouping: 1,23,456
        $last = substr($s, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($s, 0, -3));
        $s = $rest . ',' . $last;
    }
    return setting('currency_symbol', '₹') . $s;
}

function slugify(string $t): string {
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($t)), '-');
}

function discount_pct(array $p): int {
    if (empty($p['old_price']) || $p['old_price'] <= $p['price']) return 0;
    return (int)round((1 - $p['price'] / $p['old_price']) * 100);
}

function specs_of(array $p): array {
    $d = json_decode($p['specs'] ?? '', true);
    return is_array($d) ? $d : [];
}

function max_qty(): int { return max(1, (int)setting('max_qty_per_item', '10')); }

function shipping_for(float $subtotal): float {
    $fee  = (float)setting('shipping_fee', '0');
    $free = (float)setting('free_shipping_above', '0');
    return ($free > 0 && $subtotal >= $free) ? 0 : $fee;
}

/* ---------- CSRF + flash messages ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid request. Go back, refresh the page and try again.');
    }
}
function flash(?string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

/* ---------- Theme: colours/fonts from the settings table become CSS variables ---------- */
function theme_css(): string {
    $map = [
        '--primary'      => ['color_primary',      '#6D3CD9'],
        '--primary-dark' => ['color_primary_dark', '#4F2AB0'],
        '--accent'       => ['color_accent',       '#FFC93C'],
        '--bg'           => ['color_bg',           '#F6F5FB'],
        '--surface'      => ['color_surface',      '#FFFFFF'],
        '--ink'          => ['color_ink',          '#14122B'],
        '--muted'        => ['color_muted',        '#6B6880'],
        '--line'         => ['color_line',         '#E6E3F1'],
        '--sale'         => ['color_sale',         '#E5383B'],
    ];
    $css = ':root{';
    foreach ($map as $var => [$key, $def]) {
        $v = setting($key, $def);
        if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $v)) $v = $def;   // never trust DB values blindly
        $css .= "$var:$v;";
    }
    $css .= '--radius:' . max(0, min(32, (int)setting('radius', '12'))) . 'px;';
    $hf = preg_replace('/[^A-Za-z0-9 ]/', '', setting('font_heading', 'Plus Jakarta Sans'));
    $bf = preg_replace('/[^A-Za-z0-9 ]/', '', setting('font_body', 'Plus Jakarta Sans'));
    $css .= "--font-head:'$hf',system-ui,sans-serif;--font-body:'$bf',system-ui,sans-serif;}";
    return $css;
}

function fonts_url(): string {
    $fams = array_unique([
        preg_replace('/[^A-Za-z0-9 ]/', '', setting('font_heading', 'Plus Jakarta Sans')),
        preg_replace('/[^A-Za-z0-9 ]/', '', setting('font_body', 'Plus Jakarta Sans')),
    ]);
    $q = array_map(fn($f) => 'family=' . str_replace(' ', '+', $f) . ':wght@400;500;700', $fams);
    return 'https://fonts.googleapis.com/css2?' . implode('&', $q) . '&display=swap';
}

/* ---------- Images (placeholder until you add files to product_images) ---------- */
/* Finds the real file in storefront/uploads/ for a name stored in product_images.
   Forgiving on purpose: the stored name may leave out the extension, and spaces/case do not matter,
   so "AppleMacBookPro14" finds "Apple MacBook Pro14.png" or "applemacbookpro14.jpg". */
function upload_name(string $name): string {
    static $files = null;
    if ($files === null) {
        $files = ['exact' => [], 'loose' => []];
        foreach (@scandir(__DIR__ . '/../uploads') ?: [] as $f) {
            if (!preg_match('/\.(png|jpe?g|webp|avif|gif)$/i', $f)) continue;
            $files['exact'][$f] = $f;
            $files['loose'][strtolower(preg_replace('/[^a-z0-9]/i', '', preg_replace('/\.[a-z0-9]+$/i', '', $f)))] = $f;
        }
    }
    if (isset($files['exact'][$name])) return $name;
    $key = strtolower(preg_replace('/[^a-z0-9]/i', '', preg_replace('/\.(png|jpe?g|webp|avif|gif)$/i', '', $name)));
    return $files['loose'][$key] ?? $name;
}

function render_image(?string $file, string $alt, string $class = ''): string {
    if ($file) {
        $file = upload_name($file);
        return '<img class="' . e($class) . '" src="' . e(url('uploads/' . rawurlencode($file))) . '" alt="' . e($alt) . '" loading="lazy">';
    }
    return '<div class="ph ' . e($class) . '" role="img" aria-label="' . e($alt) . '">'
         . '<svg viewBox="0 0 64 48" width="64" height="48" fill="none" stroke="currentColor" stroke-width="2"><rect x="10" y="6" width="44" height="29" rx="3"/><path d="M4 40h56l-4 4H8z"/></svg></div>';
}

/* ---------- Warranty + rating ---------- */
function warranty_label(array $p): string {
    $m = (int)($p['warranty_months'] ?? 0);
    if ($m <= 0) return '';
    if ($m % 12 === 0) { $y = intdiv($m, 12); return $y . ($y === 1 ? ' year' : ' years') . ' warranty'; }
    return $m . ' months warranty';
}

function rating_html(array $p): string {
    $r = (float)($p['rating'] ?? 0);
    if ($r <= 0) return '';
    $n = (int)($p['rating_count'] ?? 0);
    return '<span class="rating" title="' . e(number_format($r, 1)) . ' out of 5">'
         . '<svg class="star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.8l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.6l-5.8 3.1 1.1-6.5L2.6 9.6l6.5-.9z"/></svg>'
         . '<b>' . e(number_format($r, 1)) . '</b>' . ($n > 0 ? '<i>(' . $n . ')</i>' : '') . '</span>';
}

/* ---------- Product card (used on home, listing, related) ---------- */
function product_card(array $p): void {
    $href  = url($p['category_slug'] . '/' . $p['slug']);
    $disc  = discount_pct($p);
    $specs = specs_of($p);
    $bits  = array_filter([$specs['RAM'] ?? null, $specs['Storage'] ?? null]);
    if (!$bits) $bits = array_slice(array_values($specs), 0, 2);
    $out   = $p['stock'] < 1;
    $war   = warranty_label($p);
    $rate  = rating_html($p);
    ?>
    <article class="card<?= $out ? ' is-out' : '' ?>">
        <a class="thumb" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><?= render_image($p['image'], $p['name']) ?></a>
        <div class="card-body">
            <p class="brand"><?= e($p['brand']) ?></p>
            <h3><a href="<?= e($href) ?>"><?= e($p['name']) ?></a></h3>
            <?php if ($bits): ?><p class="spec-line"><?= e(implode(' / ', $bits)) ?></p><?php endif; ?>
            <p class="price"><b><?= money($p['price']) ?></b><?php if ($disc): ?><s><?= money($p['old_price']) ?></s><?php endif; ?></p>
            <?php if ($rate || $war): ?>
            <div class="card-meta"><?= $rate ?><?php if ($war): ?><span class="warranty"><?= icon('shield') ?><?= e($war) ?></span><?php endif; ?></div>
            <?php endif; ?>
            <?php if ($out): ?><p class="stock out">Out of stock</p><?php endif; ?>
        </div>
    </article>
    <?php
}

function not_found(): void {
    http_response_code(404);
    $pageTitle = 'Page not found';
    require __DIR__ . '/../partials/header.php';
    echo '<main class="container narrow"><h1>Page not found</h1><p>That page does not exist. <a href="' . e(url('products')) . '">Browse all products</a>.</p></main>';
    require __DIR__ . '/../partials/footer.php';
    exit;
}


/* ---------- Inline SVG icons (stroke style, inherit colour) ---------- */
function icon(string $name): string {
    static $paths = [
        'home'    => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/>',
        'grid'    => '<rect x="3" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="2"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2"/>',
        'tag'     => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
        'cart'    => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.6 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>',
        'phone'   => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'laptop'  => '<rect x="4" y="5" width="16" height="11" rx="2"/><path d="M2 19.5h20"/>',
        'mouse'   => '<rect x="6" y="3" width="12" height="18" rx="6"/><path d="M12 7v4"/>',
        'bag'     => '<rect x="4" y="7" width="16" height="13" rx="3"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M4 13h16"/>',
        'plug'    => '<path d="M9 2v5M15 2v5"/><path d="M6 7h12v4a6 6 0 0 1-12 0z"/><path d="M12 17v5"/>',
        'drive'   => '<rect x="3" y="7" width="18" height="10" rx="2.5"/><path d="M7 12h.01M11 12h6"/>',
        'cash'    => '<rect x="2" y="6" width="20" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.5"/>',
        'shield'  => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'whatsapp'=> '<path d="M3 21l1.6-4.6A8.5 8.5 0 1 1 8 19.6z"/>',
        'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3 7 9 6 9-6"/>',
        'pin'     => '<path d="M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'heart'   => '<path d="M12 21s-7.5-4.6-9.5-9.2C1.2 8.6 3 5 6.5 5c2 0 3.5 1 5.5 3 2-2 3.5-3 5.5-3C21 5 22.8 8.6 21.5 11.8 19.5 16.4 12 21 12 21z"/>',
        'gamepad' => '<rect x="2" y="7" width="20" height="11" rx="5.5"/><path d="M7 10.5v4M5 12.5h4"/><circle cx="15.5" cy="11.5" r=".7"/><circle cx="18" cy="14" r=".7"/>',
        'headphones' => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="14" width="4" height="7" rx="1.5"/><rect x="17" y="14" width="4" height="7" rx="1.5"/>',
        'chevron' => '<path d="m9 6 6 6-6 6"/>',
    ];
    return '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">' . ($paths[$name] ?? $paths['grid']) . '</svg>';
}

/* Optional photo for a category tile. Looks in storefront/assets/image/ for c<name>.png, where c = category
   and <name> is the category slug in singular: laptops -> claptop.png, gaming -> cgaming.png, chargers -> ccharger.png.
   Also accepts categories/<slug>.png. Extensions: png, webp, jpg, jpeg, avif.
   No file = the line icon from cat_icon() is shown instead, so categories can get photos one by one. */
function cat_image(string $slug): ?string {
    $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug));
    if ($slug === '') return null;
    $one = preg_match('/ies$/', $slug) ? substr($slug, 0, -3) . 'y' : preg_replace('/s$/', '', $slug);
    $tries = ['c' . $one, 'c' . $slug, 'categories/' . $slug, 'categories/c' . $one];
    foreach ($tries as $name) {
        foreach (['png', 'webp', 'jpg', 'jpeg', 'avif'] as $ext) {
            if (is_file(__DIR__ . '/../assets/image/' . $name . '.' . $ext)) {
                return asset('assets/image/' . $name . '.' . $ext);
            }
        }
    }
    return null;
}

function cat_icon(string $slug): string {
    if (preg_match('/laptop|computer/', $slug))        return icon('laptop');
    if (preg_match('/mouse|keyboard|periph/', $slug))  return icon('mouse');
    if (preg_match('/gam/', $slug))                    return icon('gamepad');
    if (preg_match('/audio|head|speaker|sound/', $slug)) return icon('headphones');
    if (preg_match('/accessor|bag|case|sleeve/', $slug)) return icon('bag');
    if (preg_match('/charg|cable|power/', $slug))      return icon('plug');
    if (preg_match('/storage|drive|ssd|memory/', $slug)) return icon('drive');
    return icon('grid');
}

/* ---------- Hero banner image: storefront/assets/image/<hero_image>.<any extension> ---------- */
function hero_image(?string $name = null): ?array {
    $dir  = __DIR__ . '/../assets/image/';
    $name = $name ?: setting('hero_image', 'Laptop');   // $name = image for a specific banner
    $exts = 'png|jpe?g|webp|avif|gif';
    $file = null;
    if (preg_match("/\\.($exts)$/i", $name) && is_file($dir . $name)) {
        $file = $name;
    } else {
        $base = preg_replace('/[^A-Za-z0-9_\\- ]/', '', pathinfo($name, PATHINFO_FILENAME));
        foreach (glob($dir . $base . '.*') ?: [] as $f) {
            if (preg_match("/\\.($exts)$/i", $f)) { $file = basename($f); break; }
        }
    }
    if (!$file) return null;
    $size = @getimagesize($dir . $file) ?: [0, 0];
    return [
        'url' => url('assets/image/' . rawurlencode($file)) . '?v=' . filemtime($dir . $file),
        'w'   => $size[0], 'h' => $size[1],
    ];
}