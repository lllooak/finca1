<?php use App\Core\View; /** @var array $c,$faqs,$similar */ $name = $c['name_he'] ?: $c['name']; ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container">
  <h1><?= e($name) ?></h1>
  <div class="ah-meta"><?= e($c['category']) ?> · <?= e($c['unit']) ?></div>
  <div class="asset-price"><span class="px"><?= money($c['price'], $c['currency_code']) ?></span><span class="chg <?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></span></div>
</div></section>
<div class="container py-4"><div class="row"><div class="col-lg-8">
  <div class="panel section"><div class="panel-body prose">
    <h2>סקירה כללית</h2>
    <p><?= $c['description_he'] ? e($c['description_he']) : "{$name} היא סחורה בקטגוריית " . e($c['category']) . " הנסחרת במחיר " . money($c['price'], $c['currency_code']) . " ל" . e($c['unit']) . ". מחירי הסחורות מושפעים מגורמי היצע וביקוש גלובליים, מצב גיאופוליטי, שער הדולר ותנאי מאקרו." ?></p>
    <h2>גורמים המשפיעים על המחיר</h2>
    <p>מחיר הסחורה מושפע ממגוון גורמים: רמת הביקוש התעשייתי והצרכני, היצע גלובלי, מלאים, מדיניות מוניטרית, אינפלציה ושער החליפין של הדולר האמריקאי שבו נקובות מרבית הסחורות. סחורות נחשבות לעיתים כגידור (Hedge) מפני אינפלציה.</p>
  </div></div>
  <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div><div class="col-lg-4"><?php if (!empty($similar)): ?>
  <div class="panel section"><div class="panel-head"><h3>סחורות נוספות</h3></div><div class="panel-body">
    <?php foreach ($similar as $s): ?><a class="d-flex justify-content-between py-2 border-bottom" href="<?= url('commodity/' . e($s['slug'])) ?>"><span><?= e($s['name_he'] ?: $s['name']) ?></span><span class="<?= change_class($s['day_change_pct']) ?>"><?= pct($s['day_change_pct']) ?></span></a><?php endforeach; ?>
  </div></div>
<?php endif; ?></div></div></div>
