<?php
/* Listing: /products, /laptops (category), ?q=search, ?sort=, ?page= */
require __DIR__ . '/../config/helpers.php';

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['category'] ?? ''));
$q    = trim($_GET['q'] ?? '');
$deals = !empty($_GET['deals']);
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));

/* Laptop finder / filters:  ?max=90000&use=student,coding&brand=asus,dell&cpu=core-i5 */
$maxPrice = max(0, (int)($_GET['max'] ?? 0));
$fBrands  = list_param('brand');
$fUses    = list_param('use');
$fCpus    = list_param('cpu');
$per  = max(1, (int)setting('items_per_page', '12'));

$cat = null;
if ($slug !== '') {
    $cat = get_category_by_slug($slug);
    if (!$cat) not_found();
}

$filter = ['category' => $slug, 'q' => $q, 'sort' => $sort, 'deals' => $deals,
           'max_price' => $maxPrice, 'brands' => $fBrands, 'uses' => $fUses, 'cpus' => $fCpus];
$total  = count_products($filter);
$pages  = max(1, (int)ceil($total / $per));
$page   = min($page, $pages);
$items  = get_products($filter, $per, ($page - 1) * $per);

$base = $cat ? url($cat['slug']) : url('products');
function filter_qs(array $drop = []): array {   // the finder filters currently applied, as query-string parts
    global $maxPrice, $fBrands, $fUses, $fCpus;
    $qs = ['max' => $maxPrice ?: '', 'use' => implode(',', $fUses), 'brand' => implode(',', $fBrands), 'cpu' => implode(',', $fCpus)];
    foreach ($drop as $k) unset($qs[$k]);
    return $qs;
}
function list_link(string $base, array $extra, array $dropFilters = []): string {
    $qs = array_filter(array_merge(['q' => $_GET['q'] ?? '', 'sort' => $_GET['sort'] ?? '', 'deals' => $_GET['deals'] ?? ''], filter_qs($dropFilters), $extra), fn($v) => $v !== '' && $v !== null && $v !== 1);
    return $base . ($qs ? '?' . http_build_query($qs, '', '&', PHP_QUERY_RFC3986) : '');
}

/* Chips for the applied finder filters; clicking one removes just that filter */
function chip_href(string $base, array $override): string {
    $qs = array_merge(['q' => $_GET['q'] ?? '', 'sort' => $_GET['sort'] ?? '', 'deals' => $_GET['deals'] ?? ''], filter_qs(), $override);
    $qs = array_filter($qs, fn($v) => $v !== '' && $v !== null && $v !== 1);
    return $base . ($qs ? '?' . http_build_query($qs, '', '&', PHP_QUERY_RFC3986) : '');
}
$chips = [];
$opt = ($fUses || $fBrands || $fCpus) ? (finder_options() ?: []) : [];
if ($maxPrice) $chips[] = ['Up to ' . money($maxPrice), ['max' => '']];
foreach ($fUses   as $v) $chips[] = [$opt['uses'][$v]   ?? $v, ['use'   => implode(',', array_diff($fUses, [$v]))]];
foreach ($fCpus   as $v) $chips[] = [$opt['cpus'][$v]   ?? $v, ['cpu'   => implode(',', array_diff($fCpus, [$v]))]];
foreach ($fBrands as $v) $chips[] = [$opt['brands'][$v] ?? $v, ['brand' => implode(',', array_diff($fBrands, [$v]))]];

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
            <?php foreach (filter_qs() as $fk => $fv): if ($fv !== '' && $fv !== 0): ?><input type="hidden" name="<?= e($fk) ?>" value="<?= e($fv) ?>"><?php endif; endforeach; ?>
            <select name="sort" onchange="this.form.submit()" aria-label="Sort products">
                <?php foreach (['newest' => 'Newest', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'name' => 'Name'] as $k => $label): ?>
                    <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($chips): ?>
    <div class="applied" aria-label="Applied filters">
        <?php foreach ($chips as [$label, $over]): ?>
            <a class="chip" href="<?= e(chip_href($base, $over)) ?>" title="Remove filter"><?= e($label) ?><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></a>
        <?php endforeach; ?>
        <a class="chip-clear" href="<?= e(chip_href($base, ['max' => '', 'use' => '', 'brand' => '', 'cpu' => ''])) ?>">Clear all</a>
    </div>
    <?php endif; ?>

    <?php if ($items): ?>
        <div class="grid"><?php foreach ($items as $p) product_card($p); ?></div>
    <?php else: ?>
        <p class="notice">No products found. Try a different search or <a href="<?= e($chips ? chip_href($base, ['max' => '', 'use' => '', 'brand' => '', 'cpu' => '']) : url('products')) ?>"><?= $chips ? 'clear the filters' : 'view all products' ?></a>.</p>
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
