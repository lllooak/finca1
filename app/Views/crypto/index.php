<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1">קריפטו</h1>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> מטבעות דיגיטליים</p>
  <div class="panel"><div class="panel-body p-0"><div class="table-responsive">
    <table class="table table-hover asset-table mb-0">
      <thead><tr><th>#</th><th>סמל</th><th>שם</th><th class="text-start">מחיר</th><th class="text-start">שינוי 24ש'</th><th class="text-start d-none d-md-table-cell">שווי שוק</th><th class="text-start d-none d-lg-table-cell">נפח 24ש'</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $c): ?>
        <tr>
          <td class="text-soft"><?= int_num($c['rank']) ?></td>
          <td><a class="ticker-link" href="<?= url('crypto/' . e($c['slug'])) ?>"><?= e($c['symbol']) ?></a></td>
          <td class="asset-name"><a href="<?= url('crypto/' . e($c['slug'])) ?>"><?= e($c['name_he'] ?: $c['name']) ?></a></td>
          <td class="text-start"><?= money($c['price'], 'USD', $c['price'] < 1 ? 4 : 2) ?></td>
          <td class="text-start <?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></td>
          <td class="text-start d-none d-md-table-cell"><?= big_number($c['market_cap'], 'USD') ?></td>
          <td class="text-start d-none d-lg-table-cell"><?= big_number($c['volume_24h'], 'USD') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
