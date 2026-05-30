<?php use App\Core\View; /** @var array $c,$faqs,$similar */ $name = $c['name_he'] ?: $c['name']; ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container">
  <h1><?= e($name) ?> <span class="text-soft" style="font-size:1.1rem">(<?= e($c['symbol']) ?>)</span></h1>
  <div class="ah-meta"><?= e($c['category'] ?: 'מטבע דיגיטלי') ?> · דירוג #<?= int_num($c['rank']) ?></div>
  <div class="asset-price"><span class="px"><?= money($c['price'], 'USD', $c['price'] < 1 ? 4 : 2) ?></span><span class="chg <?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></span></div>
</div></section>
<div class="container py-4">
  <div class="stat-grid mb-4">
    <div class="stat"><div class="k">שווי שוק</div><div class="v"><?= big_number($c['market_cap'], 'USD') ?></div></div>
    <div class="stat"><div class="k">נפח 24ש'</div><div class="v"><?= big_number($c['volume_24h'], 'USD') ?></div></div>
    <div class="stat"><div class="k">היצע במחזור</div><div class="v"><?= big_number($c['circulating_supply']) ?></div></div>
    <div class="stat"><div class="k">היצע מקסימלי</div><div class="v"><?= $c['max_supply'] ? big_number($c['max_supply']) : '∞' ?></div></div>
    <div class="stat"><div class="k">שיא כל הזמנים</div><div class="v"><?= money($c['ath'], 'USD', $c['ath'] < 1 ? 4 : 2) ?></div></div>
  </div>
  <div class="row"><div class="col-lg-8">
    <div class="panel section"><div class="panel-body prose">
      <h2>סקירה כללית</h2>
      <p><?= $c['description_he'] ? e($c['description_he']) : "{$name} ({$c['symbol']}) הוא נכס קריפטוגרפי הנסחר בשווקים הדיגיטליים עם שווי שוק של כ-" . big_number($c['market_cap'], 'USD') . ". מטבעות קריפטו מתאפיינים בתנודתיות גבוהה ובסיכון מוגבר, ומיועדים למשקיעים בעלי סובלנות סיכון גבוהה." ?></p>
      <h2>נתוני שוק</h2>
      <p>המטבע ממוקם במקום ה-<?= int_num($c['rank']) ?> בדירוג שווי השוק העולמי. ההיצע במחזור עומד על <?= big_number($c['circulating_supply']) ?> יחידות. נתוני ההיצע והביקוש משפיעים ישירות על דינמיקת המחיר של הנכס.</p>
    </div></div>
    <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
    <?= View::partial('partials/disclaimer') ?>
  </div>
  <div class="col-lg-4"><?php if (!empty($similar)): ?>
    <div class="panel section"><div class="panel-head"><h3>מטבעות נוספים</h3></div><div class="panel-body">
      <?php foreach ($similar as $s): ?><a class="d-flex justify-content-between py-2 border-bottom" href="<?= url('crypto/' . e($s['slug'])) ?>"><span><strong><?= e($s['symbol']) ?></strong></span><span class="<?= change_class($s['day_change_pct']) ?>"><?= pct($s['day_change_pct']) ?></span></a><?php endforeach; ?>
    </div></div>
  <?php endif; ?></div></div>
</div>
