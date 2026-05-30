<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1">קרנות נדל"ן (REIT)</h1>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> קרנות REIT</p>
  <div class="panel"><div class="panel-body p-0"><div class="table-responsive">
    <table class="table table-hover asset-table mb-0">
      <thead><tr><th>סימול</th><th>שם</th><th class="text-start">סוג נכס</th><th class="text-start">מחיר</th><th class="text-start">שינוי</th><th class="text-start d-none d-md-table-cell">תשואת דיב'</th><th class="text-start d-none d-lg-table-cell">תפוסה</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><a class="ticker-link" href="<?= url('reit/' . e($r['ticker'])) ?>"><?= e($r['ticker']) ?></a></td>
          <td class="asset-name"><a href="<?= url('reit/' . e($r['ticker'])) ?>"><?= e($r['name_he'] ?: $r['name']) ?></a></td>
          <td class="text-start"><span class="tag-pill"><?= e($r['property_type']) ?></span></td>
          <td class="text-start"><?= money($r['price'], $r['currency_code']) ?></td>
          <td class="text-start <?= change_class($r['day_change_pct']) ?>"><?= pct($r['day_change_pct']) ?></td>
          <td class="text-start d-none d-md-table-cell text-up"><?= num($r['dividend_yield']) ?>%</td>
          <td class="text-start d-none d-lg-table-cell"><?= $r['occupancy_rate'] ? num($r['occupancy_rate']).'%' : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
