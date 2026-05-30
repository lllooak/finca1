<?php /** @var \App\Core\Seo $seo */ ?>
<?php if (!empty($seo->breadcrumbs)): ?>
<nav aria-label="breadcrumb" class="breadcrumbs">
  <div class="container">
    <ol class="breadcrumb mb-0">
      <?php $last = count($seo->breadcrumbs) - 1; ?>
      <?php foreach ($seo->breadcrumbs as $i => $bc): ?>
        <?php if ($i === $last): ?>
          <li class="breadcrumb-item active" aria-current="page"><?= e($bc['name']) ?></li>
        <?php else: ?>
          <li class="breadcrumb-item"><a href="<?= e($bc['url']) ?>"><?= e($bc['name']) ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>
<?php endif; ?>
