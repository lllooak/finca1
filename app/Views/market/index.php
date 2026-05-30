<?php use App\Core\View; /** @var array $indices,$gainers,$losers,$active,$sectors */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi bi-graph-up"></i> סקירת שוק</h1>

  <div class="market-strip mt-0 mb-4" style="position:static">
    <?php foreach ($indices as $ix): ?>
    <a class="mkt-card" href="<?= url('index/' . e($ix['slug'])) ?>">
      <div class="mc-name"><?= e($ix['name_he'] ?: $ix['name']) ?></div>
      <div class="mc-val"><?= num($ix['value']) ?></div>
      <div class="mc-chg <?= change_class($ix['day_change_pct']) ?>"><?= pct($ix['day_change_pct']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="row">
    <div class="col-lg-4">
      <div class="panel section"><div class="panel-head"><h2><i class="bi bi-graph-up-arrow text-up"></i> העולות ביותר</h2></div>
        <div class="panel-body p-0"><?= View::partial('partials/stocks_table', ['rows' => $gainers]) ?></div></div>
    </div>
    <div class="col-lg-4">
      <div class="panel section"><div class="panel-head"><h2><i class="bi bi-graph-down-arrow text-down"></i> היורדות ביותר</h2></div>
        <div class="panel-body p-0"><?= View::partial('partials/stocks_table', ['rows' => $losers]) ?></div></div>
    </div>
    <div class="col-lg-4">
      <div class="panel section"><div class="panel-head"><h2><i class="bi bi-activity"></i> הנסחרות ביותר</h2></div>
        <div class="panel-body p-0"><?= View::partial('partials/stocks_table', ['rows' => $active]) ?></div></div>
    </div>
  </div>

  <div class="section">
    <h2 class="section-title"><i class="bi bi-grid-3x3-gap"></i> ביצועי סקטורים</h2>
    <div class="chip-grid">
      <?php foreach ($sectors as $s): ?>
      <a class="chip-card" href="<?= url('sector/' . e($s['slug'])) ?>">
        <div class="cc-title"><i class="bi <?= e($s['icon'] ?: 'bi-diagram-2') ?>"></i> <?= e($s['name_he'] ?: $s['name']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?= View::partial('partials/disclaimer') ?>
</div>
