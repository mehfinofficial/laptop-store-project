<?php
/* Guest checkout (cash on delivery) */
require __DIR__ . '/../config/helpers.php';

$items = cart_items();
if (!$items) redirect('cart');
foreach ($items as $it) if (!$it['ok']) redirect('cart');

$subtotal = cart_subtotal($items);
$shipping = shipping_for($subtotal);
$v = ['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'city' => setting('city'), 'pincode' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');

    if (mb_strlen($v['name']) < 2)                          $errors['name']    = 'Enter your full name.';
    if (!preg_match('/^[0-9+\- ]{10,15}$/', $v['phone']))   $errors['phone']   = 'Enter a valid phone number.';
    if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email or leave it empty.';
    if (mb_strlen($v['address']) < 8)                       $errors['address'] = 'Enter your full address.';
    if ($v['city'] === '')                                  $errors['city']    = 'Enter your city.';
    if (!preg_match('/^\d{6}$/', $v['pincode']))            $errors['pincode'] = 'Enter a 6-digit pincode.';

    if (!$errors) {
        try {
            $number = create_order($v, $items, $shipping);
            cart_clear();
            $_SESSION['last_order'] = $number;
            redirect('order-success');
        } catch (Throwable $e) {
            $errors['form'] = $e instanceof RuntimeException ? $e->getMessage() : 'Could not place the order. Please try again.';
        }
    }
}

function field(string $name, string $label, array $v, array $errors, string $type = 'text', bool $area = false): void {
    echo '<div class="field"><label for="' . $name . '">' . e($label) . '</label>';
    if ($area) echo '<textarea id="' . $name . '" name="' . $name . '" rows="3" required>' . e($v[$name]) . '</textarea>';
    else echo '<input id="' . $name . '" type="' . $type . '" name="' . $name . '" value="' . e($v[$name]) . '"' . ($name === 'email' ? '' : ' required') . '>';
    if (isset($errors[$name])) echo '<p class="error">' . e($errors[$name]) . '</p>';
    echo '</div>';
}

$pageTitle = 'Checkout';
require __DIR__ . '/../partials/header.php';
?>
<main class="container">
    <div class="page-title"><h1>Checkout</h1></div>
    <?php if (isset($errors['form'])): ?><p class="notice"><?= e($errors['form']) ?></p><?php endif; ?>

    <div class="two-col">
        <form method="post" action="<?= e(url('checkout')) ?>" novalidate>
            <?= csrf_field() ?>
            <?php field('name', 'Full name', $v, $errors); field('phone', 'Phone', $v, $errors, 'tel'); field('email', 'Email (optional)', $v, $errors, 'email');
                  field('address', 'Address', $v, $errors, 'text', true); field('city', 'City', $v, $errors); field('pincode', 'Pincode', $v, $errors); ?>
            <p class="muted">Payment: cash on delivery.</p>
            <button class="btn alt" type="submit">Place order</button>
        </form>

        <aside class="summary">
            <h2>Order summary</h2>
            <?php foreach ($items as $it): ?>
                <div class="row"><span><?= e($it['name']) ?> x <?= $it['qty'] ?></span><span><?= money($it['line']) ?></span></div>
            <?php endforeach; ?>
            <div class="row"><span>Shipping</span><span><?= $shipping > 0 ? money($shipping) : 'Free' ?></span></div>
            <div class="row total"><span>Total</span><span><?= money($subtotal + $shipping) ?></span></div>
        </aside>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
