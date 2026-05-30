<?php use App\Core\View; /** @var array $g,$faqs,$related */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <div class="row">
    <div class="col-lg-8">
      <article class="panel"><div class="panel-body prose">
        <h1><?= e($g['title']) ?></h1>
        <div class="text-soft mb-3"><i class="bi bi-clock"></i> <?= int_num($g['reading_time']) ?> דקות קריאה · רמה: <?= e($g['level']) ?> · <?= he_date($g['published_at'] ?: $g['created_at']) ?></div>
        <?php if (!empty($g['summary'])): ?><div class="lead text-soft"><?= e($g['summary']) ?></div><div class="divider"></div><?php endif; ?>
        <?= $g['content'] ?: '<p>' . e($g['summary']) . '</p>' ?>
      </div></article>
      <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
      <?= View::partial('partials/disclaimer') ?>
    </div>
    <div class="col-lg-4">
      <?php if (!empty($related)): ?>
      <div class="panel section"><div class="panel-head"><h3>מדריכים קשורים</h3></div><div class="panel-body">
        <?php foreach ($related as $r): ?>
        <a class="d-block py-2 border-bottom" href="<?= url('guide/' . e($r['slug'])) ?>"><strong><?= e($r['title']) ?></strong><br><span class="text-soft small"><?= e(excerpt($r['summary'], 70)) ?></span></a>
        <?php endforeach; ?>
      </div></div>
      <?php endif; ?>
    </div>
  </div>
</div>
