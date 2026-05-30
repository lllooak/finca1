<?php use App\Core\View; /** @var array $rows,$cats,$pagination; @var string $baseUrl,$title */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi bi-book"></i> <?= e($title) ?></h1>
  <div class="mb-4">
    <?php foreach ($cats as $c): ?><a class="tag-pill" style="margin:3px;display:inline-block" href="<?= url('guides/category/' . e($c['slug'])) ?>"><?= e($c['name_he'] ?: $c['name']) ?></a><?php endforeach; ?>
  </div>
  <div class="row gy-3">
    <?php foreach ($rows as $g): ?>
    <div class="col-md-6 col-lg-4">
      <article class="news-card h-100"><div class="nc-body">
        <?php if (!empty($g['cat_he']) || !empty($g['cat'])): ?><div class="nc-cat"><?= e($g['cat_he'] ?? $g['cat']) ?></div><?php endif; ?>
        <h3><a href="<?= url('guide/' . e($g['slug'])) ?>"><?= e($g['title']) ?></a></h3>
        <p class="text-soft small"><?= e(excerpt($g['summary'], 120)) ?></p>
        <div class="nc-meta"><i class="bi bi-clock"></i> <?= int_num($g['reading_time']) ?> דק' · <?= e($g['level']) ?></div>
      </div></article>
    </div>
    <?php endforeach; ?>
  </div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
</div>
