<?php
/* Listing: /products, /laptops (category), ?q=search, ?sort=, ?page= */
require __DIR__ . '/../config/helpers.php';

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['category'] ?? ''));
$q    = trim($_GET['q'] ?? '');
$deals = !empty($_GET['deals']);
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = max(1, (int)setting('items_per_page', '12'));

$cat = null;
if ($slug !== '') {
    $cat = get_category_by_slug($slug);
    if (!$cat) not_found();
}

$filter = ['category' => $slug, 'q' => $q, 'sort' => $sort, 'deals' => $deals];
$total  = count_products($filter);
$pages  = max(1, (int)ceil($total / $per));
$page   = min($page, $pages);
$items  = get_products($filter, $per, ($page - 1) * $per);

$base = $cat ? url($cat['slug']) : url('products');
function list_link(string $base, array $extra): string {
    $qs = array_filter(array_merge(['q' => $_GET['q'] ?? '', 'sort' => $_GET['sort'] ?? '', 'deals' => $_GET['deals'] ?? ''], $extra), fn($v) => $v !== '' && $v !== null && $v !== 1);
    return $base . ($qs ? '?' . http_build_query($qs) : '');
}

$pageTitle = $q !== '' ? 'Search: ' . $q : ($cat ? $cat['name'] : ($deals ? 'Deals' : 'All products'));
$metaDesc  = $cat ? 'Buy ' . $cat['name'] . ' online at ' . setting('store_name') . '. Real specs and clear prices.' : null;
$canonical = full_url($cat ? $cat['slug'] : 'products') . ($deals ? '?deals=1' : '') . ($page > 1 ? ($deals ? '&' : '?') . 'page=' . $page : '');
require __DIR__ . '/../partials/header.php';
?>
<main class="container">
    <div class="page-title">
        <h1><?= e($pageTitle) ?></h1>
        <p class="muted"><?= $total ?> product<?= $total === 1 ? '' : 's' ?></p>
    </div>

    <div class="toolbar">
        <div class="pills">
            <a href="<?= e(url('products')) ?>" class="<?= !$cat && !$deals ? 'on' : '' ?>">All</a>
            <a href="<?= e(url('products?deals=1')) ?>" class="<?= $deals ? 'on' : '' ?>">Deals</a>
            <?php foreach (get_categories() as $c): ?>
                <a href="<?= e(url($c['slug'])) ?>" class="<?= $cat && $cat['id'] == $c['id'] ? 'on' : '' ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <form method="get" action="<?= e($base) ?>">
            <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
            <?php if ($deals): ?><input type="hidden" name="deals" value="1"><?php endif; ?>
            <select name="sort" onchange="this.form.submit()" aria-label="Sort products">
                <?php foreach (['newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'name' => 'Name'] as $k => $label): ?>
                    <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($items): ?>
        <div class="grid"><?php foreach ($items as $p) product_card($p); ?></div>
    <?php else: ?>
        <p class="notice">No products found. Try a different search or <a href="<?= e(url('products')) ?>">view all products</a>.</p>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="Pages">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php if ($i === $page): ?><span class="on"><?= $i ?></span>
            <?php else: ?><a href="<?= e(list_link($base, ['page' => $i])) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
