<?php use App\Core\View; /** @var array $sectors,$themes,$guides */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-4">מפת אתר</h1>
  <div class="row">
    <div class="col-md-4 mb-4">
      <h2 class="section-title">נכסים</h2>
      <ul class="list-unstyled">
        <li><a href="<?= url('stocks') ?>">מניות</a></li>
        <li><a href="<?= url('etfs') ?>">קרנות סל (ETF)</a></li>
        <li><a href="<?= url('bonds') ?>">אג"ח</a></li>
        <li><a href="<?= url('indices') ?>">מדדים</a></li>
        <li><a href="<?= url('reits') ?>">REIT</a></li>
        <li><a href="<?= url('crypto') ?>">קריפטו</a></li>
        <li><a href="<?= url('commodities') ?>">סחורות</a></li>
        <li><a href="<?= url('currencies') ?>">מטבעות</a></li>
      </ul>
      <h2 class="section-title mt-3">כלים ותוכן</h2>
      <ul class="list-unstyled">
        <li><a href="<?= url('screener') ?>">סקרינרים</a></li>
        <li><a href="<?= url('compare') ?>">השוואות</a></li>
        <li><a href="<?= url('guides') ?>">מדריכים</a></li>
        <li><a href="<?= url('glossary') ?>">מילון מונחים</a></li>
        <li><a href="<?= url('news') ?>">חדשות</a></li>
        <li><a href="<?= url('markets') ?>">סקירת שוק</a></li>
      </ul>
      <h2 class="section-title mt-3">אודות ומשפטי</h2>
      <ul class="list-unstyled">
        <li><a href="<?= url('about') ?>">אודות האתר</a></li>
        <li><a href="<?= url('faq') ?>">שאלות נפוצות</a></li>
        <li><a href="<?= url('contact') ?>">צור קשר</a></li>
        <li><a href="<?= url('disclaimer') ?>">גילוי נאות</a></li>
        <li><a href="<?= url('terms') ?>">תנאי שימוש</a></li>
        <li><a href="<?= url('privacy') ?>">מדיניות פרטיות</a></li>
      </ul>
    </div>
    <div class="col-md-4 mb-4">
      <h2 class="section-title">סקטורים</h2>
      <ul class="list-unstyled">
        <?php foreach ($sectors as $s): ?><li><a href="<?= url('sector/' . e($s['slug'])) ?>"><?= e($s['name_he'] ?: $s['name']) ?></a></li><?php endforeach; ?>
      </ul>
    </div>
    <div class="col-md-4 mb-4">
      <h2 class="section-title">Themes</h2>
      <ul class="list-unstyled">
        <?php foreach ($themes as $t): ?><li><a href="<?= url('theme/' . e($t['slug'])) ?>"><?= e($t['name_he'] ?: $t['name']) ?></a></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
  <h2 class="section-title">מדריכים פופולריים</h2>
  <div class="row"><?php foreach ($guides as $g): ?><div class="col-md-6"><a href="<?= url('guide/' . e($g['slug'])) ?>"><?= e($g['title']) ?></a></div><?php endforeach; ?></div>
  <p class="mt-4 text-soft">מפת אתר XML: <a href="<?= url('sitemap.xml') ?>">sitemap.xml</a></p>
</div>
