<?php use App\Core\View; /** @var array $presets */
$all = [
  'stocks' => ['מניות', 'bi-bar-chart'], 'dividend' => ['דיבידנד', 'bi-cash-coin'],
  'ai' => ['בינה מלאכותית', 'bi-cpu'], 'robotics' => ['רובוטיקה', 'bi-robot'],
  'hong-kong' => ['הונג קונג', 'bi-buildings'], 'growth' => ['צמיחה', 'bi-graph-up-arrow'],
  'value' => ['ערך', 'bi-tags'],
];
$extra = [
  'reit' => ['REIT', 'bi-building', 'reits'], 'bond' => ['אג"ח', 'bi-bank', 'bonds'],
  'crypto' => ['קריפטו', 'bi-currency-bitcoin', 'crypto'], 'etf' => ['ETF', 'bi-collection', 'etfs'],
];
?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi bi-funnel"></i> סקרינרים</h1>
  <p class="text-soft">בחר סקרינר מוכן או השתמש בסקרינר המניות המלא לסינון מתקדם.</p>
  <div class="chip-grid">
    <?php foreach ($all as $slug => $info): ?>
    <a class="chip-card" href="<?= url('screener/' . $slug) ?>"><div class="cc-title"><i class="bi <?= $info[1] ?>"></i> סקרינר <?= e($info[0]) ?></div><div class="cc-meta"><?= e($presets[$slug]['desc'] ?? '') ?></div></a>
    <?php endforeach; ?>
    <?php foreach ($extra as $info): ?>
    <a class="chip-card" href="<?= url($info[2]) ?>"><div class="cc-title"><i class="bi <?= $info[1] ?>"></i> סקרינר <?= e($info[0]) ?></div><div class="cc-meta">עיון וסינון <?= e($info[0]) ?></div></a>
    <?php endforeach; ?>
  </div>
</div>
