<?php
/* Session cart: $_SESSION['cart'] = [product_id => qty] */

function cart_get(): array { return $_SESSION['cart'] ?? []; }

function cart_add(int $id, int $qty = 1): bool {
    $p = get_products_by_ids([$id])[0] ?? null;
    if (!$p || $p['stock'] < 1) return false;
    $cart = cart_get();
    $cart[$id] = min(($cart[$id] ?? 0) + $qty, max_qty(), (int)$p['stock']);
    $_SESSION['cart'] = $cart;
    return true;
}

function cart_set(int $id, int $qty): void {
    $cart = cart_get();
    if ($qty <= 0) unset($cart[$id]); else $cart[$id] = min($qty, max_qty());
    $_SESSION['cart'] = $cart;
}

function cart_remove(int $id): void { cart_set($id, 0); }
function cart_clear(): void { unset($_SESSION['cart']); }
function cart_count(): int { return array_sum(cart_get()); }

/* Full cart lines with product data. 'ok' = enough stock for the quantity. */
function cart_items(): array {
    $cart = cart_get();
    if (!$cart) return [];
    $items = [];
    foreach (get_products_by_ids(array_keys($cart)) as $p) {
        $qty = (int)$cart[$p['id']];
        $items[] = [
            'id' => (int)$p['id'], 'name' => $p['name'], 'slug' => $p['slug'],
            'category_slug' => $p['category_slug'], 'image' => $p['image'],
            'price' => (float)$p['price'], 'qty' => $qty, 'stock' => (int)$p['stock'],
            'line' => (float)$p['price'] * $qty, 'ok' => $p['stock'] >= $qty,
        ];
    }
    return $items;
}

function cart_subtotal(array $items): float {
    return array_sum(array_column($items, 'line'));
}
