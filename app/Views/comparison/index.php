<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi bi-bar-chart-steps"></i> השוואות פיננסיות</h1>
  <div class="panel section"><div class="panel-body">
    <p class="mb-2">השווה שתי מניות במהירות:</p>
    <form class="d-flex gap-2 flex-wrap" onsubmit="event.preventDefault(); var a=document.getElementById('cmpA').value.trim().toUpperCase(); var b=document.getElementById('cmpB').value.trim().toUpperCase(); if(a&&b) location.href='<?= url('vs') ?>/'+a+'/'+b;">
      <input id="cmpA" class="form-control" style="max-width:160px" placeholder="סימול א' (AAPL)">
      <span class="align-self-center">מול</span>
      <input id="cmpB" class="form-control" style="max-width:160px" placeholder="סימול ב' (MSFT)">
      <button class="btn btn-primary">השווה</button>
    </form>
  </div></div>
  <div class="row gy-3">
    <?php foreach ($rows as $c): ?>
    <div class="col-md-6 col-lg-4">
      <a class="chip-card h-100 d-block" href="<?= url('compare/' . e($c['slug'])) ?>">
        <div class="cc-title"><?= e($c['title']) ?></div>
        <div class="cc-meta"><?= e(excerpt($c['summary'], 100)) ?></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
</div>
