<?php use App\Core\View; /** @var array $rows,$cats,$pagination; @var string $baseUrl,$title */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi bi-newspaper"></i> <?= e($title) ?></h1>
  <div class="mb-4">
    <?php foreach ($cats as $c): ?><a class="tag-pill" style="margin:3px;display:inline-block" href="<?= url('news/category/' . e($c['slug'])) ?>"><?= e($c['name_he'] ?: $c['name']) ?></a><?php endforeach; ?>
  </div>
  <div class="row gy-3">
    <?php foreach ($rows as $n): ?>
    <div class="col-md-6 col-lg-4">
      <article class="news-card h-100"><div class="nc-body">
        <div class="nc-cat"><?= e($n['cat_he'] ?: $n['cat'] ?: 'חדשות') ?></div>
        <h3><a href="<?= url('news/' . e($n['slug'])) ?>"><?= e($n['title']) ?></a></h3>
        <p class="text-soft small"><?= e(excerpt($n['summary'], 130)) ?></p>
        <div class="nc-meta"><i class="bi bi-calendar3"></i> <?= he_date($n['published_at']) ?></div>
      </div></article>
    </div>
    <?php endforeach; ?>
  </div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
</div>
