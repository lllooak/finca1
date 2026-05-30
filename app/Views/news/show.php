<?php use App\Core\View; /** @var array $n,$related */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4"><div class="row">
  <div class="col-lg-8">
    <article class="panel"><div class="panel-body prose">
      <div class="nc-cat"><?= e($n['cat_he'] ?: $n['cat'] ?: 'חדשות') ?></div>
      <h1><?= e($n['title']) ?></h1>
      <div class="text-soft mb-3"><i class="bi bi-calendar3"></i> <?= he_datetime($n['published_at']) ?> <?php if (!empty($n['source'])): ?>· מקור: <?= e($n['source']) ?><?php endif; ?></div>
      <?php if (!empty($n['summary'])): ?><div class="lead text-soft"><?= e($n['summary']) ?></div><div class="divider"></div><?php endif; ?>
      <?= $n['content'] ?: '<p>' . e($n['summary']) . '</p>' ?>
      <?php if (!empty($n['tags'])): ?><div class="mt-4"><?php foreach (explode(',', $n['tags']) as $tag): $tag = trim($tag); if ($tag === '') continue; ?><span class="tag-pill" style="margin:2px"><?= e($tag) ?></span><?php endforeach; ?></div><?php endif; ?>
    </div></article>
    <?= View::partial('partials/disclaimer') ?>
  </div>
  <div class="col-lg-4"><?php if (!empty($related)): ?>
    <div class="panel section"><div class="panel-head"><h3>כתבות נוספות</h3></div><div class="panel-body">
      <?php foreach ($related as $r): ?><a class="d-block py-2 border-bottom" href="<?= url('news/' . e($r['slug'])) ?>"><strong><?= e($r['title']) ?></strong><br><span class="text-soft small"><?= he_date($r['published_at']) ?></span></a><?php endforeach; ?>
    </div></div>
  <?php endif; ?></div>
</div></div>
