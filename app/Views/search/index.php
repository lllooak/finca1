<?php use App\Core\View; /** @var string $q; @var array $results */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3">תוצאות חיפוש</h1>
  <form class="hero-search mb-4" action="<?= url('search') ?>" method="get" style="max-width:600px;margin-top:0">
    <i class="bi bi-search"></i>
    <input type="text" name="q" class="form-control" value="<?= e($q) ?>" placeholder="חפש...">
  </form>

  <?php if ($q === ''): ?>
    <p class="text-soft">הקלד מונח לחיפוש מניות, ETF, מדדים, מונחים ומדריכים.</p>
  <?php elseif (empty($results)): ?>
    <div class="panel"><div class="panel-body">לא נמצאו תוצאות עבור "<strong><?= e($q) ?></strong>".</div></div>
  <?php else: ?>
    <p class="text-soft mb-3">נמצאו <?= count($results) ?> תוצאות עבור "<strong><?= e($q) ?></strong>"</p>
    <div class="panel"><div class="panel-body p-0">
      <div class="list-group list-group-flush">
        <?php foreach ($results as $r): ?>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e($r['url']) ?>">
          <span><strong><?= e($r['label']) ?></strong> <?php if ($r['ticker']): ?><span class="text-soft">· <?= e($r['ticker']) ?></span><?php endif; ?></span>
          <span class="tag-pill"><?= e($r['type']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div></div>
  <?php endif; ?>
</div>
