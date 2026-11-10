<?php
/* Cart: GET shows it, POST (add / update / remove) changes it then redirects */
require __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'add') {
        if (cart_add($id, max(1, (int)($_POST['qty'] ?? 1)))) flash('Added to cart.');
        else flash('Sorry, that product is not available.');
    } elseif ($action === 'update') {
        cart_set($id, (int)($_POST['qty'] ?? 1));
    } elseif ($action === 'remove') {
        cart_remove($id);
    }
    redirect('cart');
}

$items    = cart_items();
$subtotal = cart_subtotal($items);
$shipping = $items ? shipping_for($subtotal) : 0;
$blocked  = array_filter($items, fn($i) => !$i['ok']);

$pageTitle = 'Your cart';
require __DIR__ . '/../partials/header.php';
?>
<main class="container">
    <div class="page-title"><h1>Your cart</h1></div>
    <?php if ($m = flash()): ?><p class="notice"><?= e($m) ?></p><?php endif; ?>

    <?php if (!$items): ?>
        <p class="notice">Your cart is empty. <a href="<?= e(url('products')) ?>">Browse products</a>.</p>
    <?php else: ?>
    <div class="two-col">
        <table class="table">
            <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><div class="cart-prod">
                        <a class="thumb" href="<?= e(url($it['category_slug'] . '/' . $it['slug'])) ?>"><?= render_image($it['image'], $it['name']) ?></a>
                        <div><a href="<?= e(url($it['category_slug'] . '/' . $it['slug'])) ?>"><?= e($it['name']) ?></a>
                        <?php if (!$it['ok']): ?><p class="error">Only <?= $it['stock'] ?> in stock. Lower the quantity.</p><?php endif; ?></div>
                    </div></td>
                    <td><?= money($it['price']) ?></td>
                    <td>
                        <form method="post" action="<?= e(url('cart')) ?>">
                            <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $it['id'] ?>">
                            <input class="qty-input" type="number" name="qty" value="<?= $it['qty'] ?>" min="0" max="<?= max_qty() ?>" onchange="this.form.submit()" aria-label="Quantity">
                        </form>
                    </td>
                    <td><?= money($it['line']) ?></td>
                    <td>
                        <form method="post" action="<?= e(url('cart')) ?>">
                            <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= $it['id'] ?>">
                            <button class="link-btn" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <aside class="summary">
            <div class="row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
            <div class="row"><span>Shipping</span><span><?= $shipping > 0 ? money($shipping) : 'Free' ?></span></div>
            <div class="row total"><span>Total</span><span><?= money($subtotal + $shipping) ?></span></div>
            <p class="muted">Pay cash on delivery.</p>
            <?php if ($blocked): ?><button class="btn" disabled>Checkout</button>
            <?php else: ?><a class="btn" href="<?= e(url('checkout')) ?>">Checkout</a><?php endif; ?>
        </aside>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
