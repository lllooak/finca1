<?php use App\Core\View; /** @var array $r,$faqs,$similar */ $name = $r['name_he'] ?: $r['name']; $cur = $r['currency_code']; ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container">
  <div class="ah-top"><div class="asset-logo"><?= e(mb_substr($r['ticker'],0,2)) ?></div><div>
    <h1><?= e($name) ?> <span class="text-soft" style="font-size:1.1rem">(<?= e($r['ticker']) ?>)</span></h1>
    <div class="ah-meta">REIT · <?= e($r['property_type']) ?> · <?= e($r['exchange_name_he'] ?: $r['exchange_name'] ?: '') ?></div>
  </div></div>
  <div class="asset-price"><span class="px"><?= money($r['price'], $cur) ?></span><span class="chg <?= change_class($r['day_change_pct']) ?>"><?= pct($r['day_change_pct']) ?></span></div>
</div></section>
<div class="container py-4">
  <div class="stat-grid mb-4">
    <div class="stat"><div class="k">שווי שוק</div><div class="v"><?= big_number($r['market_cap'], $cur) ?></div></div>
    <div class="stat"><div class="k">תשואת דיבידנד</div><div class="v"><?= num($r['dividend_yield']) ?>%</div></div>
    <div class="stat"><div class="k">FFO</div><div class="v"><?= big_number($r['ffo'], $cur) ?></div></div>
    <div class="stat"><div class="k">שיעור תפוסה</div><div class="v"><?= $r['occupancy_rate'] ? num($r['occupancy_rate']).'%' : '—' ?></div></div>
    <div class="stat"><div class="k">מספר נכסים</div><div class="v"><?= int_num($r['properties_count']) ?></div></div>
  </div>
  <div class="row"><div class="col-lg-8">
    <div class="panel section"><div class="panel-body prose">
      <h2>סקירה כללית</h2>
      <p><?= $r['description_he'] ? e($r['description_he']) : "{$name} ({$r['ticker']}) היא קרן ריט (REIT) המתמחה בנדל\"ן מסוג " . e($r['property_type']) . ", עם תשואת דיבידנד של " . num($r['dividend_yield']) . "%. קרנות REIT מחויבות לחלק את מרבית רווחיהן כדיבידנד, ולכן הן פופולריות בקרב משקיעי הכנסה." ?></p>
      <h2>ניתוח דיבידנד ו-FFO</h2>
      <p>קרנות REIT נמדדות בעיקר לפי FFO (Funds From Operations) — מדד תזרים מזומנים תפעולי המתאים יותר מהרווח הנקי החשבונאי בשל ניטרול הפחתות. תשואת הדיבידנד של <?= num($r['dividend_yield']) ?>% משקפת את ההכנסה השוטפת שהמשקיע מקבל יחסית למחיר המניה.</p>
      <h2>שיעור תפוסה ואיכות הנכסים</h2>
      <p>שיעור התפוסה של <?= $r['occupancy_rate'] ? num($r['occupancy_rate']).'%' : 'הקרן' ?> הוא מדד מפתח לאיכות תיק הנכסים ולחוזק הביקוש. תפוסה גבוהה ויציבה מעידה על נכסים איכותיים במיקומים מבוקשים ועל ניהול יעיל.</p>
    </div></div>
    <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
    <?= View::partial('partials/disclaimer') ?>
  </div>
  <div class="col-lg-4"><?php if (!empty($similar)): ?>
    <div class="panel section"><div class="panel-head"><h3>קרנות REIT דומות</h3></div><div class="panel-body">
      <?php foreach ($similar as $s): ?><a class="d-flex justify-content-between py-2 border-bottom" href="<?= url('reit/' . e($s['ticker'])) ?>"><span><strong><?= e($s['ticker']) ?></strong></span><span class="text-up"><?= num($s['dividend_yield']) ?>%</span></a><?php endforeach; ?>
    </div></div>
  <?php endif; ?></div></div>
</div>
