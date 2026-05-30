<?php use App\Core\View; /** @var array $ix,$faqs,$constituents */ $name = $ix['name_he'] ?: $ix['name']; ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container">
  <h1><?= e($name) ?> <span class="text-soft" style="font-size:1.1rem">(<?= e($ix['symbol']) ?>)</span></h1>
  <div class="ah-meta"><?= e($ix['country_name_he'] ?: $ix['country_name'] ?: '') ?> · שקלול לפי <?= e($ix['weighting_method'] ?: 'שווי שוק') ?></div>
  <div class="asset-price"><span class="px"><?= num($ix['value']) ?></span><span class="chg <?= change_class($ix['day_change_pct']) ?>"><?= pct($ix['day_change_pct']) ?></span></div>
</div></section>
<div class="container py-4">
  <div class="stat-grid mb-4">
    <div class="stat"><div class="k">ערך נוכחי</div><div class="v"><?= num($ix['value']) ?></div></div>
    <div class="stat"><div class="k">מספר מניות</div><div class="v"><?= int_num($ix['constituents_count']) ?></div></div>
    <div class="stat"><div class="k">תשואה YTD</div><div class="v <?= change_class($ix['ytd_return']) ?>"><?= pct($ix['ytd_return']) ?></div></div>
    <div class="stat"><div class="k">תשואה שנה</div><div class="v <?= change_class($ix['return_1y']) ?>"><?= pct($ix['return_1y']) ?></div></div>
    <div class="stat"><div class="k">תשואה 5ש'</div><div class="v <?= change_class($ix['return_5y']) ?>"><?= pct($ix['return_5y']) ?></div></div>
  </div>
  <div class="row"><div class="col-lg-8">
    <div class="panel section"><div class="panel-body prose">
      <h2>סקירת המדד</h2>
      <p><?= $ix['description_he'] ? e($ix['description_he']) : "מדד {$name} הוא מדד מניות מוביל הכולל כ-" . int_num($ix['constituents_count']) . " חברות. המדד משוקלל לפי " . e($ix['weighting_method'] ?: 'שווי שוק') . " ומשמש כברומטר מרכזי למצב השוק ולביצועי המניות הנכללות בו." ?></p>
      <h2>שיטת השקלול</h2>
      <p>המדד משתמש בשיטת שקלול לפי <?= e($ix['weighting_method'] ?: 'שווי שוק') ?>. בשיטה זו, משקל כל מניה במדד נקבע בהתאם לקריטריון השקלול, כך שחברות גדולות יותר משפיעות יותר על תנועת המדד. הבנת שיטת השקלול חיונית לפרשנות נכונה של ביצועי המדד.</p>
      <h2>ביצועים היסטוריים</h2>
      <p>מתחילת השנה רשם המדד תשואה של <?= pct($ix['ytd_return']) ?>, ובשנה האחרונה <?= pct($ix['return_1y']) ?>. בחינת הביצועים לאורך תקופות שונות מספקת פרספקטיבה על מגמות השוק ועל רמת התנודתיות.</p>
    </div></div>
    <?php if (!empty($constituents)): ?>
    <div class="panel section"><div class="panel-head"><h2><i class="bi bi-list-ol"></i> מניות עיקריות במדד</h2></div>
      <div class="panel-body p-0"><div class="table-responsive"><table class="table asset-table mb-0">
        <thead><tr><th>#</th><th>שם</th><th>סימול</th><th class="text-start">משקל</th></tr></thead><tbody>
        <?php foreach ($constituents as $i => $c): ?>
        <tr><td><?= $i+1 ?></td><td class="asset-name"><?php if ($c['stock_id'] && $c['ticker']): ?><a href="<?= url('stock/' . e($c['ticker'])) ?>"><?= e($c['name']) ?></a><?php else: ?><?= e($c['name']) ?><?php endif; ?></td><td class="text-soft"><?= e($c['ticker']) ?></td><td class="text-start"><strong><?= num($c['weight']) ?>%</strong></td></tr>
        <?php endforeach; ?>
      </tbody></table></div></div></div>
    <?php endif; ?>
    <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
    <?= View::partial('partials/disclaimer') ?>
  </div><div class="col-lg-4"></div></div>
</div>
