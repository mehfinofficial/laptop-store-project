<?php
/* Shown only to the browser session that just placed the order */
require __DIR__ . '/../config/helpers.php';

$number = $_SESSION['last_order'] ?? '';
$order  = $number !== '' ? get_order_by_number($number) : null;
if (!$order) redirect('');

$pageTitle = 'Order placed';
require __DIR__ . '/../partials/header.php';
?>
<main class="container narrow">
    <div class="page-title"><h1>Thank you, <?= e($order['customer_name']) ?>.</h1></div>
    <p>Your order <strong><?= e($order['order_number']) ?></strong> is placed. We will call <?= e($order['customer_phone']) ?> to confirm it.</p>

    <table class="table">
        <?php foreach ($order['items'] as $it): ?>
            <tr><td><?= e($it['product_name']) ?> x <?= (int)$it['quantity'] ?></td><td><?= money($it['price'] * $it['quantity']) ?></td></tr>
        <?php endforeach; ?>
        <tr><td>Shipping</td><td><?= $order['shipping'] > 0 ? money($order['shipping']) : 'Free' ?></td></tr>
        <tr><td><strong>Total (pay on delivery)</strong></td><td><strong><?= money($order['total']) ?></strong></td></tr>
    </table>
    <p class="muted">Delivering to: <?= e($order['address']) ?>, <?= e($order['city']) ?> <?= e($order['pincode']) ?></p>
    <p><a class="btn" href="<?= e(url('products')) ?>">Continue shopping</a></p>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
