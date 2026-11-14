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