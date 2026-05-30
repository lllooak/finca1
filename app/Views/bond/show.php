<?php use App\Core\View; /** @var array $b,$faqs,$similar */ $name = $b['name_he'] ?: $b['name']; $cur = $b['currency_code']; ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container">
  <h1><?= e($name) ?></h1>
  <div class="ah-meta"><?= e($b['issuer']) ?> · <?= e($b['bond_type']) ?></div>
  <div class="asset-price"><span class="px text-up"><?= num($b['yield']) ?>%</span><span class="chg text-soft">תשואה לפדיון</span></div>
</div></section>
<div class="container py-4">
  <div class="stat-grid mb-4">
    <div class="stat"><div class="k">תשואה</div><div class="v"><?= num($b['yield']) ?>%</div></div>
    <div class="stat"><div class="k">קופון</div><div class="v"><?= num($b['coupon']) ?>%</div></div>
    <div class="stat"><div class="k">מח"מ</div><div class="v"><?= num($b['duration'], 2) ?></div></div>
    <div class="stat"><div class="k">דירוג אשראי</div><div class="v"><?= e($b['credit_rating']) ?></div></div>
    <div class="stat"><div class="k">מועד פדיון</div><div class="v"><?= he_date($b['maturity_date']) ?></div></div>
    <div class="stat"><div class="k">רמת סיכון</div><div class="v"><?= e($b['risk_level']) ?></div></div>
  </div>
  <div class="row"><div class="col-lg-8">
    <div class="panel section"><div class="panel-body prose">
      <h2>סקירה כללית</h2>
      <p><?= $b['description_he'] ? e($b['description_he']) : "אג\"ח {$name} מסוג " . e($b['bond_type']) . " מציעה תשואה לפדיון של " . num($b['yield']) . "% עם דירוג אשראי {$b['credit_rating']}. איגרות חוב הן מכשיר חוב שבו המשקיע מלווה כסף למנפיק בתמורה לתשלומי ריבית (קופון) תקופתיים והחזר הקרן במועד הפדיון." ?></p>
      <h2>ניתוח תשואה</h2>
      <p>התשואה לפדיון (YTM) של האג\"ח עומדת על <?= num($b['yield']) ?>%, נתון המשקלל את תשלומי הקופון העתידיים ואת ההפרש בין מחיר השוק לערך הנקוב. תשואה זו מהווה בנצ'מרק להשוואה מול אג"ח אחרות באותה רמת סיכון ומח"מ דומה.</p>
      <h2>ניתוח אשראי</h2>
      <p>דירוג האשראי <?= e($b['credit_rating']) ?> משקף את הערכת חברות הדירוג ליכולת המנפיק לעמוד בהתחייבויותיו. דירוג גבוה מעיד על סיכון אשראי נמוך ולרוב מלווה בתשואה נמוכה יותר, ולהפך.</p>
      <h2>סיכון ריבית</h2>
      <p>המח"מ (Duration) של <?= num($b['duration'], 2) ?> שנים מודד את רגישות מחיר האג"ח לשינויים בריבית. ככל שהמח"מ ארוך יותר, כך תנודתיות המחיר בתגובה לשינויי ריבית גבוהה יותר. עלייה בריבית השוק מובילה לירידה במחיר האג"ח, וירידה בריבית מובילה לעלייה.</p>
      <h2>סיכון אינפלציה</h2>
      <p>אינפלציה שוחקת את הערך הריאלי של תשלומי הקופון והקרן. אג"ח בריבית קבועה חשופות במיוחד לסיכון זה, בעוד שאג"ח צמודות מדד מספקות הגנה חלקית מפני שחיקת הכוח הקנייה.</p>
    </div></div>
    <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
    <?= View::partial('partials/disclaimer') ?>
  </div>
  <div class="col-lg-4"><?php if (!empty($similar)): ?>
    <div class="panel section"><div class="panel-head"><h3>אג"ח דומות</h3></div><div class="panel-body">
      <?php foreach ($similar as $s): ?>
      <a class="d-flex justify-content-between py-2 border-bottom" href="<?= url('bond/' . e($s['slug'])) ?>"><span><?= e(excerpt($s['name_he'] ?: $s['name'], 30)) ?></span><span class="text-up"><?= num($s['yield']) ?>%</span></a>
      <?php endforeach; ?>
    </div></div>
  <?php endif; ?></div></div>
</div>
