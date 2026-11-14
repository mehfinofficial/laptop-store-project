<?php
/* Laptop finder popup. Needs $finder = finder_options() (set in the page). Options come from the laptops in the DB,
   so a new brand / processor / use case shows up by itself. Submits a normal GET to /laptops (works without JS too). */
if (empty($finder)) return;
$__img = gift_image('Laptop2') ?: gift_image('Laptop');
$__sym = setting('currency_symbol', '₹');
?>
<div class="fm-backdrop" id="fmBackdrop"></div>
<section class="fm" id="finderModal" role="dialog" aria-modal="true" aria-labelledby="fmTitle" aria-hidden="true"
         data-sym="<?= e($__sym) ?>" data-min="<?= (int)$finder['min'] ?>" data-max="<?= (int)$finder['max'] ?>">
    <button type="button" class="fm-close" data-finder-close aria-label="Close">
        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
    <aside class="fm-side">
        <div class="fm-side-text">
            <span class="fm-kicker">Laptop finder</span>
            <h2 id="fmTitle">Find your perfect laptop</h2>
            <p>Pick what matters. We'll filter the store for you.</p>
        </div>
        <?php if ($__img): ?><img class="fm-img" src="<?= e($__img) ?>" alt="" decoding="async"><?php endif; ?>
    </aside>

    <form class="fm-form" method="get" action="<?= e(url('laptops')) ?>">
        <div class="fm-body">
            <fieldset class="fm-group">
                <legend>Budget</legend>
                <output class="fm-budget" id="fmBudget">Any budget</output>
                <input class="fm-range" type="range" name="max" id="fmRange" aria-label="Maximum budget"
                       min="<?= (int)$finder['min'] ?>" max="<?= (int)$finder['max'] ?>" step="<?= (int)$finder['step'] ?>" value="<?= (int)$finder['max'] ?>">
                <div class="fm-scale"><span><?= e(money($finder['min'])) ?></span><span><?= e(money($finder['max'])) ?></span></div>
            </fieldset>

            <?php if ($finder['cpus']): ?>
            <fieldset class="fm-group">
                <legend>Processor</legend>
                <div class="fm-pills">
                    <?php foreach ($finder['cpus'] as $slug => $label): ?>
                        <label class="fp"><input type="checkbox" name="cpu[]" value="<?= e($slug) ?>"><span><?= e($label) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php endif; ?>

            <?php if ($finder['uses']): ?>
            <fieldset class="fm-group">
                <legend>Use case</legend>
                <div class="fm-pills">
                    <?php foreach ($finder['uses'] as $slug => $label): ?>
                        <label class="fp"><input type="checkbox" name="use[]" value="<?= e($slug) ?>"><span><?= e($label) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php endif; ?>

            <?php if ($finder['brands']): ?>
            <fieldset class="fm-group">
                <legend>Brand</legend>
                <div class="fm-pills">
                    <?php foreach ($finder['brands'] as $slug => $label): ?>
                        <label class="fp"><input type="checkbox" name="brand[]" value="<?= e($slug) ?>"><span><?= e($label) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php endif; ?>
        </div>

        <div class="fm-foot">
            <button type="button" class="fm-reset" id="fmReset">Reset</button>
            <button type="submit" class="fm-go" id="fmGo">Show laptops</button>
        </div>
    </form>
</section>
<script type="application/json" id="finderData"><?= json_encode($finder['data'], JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
