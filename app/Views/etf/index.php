<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1">קרנות סל (ETF)</h1>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> קרנות סל</p>
  <div class="panel"><div class="panel-body p-0"><div class="table-responsive">
    <table class="table table-hover asset-table mb-0">
      <thead><tr><th>סימול</th><th>שם</th><th class="text-start">מנפיק</th><th class="text-start">מחיר</th><th class="text-start">שינוי</th><th class="text-start d-none d-md-table-cell">נכסים</th><th class="text-start d-none d-lg-table-cell">דמי ניהול</th><th class="text-start d-none d-lg-table-cell">תשואת דיב'</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $e): ?>
        <tr>
          <td><a class="ticker-link" href="<?= url('etf/' . e($e['ticker'])) ?>"><?= e($e['ticker']) ?></a></td>
          <td class="asset-name"><a href="<?= url('etf/' . e($e['ticker'])) ?>"><?= e($e['name_he'] ?: $e['name']) ?></a></td>
          <td class="text-start text-soft"><?= e($e['issuer']) ?></td>
          <td class="text-start"><?= money($e['price'], $e['currency_code']) ?></td>
          <td class="text-start <?= change_class($e['day_change_pct']) ?>"><?= pct($e['day_change_pct']) ?></td>
          <td class="text-start d-none d-md-table-cell"><?= big_number($e['aum'], $e['currency_code']) ?></td>
          <td class="text-start d-none d-lg-table-cell"><?= $e['expense_ratio'] ? num($e['expense_ratio']).'%' : '—' ?></td>
          <td class="text-start d-none d-lg-table-cell"><?= $e['dividend_yield'] ? num($e['dividend_yield']).'%' : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
