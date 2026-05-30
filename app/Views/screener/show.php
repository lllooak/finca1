<?php use App\Core\View; /** @var string $type,$baseUrl; @var array $preset,$rows,$pagination,$sectors,$exchanges,$countries */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1"><i class="bi bi-funnel"></i> <?= e($preset['title']) ?></h1>
  <p class="text-soft"><?= e($preset['desc']) ?></p>

  <form class="screener-filters" method="get">
    <div class="row g-3">
      <div class="col-md-3"><label>סקטור</label><select name="sector" class="form-select form-select-sm"><option value="">הכל</option><?php foreach ($sectors as $s): ?><option value="<?= $s['id'] ?>" <?= request_param('sector')==$s['id']?'selected':'' ?>><?= e($s['name_he'] ?: $s['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label>בורסה</label><select name="exchange" class="form-select form-select-sm"><option value="">הכל</option><?php foreach ($exchanges as $x): ?><option value="<?= $x['id'] ?>" <?= request_param('exchange')==$x['id']?'selected':'' ?>><?= e($x['name_he'] ?: $x['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label>מדינה</label><select name="country" class="form-select form-select-sm"><option value="">הכל</option><?php foreach ($countries as $c): ?><option value="<?= $c['id'] ?>" <?= request_param('country')==$c['id']?'selected':'' ?>><?= e($c['name_he'] ?: $c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label>מיון לפי</label><select name="sort" class="form-select form-select-sm"><option value="market_cap" <?= request_param('sort')==='market_cap'?'selected':'' ?>>שווי שוק</option><option value="day_change_pct" <?= request_param('sort')==='day_change_pct'?'selected':'' ?>>שינוי יומי</option><option value="dividend_yield" <?= request_param('sort')==='dividend_yield'?'selected':'' ?>>תשואת דיבידנד</option><option value="pe_ratio" <?= request_param('sort')==='pe_ratio'?'selected':'' ?>>מכפיל P/E</option><option value="revenue_growth" <?= request_param('sort')==='revenue_growth'?'selected':'' ?>>צמיחת הכנסות</option></select></div>
      <div class="col-md-3"><label>שווי שוק מינ' (מיליארד $)</label><input type="number" step="0.1" name="mcap_min" value="<?= e(request_param('mcap_min','')) ?>" class="form-control form-control-sm"></div>
      <div class="col-md-3"><label>מכפיל P/E מקס'</label><input type="number" step="0.1" name="pe_max" value="<?= e(request_param('pe_max','')) ?>" class="form-control form-control-sm"></div>
      <div class="col-md-3"><label>תשואת דיב' מינ' (%)</label><input type="number" step="0.1" name="div_min" value="<?= e(request_param('div_min','')) ?>" class="form-control form-control-sm"></div>
      <div class="col-md-3 d-flex align-items-end gap-2"><button class="btn btn-primary btn-sm flex-fill">סנן</button><a href="<?= $baseUrl ?>" class="btn btn-outline-secondary btn-sm">איפוס</a></div>
    </div>
  </form>

  <p class="text-soft">נמצאו <?= int_num($pagination['total']) ?> תוצאות</p>
  <div class="panel"><div class="panel-body p-0"><?= View::partial('partials/stocks_table', ['rows' => $rows, 'showDividend' => true]) ?></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
