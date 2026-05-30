<?php use App\Core\View; /** @var array $rows,$pagination,$letters; @var string $baseUrl; @var ?string $active */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi bi-journal-text"></i> מילון מונחים פיננסי</h1>
  <div class="mb-4">
    <a class="tag-pill <?= !$active ? 'rating-badge' : '' ?>" style="margin:2px;display:inline-block" href="<?= url('glossary') ?>">הכל</a>
    <?php foreach ($letters as $l): ?><a class="tag-pill <?= $active === $l ? 'rating-badge' : '' ?>" style="margin:2px;display:inline-block" href="<?= url('glossary?letter=' . $l) ?>"><?= $l ?></a><?php endforeach; ?>
  </div>
  <div class="row gy-3">
    <?php foreach ($rows as $t): ?>
    <div class="col-md-6 col-lg-4">
      <a class="chip-card h-100 d-block" href="<?= url('glossary/' . e($t['slug'])) ?>">
        <div class="cc-title"><?= e($t['term_he'] ?: $t['term']) ?> <?php if (($t['term_he'] ?? '') && ($t['term'] ?? '')): ?><span class="text-soft small">(<?= e($t['term']) ?>)</span><?php endif; ?></div>
        <div class="cc-meta"><?= e(excerpt($t['definition'], 90)) ?></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
</div>
