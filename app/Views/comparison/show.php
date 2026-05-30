<?php
use App\Core\View;
/** @var array $c,$faqs; @var ?array $a,$b */
$la = $c['entity_a_label'] ?: 'נכס א';
$lb = $c['entity_b_label'] ?: 'נכס ב';
$metric = function ($label, $va, $vb, $fmt = 'num') {
    $f = function ($v) use ($fmt) {
        return match ($fmt) {
            'money' => money($v, 'USD'),
            'big' => big_number($v, 'USD'),
            'pct' => $v !== null ? num($v) . '%' : '—',
            default => num($v),
        };
    };
    echo '<tr><td class="text-soft">' . e($label) . '</td><td class="text-start"><strong>' . $f($va) . '</strong></td><td class="text-start"><strong>' . $f($vb) . '</strong></td></tr>';
};
?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<section class="asset-hero"><div class="container"><h1><?= e($c['title']) ?></h1></div></section>
<div class="container py-4">
  <?php if ($a && $b): ?>
  <div class="panel section">
    <div class="panel-head"><h2><i class="bi bi-table"></i> טבלת השוואה</h2></div>
    <div class="panel-body p-0"><div class="table-responsive"><table class="table asset-table mb-0">
      <thead><tr><th>מדד</th><th class="text-start"><a href="<?= url('stock/' . e($a['ticker'])) ?>"><?= e($la) ?></a></th><th class="text-start"><a href="<?= url('stock/' . e($b['ticker'])) ?>"><?= e($lb) ?></a></th></tr></thead>
      <tbody>
        <?php
        $metric('מחיר', $a['price'], $b['price'], 'money');
        $metric('שווי שוק', $a['market_cap'], $b['market_cap'], 'big');
        $metric('מכפיל P/E', $a['pe_ratio'], $b['pe_ratio']);
        $metric('Forward P/E', $a['forward_pe'], $b['forward_pe']);
        $metric('מכפיל P/S', $a['ps_ratio'], $b['ps_ratio']);
        $metric('EPS', $a['eps'], $b['eps'], 'money');
        $metric('הכנסות', $a['revenue'], $b['revenue'], 'big');
        $metric('צמיחת הכנסות', $a['revenue_growth'], $b['revenue_growth'], 'pct');
        $metric('רווח נקי', $a['net_income'], $b['net_income'], 'big');
        $metric('שולי רווח', $a['profit_margin'], $b['profit_margin'], 'pct');
        $metric('ROE', $a['roe'], $b['roe'], 'pct');
        $metric('תשואת דיבידנד', $a['dividend_yield'], $b['dividend_yield'], 'pct');
        $metric('Beta', $a['beta'], $b['beta']);
        $metric('תשואת שנה', $a['return_1y'], $b['return_1y'], 'pct');
        ?>
      </tbody>
    </table></div></div>
  </div>
  <?php endif; ?>

  <div class="panel section"><div class="panel-body prose">
    <?php if (!empty($c['content'])): ?>
      <?= $c['content'] ?>
    <?php else: ?>
      <h2>סקירת ההשוואה</h2>
      <p><?= e($c['summary']) ?></p>
      <?php if ($a && $b): ?>
      <h2>השוואת תמחור</h2>
      <p>מבחינת תמחור, מכפיל הרווח של <?= e($la) ?> עומד על <?= num($a['pe_ratio']) ?> לעומת <?= num($b['pe_ratio']) ?> של <?= e($lb) ?>. ככל שהמכפיל נמוך יותר, כך המניה נחשבת זולה יותר ביחס לרווחיה, אם כי יש לקחת בחשבון את שיעורי הצמיחה הצפויים.</p>
      <h2>השוואת צמיחה</h2>
      <p>צמיחת ההכנסות של <?= e($la) ?> עומדת על <?= pct($a['revenue_growth']) ?> ושל <?= e($lb) ?> על <?= pct($b['revenue_growth']) ?>. קצב הצמיחה הוא גורם מפתח בהערכת שווי, במיוחד עבור חברות צמיחה.</p>
      <h2>השוואת רווחיות</h2>
      <p>שולי הרווח הנקי של <?= e($la) ?> הם <?= pct($a['profit_margin']) ?> לעומת <?= pct($b['profit_margin']) ?> של <?= e($lb) ?>, ותשואה על ההון (ROE) של <?= pct($a['roe']) ?> מול <?= pct($b['roe']) ?>. רווחיות גבוהה ועקבית מעידה על איכות עסקית.</p>
      <h2>השוואת ביצועים</h2>
      <p>בשנה האחרונה רשמה <?= e($la) ?> תשואה של <?= pct($a['return_1y']) ?> לעומת <?= pct($b['return_1y']) ?> של <?= e($lb) ?>.</p>
      <h2>סיכום</h2>
      <p>הבחירה בין שתי המניות תלויה באסטרטגיית ההשקעה ובפרופיל הסיכון. משקיע ערך עשוי להעדיף את המניה עם המכפיל הנמוך, בעוד שמשקיע צמיחה יעדיף את זו עם קצב הצמיחה הגבוה. מומלץ לשקלל את כלל הנתונים ולא להסתמך על מדד בודד.</p>
      <?php endif; ?>
    <?php endif; ?>
  </div></div>

  <?php if (!empty($c['pros_a']) || !empty($c['cons_a'])): ?>
  <div class="row">
    <div class="col-md-6"><div class="panel section"><div class="panel-head"><h3><?= e($la) ?></h3></div><div class="panel-body">
      <?php if ($c['pros_a']): ?><h4 class="text-up">יתרונות</h4><div class="prose"><?= nl2br(e($c['pros_a'])) ?></div><?php endif; ?>
      <?php if ($c['cons_a']): ?><h4 class="text-down">חסרונות</h4><div class="prose"><?= nl2br(e($c['cons_a'])) ?></div><?php endif; ?>
    </div></div></div>
    <div class="col-md-6"><div class="panel section"><div class="panel-head"><h3><?= e($lb) ?></h3></div><div class="panel-body">
      <?php if ($c['pros_b']): ?><h4 class="text-up">יתרונות</h4><div class="prose"><?= nl2br(e($c['pros_b'])) ?></div><?php endif; ?>
      <?php if ($c['cons_b']): ?><h4 class="text-down">חסרונות</h4><div class="prose"><?= nl2br(e($c['cons_b'])) ?></div><?php endif; ?>
    </div></div></div>
  </div>
  <?php endif; ?>

  <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
