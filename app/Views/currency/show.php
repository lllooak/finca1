<?php use App\Core\View; /** @var array $c,$faqs,$similar */ $name = $c['name_he'] ?: $c['name']; ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container">
  <h1><?= e($name) ?> <span class="text-soft" style="font-size:1.1rem">(<?= e($c['pair'] ?: $c['code']) ?>)</span></h1>
  <div class="asset-price"><span class="px"><?= num($c['rate'], 4) ?></span><span class="chg <?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></span></div>
</div></section>
<div class="container py-4"><div class="row"><div class="col-lg-8">
  <div class="panel section"><div class="panel-body prose">
    <h2>סקירה כללית</h2>
    <p><?= $c['description_he'] ? e($c['description_he']) : "זוג המטבעות {$name} ({$c['pair']}) נסחר כעת בשער של " . num($c['rate'], 4) . ". שוק המט\"ח (Forex) הוא השוק הפיננסי הגדול בעולם, ושערי החליפין מושפעים מהפרשי ריבית בין מדינות, נתוני מאקרו, מדיניות בנקים מרכזיים וגורמים גיאופוליטיים." ?></p>
    <h2>גורמים המשפיעים על השער</h2>
    <p>שער החליפין מושפע מהפרשי הריבית בין שתי המדינות, מאזן הסחר, רמת האינפלציה, יציבות פוליטית ותיאבון הסיכון בשווקים הגלובליים. בנקים מרכזיים משפיעים על השער באמצעות החלטות ריבית והתערבות בשוק.</p>
  </div></div>
  <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div><div class="col-lg-4"><?php if (!empty($similar)): ?>
  <div class="panel section"><div class="panel-head"><h3>מטבעות נוספים</h3></div><div class="panel-body">
    <?php foreach ($similar as $s): ?><a class="d-flex justify-content-between py-2 border-bottom" href="<?= url('currency/' . e($s['slug'])) ?>"><span><?= e($s['pair']) ?></span><span class="<?= change_class($s['day_change_pct']) ?>"><?= pct($s['day_change_pct']) ?></span></a><?php endforeach; ?>
  </div></div>
<?php endif; ?></div></div></div>
