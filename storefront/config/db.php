<?php
/* DB connection + all queries.
   MySQL later: change the PDO line in db(), delete the PRAGMA lines. Queries stay the same. */

define('DB_PATH', __DIR__ . '/../../database/store.sqlite');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }
    return $pdo;
}

/* ---------- Settings (store name, colours, contact, options) ---------- */
function all_settings(): array {
    static $s = null;
    if ($s === null) {
        try {
            $s = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable $e) {
            $s = [];
        }
    }
    return $s;
}
function setting(string $key, string $default = ''): string {
    $s = all_settings();
    return (isset($s[$key]) && $s[$key] !== '') ? $s[$key] : $default;
}

/* ---------- Extra hero banners (slides after the main hero from settings) ---------- */
function get_banners(): array {
    try {
        return db()->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    } catch (Throwable $e) {
        return [];   // table not created yet: site just shows the single hero
    }
}

/* ---------- Homepage content blocks (edited in the database, shown only when they have rows) ---------- */
function get_reviews(int $limit = 6): array {
    try {
        return db()->query('SELECT * FROM reviews WHERE is_active = 1 ORDER BY sort_order, id DESC LIMIT ' . (int)$limit)->fetchAll();
    } catch (Throwable $e) {
        return [];   // table not created yet: section is simply hidden
    }
}
function get_faqs(): array {
    try {
        return db()->query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/* ---------- Categories ---------- */
function get_categories(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $sql = "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
            FROM categories c ORDER BY %s";
    try {
        return $cache = db()->query(sprintf($sql, 'c.sort_order, c.id'))->fetchAll();
    } catch (Throwable $e) {   // sort_order column not added yet: fall back to id order
        return $cache = db()->query(sprintf($sql, 'c.id'))->fetchAll();
    }
}
function get_category_by_slug(string $slug): ?array {
    $st = db()->prepare('SELECT * FROM categories WHERE slug = ?');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

/* ---------- Products ---------- */
const PRODUCT_SELECT = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
    (SELECT filename FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1) AS image
    FROM products p JOIN categories c ON c.id = p.category_id";

function product_where(array $f): array {
    $where = ['p.is_active = 1'];
    $args  = [];
    if (!empty($f['category'])) { $where[] = 'c.slug = ?';  $args[] = $f['category']; }
    if (!empty($f['deals']))    { $where[] = 'p.old_price IS NOT NULL AND p.old_price > p.price'; }
    if (!empty($f['max_price'])) { $where[] = 'p.price <= ?'; $args[] = (int)$f['max_price']; }
    if (!empty($f['brands'])) {   // brand slugs from the laptop finder -> real brand names
        $names = brand_names_for($f['brands']);
        if ($names) { $where[] = 'p.brand IN (' . implode(',', array_fill(0, count($names), '?')) . ')'; array_push($args, ...$names); }
        else        { $where[] = '0'; }
    }
    if (!empty($f['uses'])) {     // use-case slugs; a laptop matches if it has ANY of them
        $where[] = 'p.id IN (SELECT pu.product_id FROM product_use_cases pu JOIN use_cases u ON u.id = pu.use_case_id
                             WHERE u.is_active = 1 AND u.slug IN (' . implode(',', array_fill(0, count($f['uses']), '?')) . '))';
        array_push($args, ...array_values($f['uses']));
    }
    if (!empty($f['cpus'])) {     // processor families (core-i5, ryzen-5, apple-m ...)
        $ids = product_ids_for_cpus($f['cpus']);
        $where[] = $ids ? 'p.id IN (' . implode(',', $ids) . ')' : '0';
    }
    if (!empty($f['q']))        { $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
                                  $like = '%' . $f['q'] . '%'; array_push($args, $like, $like, $like); }
    return [implode(' AND ', $where), $args];
}

function get_products(array $f = [], int $limit = 12, int $offset = 0): array {
    [$where, $args] = product_where($f);
    $orders = [
        'newest'     => 'p.id DESC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name'       => 'p.name ASC',
    ];
    $order = $orders[$f['sort'] ?? 'newest'] ?? $orders['newest'];
    $sql = PRODUCT_SELECT . " WHERE $where ORDER BY $order LIMIT " . (int)$limit . ' OFFSET ' . (int)$offset;
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function count_products(array $f = []): int {
    [$where, $args] = product_where($f);
    $st = db()->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE $where");
    $st->execute($args);
    return (int)$st->fetchColumn();
}

function get_featured_products(int $limit = 8): array {
    // Laptops first (most expensive first), then the other featured products, newest first
    $st = db()->prepare(PRODUCT_SELECT . " WHERE p.is_active = 1 AND p.is_featured = 1
        ORDER BY (c.slug = 'laptops') DESC, CASE WHEN c.slug = 'laptops' THEN p.price END DESC, p.id DESC LIMIT " . (int)$limit);
    $st->execute();
    return $st->fetchAll();
}

function get_product_by_slug(string $slug): ?array {
    $st = db()->prepare(PRODUCT_SELECT . ' WHERE p.slug = ? AND p.is_active = 1');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

function get_products_by_ids(array $ids): array {
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (!$ids) return [];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare(PRODUCT_SELECT . " WHERE p.is_active = 1 AND p.id IN ($in)");
    $st->execute($ids);
    return $st->fetchAll();
}

function get_product_images(int $productId): array {
    $st = db()->prepare('SELECT filename FROM product_images WHERE product_id = ? ORDER BY sort_order, id');
    $st->execute([$productId]);
    return array_map('upload_name', $st->fetchAll(PDO::FETCH_COLUMN));
}

function get_related_products(int $categoryId, int $excludeId, int $limit = 4): array {
    $st = db()->prepare(PRODUCT_SELECT . ' WHERE p.is_active = 1 AND p.category_id = ? AND p.id != ? ORDER BY p.id DESC LIMIT ' . (int)$limit);
    $st->execute([$categoryId, $excludeId]);
    return $st->fetchAll();
}

/* ---------- Orders (guest checkout) ---------- */
function create_order(array $c, array $items, float $shipping): string {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $subtotal = 0;
        foreach ($items as $it) $subtotal += $it['price'] * $it['qty'];
        $total  = $subtotal + $shipping;
        $number = setting('order_prefix', 'ORD') . date('ymd') . strtoupper(bin2hex(random_bytes(2)));

        $pdo->prepare('INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, address, city, pincode, shipping, total, payment_method)
                       VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$number, $c['name'], $c['email'], $c['phone'], $c['address'], $c['city'], $c['pincode'], $shipping, $total, 'cod']);
        $orderId = (int)$pdo->lastInsertId();

        $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?,?,?,?,?)');
        $dec = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        foreach ($items as $it) {
            $dec->execute([$it['qty'], $it['id'], $it['qty']]);
            if ($dec->rowCount() === 0) throw new RuntimeException($it['name'] . ' just went out of stock.');
            $ins->execute([$orderId, $it['id'], $it['name'], $it['price'], $it['qty']]);
        }
        $pdo->commit();
        return $number;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function get_order_by_number(string $number): ?array {
    $st = db()->prepare('SELECT * FROM orders WHERE order_number = ?');
    $st->execute([$number]);
    $o = $st->fetch();
    if (!$o) return null;
    $it = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $it->execute([$o['id']]);
    $o['items'] = $it->fetchAll();
    return $o;
}


/* ---------- Laptop finder (options are built from the laptops that exist) ---------- */

/* "Intel Core i5-1235U" -> ['core-i5', 'Intel Core i5'].  Unknown chips -> null (no processor pill). */
function cpu_family(?string $cpu): ?array {
    $c = strtolower((string)$cpu);
    if ($c === '') return null;
    if (preg_match('/core\s*ultra/', $c))        return ['core-ultra', 'Intel Core Ultra'];
    if (preg_match('/core\s*(i[3579])\b/', $c, $m)) return ['core-' . $m[1], 'Intel Core ' . $m[1]];
    if (preg_match('/ryzen\s*([3579])\b/', $c, $m)) return ['ryzen-' . $m[1], 'AMD Ryzen ' . $m[1]];
    if (preg_match('/\bapple\b|\bm[1-9]\b/', $c)) return ['apple-m', 'Apple M-series'];
    return null;
}

/* Active laptops with the little we need for the finder. */
function finder_laptops(): array {
    static $rows = null;
    if ($rows !== null) return $rows;
    try {
        $st = db()->prepare("SELECT p.id, p.brand, p.price, p.specs FROM products p JOIN categories c ON c.id = p.category_id
                             WHERE p.is_active = 1 AND c.slug = 'laptops' ORDER BY p.price");
        $st->execute();
        $rows = $st->fetchAll();
        $uses = [];
        foreach (db()->query('SELECT pu.product_id, u.slug FROM product_use_cases pu JOIN use_cases u ON u.id = pu.use_case_id WHERE u.is_active = 1') as $r) {
            $uses[$r['product_id']][] = $r['slug'];
        }
    } catch (Throwable $e) {
        return $rows = [];
    }
    foreach ($rows as &$r) {
        $spec = json_decode((string)$r['specs'], true) ?: [];
        $fam  = cpu_family($spec['Processor'] ?? '');
        $r['cpu']  = $fam ? $fam[0] : '';
        $r['cpu_label'] = $fam ? $fam[1] : '';
        $r['brand_slug'] = slugify((string)$r['brand']);
        $r['uses'] = $uses[$r['id']] ?? [];
    }
    return $rows;
}

function brand_names_for(array $slugs): array {
    $out = [];
    foreach (finder_laptops() as $r) if (in_array($r['brand_slug'], $slugs, true)) $out[$r['brand']] = true;
    return array_keys($out);
}

function product_ids_for_cpus(array $fams): array {
    $ids = [];
    foreach (finder_laptops() as $r) if ($r['cpu'] !== '' && in_array($r['cpu'], $fams, true)) $ids[] = (int)$r['id'];
    return $ids;
}

/* Everything the popup needs. Returns null when there are no laptops (popup is then not shown). */
function finder_options(): ?array {
    $rows = finder_laptops();
    if (!$rows) return null;

    $prices = array_map(fn($r) => (float)$r['price'], $rows);
    $step = 5000;
    $min  = (int)(floor(min($prices) / $step) * $step);
    $max  = (int)(ceil(max($prices) / $step) * $step);
    if ($max <= $min) $max = $min + $step;

    $brands = [];
    foreach ($rows as $r) if ($r['brand'] !== '' && $r['brand'] !== null) $brands[$r['brand_slug']] = $r['brand'];
    asort($brands, SORT_NATURAL | SORT_FLAG_CASE);

    $cpuOrder = ['core-i3', 'core-i5', 'core-i7', 'core-i9', 'core-ultra', 'ryzen-3', 'ryzen-5', 'ryzen-7', 'ryzen-9', 'apple-m'];
    $cpus = [];
    foreach ($rows as $r) if ($r['cpu'] !== '') $cpus[$r['cpu']] = $r['cpu_label'];
    uksort($cpus, fn($a, $b) => (array_search($a, $cpuOrder) <=> array_search($b, $cpuOrder)));

    $uses = [];
    try {
        $have = array_unique(array_merge(...array_column($rows, 'uses') ?: [[]]));
        foreach (db()->query('SELECT slug, name FROM use_cases WHERE is_active = 1 ORDER BY sort_order, id') as $u) {
            if (in_array($u['slug'], $have, true)) $uses[$u['slug']] = $u['name'];
        }
    } catch (Throwable $e) {}

    return [
        'min' => $min, 'max' => $max, 'step' => $step,
        'brands' => $brands, 'cpus' => $cpus, 'uses' => $uses,
        'data' => array_map(fn($r) => ['p' => (int)$r['price'], 'b' => $r['brand_slug'], 'c' => $r['cpu'], 'u' => $r['uses']], $rows),
    ];
}

/* Read ?brand=asus,dell or ?brand[]=asus&brand[]=dell into a clean list of slugs. */
function list_param(string $name): array {
    $v = $_GET[$name] ?? [];
    if (is_string($v)) $v = explode(',', $v);
    if (!is_array($v)) return [];
    $v = array_map(fn($x) => preg_replace('/[^a-z0-9-]/', '', strtolower((string)$x)), $v);
    return array_values(array_unique(array_filter($v, fn($x) => $x !== '')));
}
