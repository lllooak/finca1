<?php use App\Core\View; /** @var string $title,$route,$icon; @var array $rows */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi <?= e($icon) ?>"></i> <?= e($title) ?></h1>
  <div class="chip-grid">
    <?php foreach ($rows as $r): ?>
    <a class="chip-card" href="<?= url($route . e($r['slug'])) ?>">
      <div class="cc-title">
        <?php if (!empty($r['icon'])): ?><i class="bi <?= e($r['icon']) ?>"></i> <?php endif; ?>
        <?php if (!empty($r['icon_text'])): ?><?= e($r['icon_text']) ?> <?php endif; ?>
        <?= e($r['name_he'] ?: $r['name']) ?>
      </div>
      <div class="cc-meta"><?= e(excerpt($r['description'] ?? '', 80) ?: 'צפה במניות ובניתוח') ?></div>
    </a>
    <?php endforeach; ?>
  </div>
  <?= View::partial('partials/disclaimer') ?>
</div>
