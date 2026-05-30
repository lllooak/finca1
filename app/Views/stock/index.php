<?php use App\Core\View; /** @var string $title; @var array $rows,$pagination; @var string $baseUrl,$sort */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <h1 class="mb-0"><?= e($title) ?></h1>
    <a class="btn btn-outline-primary btn-sm" href="<?= url('screener') ?>"><i class="bi bi-funnel"></i> סקרינר מתקדם</a>
  </div>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> מניות · מציג עמוד <?= $pagination['current'] ?> מתוך <?= $pagination['pages'] ?></p>

  <form class="d-flex gap-2 mb-3" method="get">
    <select name="sort" class="form-select form-select-sm" style="max-width:220px" onchange="this.form.submit()">
      <option value="market_cap" <?= $sort==='market_cap'?'selected':'' ?>>מיון לפי שווי שוק</option>
      <option value="day_change_pct" <?= $sort==='day_change_pct'?'selected':'' ?>>מיון לפי שינוי יומי</option>
      <option value="volume" <?= $sort==='volume'?'selected':'' ?>>מיון לפי נפח מסחר</option>
      <option value="dividend_yield" <?= $sort==='dividend_yield'?'selected':'' ?>>מיון לפי תשואת דיבידנד</option>
      <option value="pe_ratio" <?= $sort==='pe_ratio'?'selected':'' ?>>מיון לפי מכפיל P/E</option>
      <option value="price" <?= $sort==='price'?'selected':'' ?>>מיון לפי מחיר</option>
    </select>
  </form>

  <div class="panel"><div class="panel-body p-0">
    <?= View::partial('partials/stocks_table', ['rows' => $rows, 'showDividend' => true]) ?>
  </div></div>

  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
