<?php use App\Core\View; /** @var array $row,$stocks,$etfs,$faqs; @var string $name,$kind */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container">
  <h1><?= e($name) ?></h1>
  <?php if (!empty($row['category'])): ?><div class="ah-meta"><?= e($row['category']) ?></div><?php endif; ?>
</div></section>
<div class="container py-4">
  <div class="panel section"><div class="panel-body prose">
    <h2 id="overview">סקירה כללית</h2>
    <p><?= !empty($row['overview']) ? nl2br(e($row['overview'])) : (!empty($row['description']) ? e($row['description']) : "סקירה מקיפה של {$name}, הכוללת את המניות המובילות בקטגוריה, ניתוח מגמות, הזדמנויות וסיכונים. הקטגוריה מרכזת חברות בעלות מאפיינים משותפים, ומאפשרת למשקיעים להתמקד בתחום ספציפי בהתאם לאסטרטגיית ההשקעה שלהם.") ?></p>
  </div></div>

  <?php if (!empty($stocks)): ?>
  <div class="panel section">
    <div class="panel-head"><h2><i class="bi bi-bar-chart"></i> מניות קשורות (<?= count($stocks) ?>)</h2></div>
    <div class="panel-body p-0"><?= View::partial('partials/stocks_table', ['rows' => $stocks, 'showDividend' => true]) ?></div>
  </div>
  <?php endif; ?>

  <?php if (!empty($etfs)): ?>
  <div class="panel section"><div class="panel-head"><h2><i class="bi bi-collection"></i> קרנות סל קשורות</h2></div>
    <div class="panel-body">
      <?php foreach ($etfs as $e): ?><a class="tag-pill" style="margin:4px;display:inline-block" href="<?= url('etf/' . e($e['ticker'])) ?>"><?= e($e['ticker']) ?> · <?= e(excerpt($e['name_he'] ?: $e['name'], 30)) ?></a><?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
