<?php use App\Core\View; /** @var array $t,$faqs,$related; @var string $term */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4"><div class="row">
  <div class="col-lg-8">
    <article class="panel"><div class="panel-body prose">
      <h1><?= e($term) ?> <?php if (($t['term_he'] ?? '') && ($t['term'] ?? '')): ?><span class="text-soft" style="font-size:1.1rem">(<?= e($t['term']) ?>)</span><?php endif; ?></h1>
      <?php if (!empty($t['category'])): ?><span class="tag-pill"><?= e($t['category']) ?></span><?php endif; ?>
      <h2>הגדרה</h2>
      <p><?= nl2br(e($t['definition'])) ?></p>
      <?php if (!empty($t['explanation'])): ?><h2>הסבר מורחב</h2><div><?= nl2br(e($t['explanation'])) ?></div><?php endif; ?>
      <?php if (!empty($t['formula'])): ?><h2>נוסחה</h2><pre style="direction:ltr;text-align:left;background:var(--surface-alt);padding:12px;border-radius:8px"><?= e($t['formula']) ?></pre><?php endif; ?>
      <?php if (!empty($t['example'])): ?><h2>דוגמה</h2><p><?= nl2br(e($t['example'])) ?></p><?php endif; ?>
    </div></article>
    <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
    <?= View::partial('partials/disclaimer') ?>
  </div>
  <div class="col-lg-4"><?php if (!empty($related)): ?>
    <div class="panel section"><div class="panel-head"><h3>מונחים קשורים</h3></div><div class="panel-body">
      <?php foreach ($related as $r): ?><a class="d-block py-2 border-bottom" href="<?= url('glossary/' . e($r['slug'])) ?>"><?= e($r['term_he'] ?: $r['term']) ?></a><?php endforeach; ?>
    </div></div>
  <?php endif; ?></div>
</div></div>
