<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1">מדדי בורסה</h1>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> מדדים</p>
  <div class="panel"><div class="panel-body p-0"><div class="table-responsive">
    <table class="table table-hover asset-table mb-0">
      <thead><tr><th>סמל</th><th>שם</th><th class="text-start">ערך</th><th class="text-start">שינוי</th><th class="text-start d-none d-md-table-cell">YTD</th><th class="text-start d-none d-lg-table-cell">שנה</th><th class="text-start d-none d-lg-table-cell">מניות</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $i): ?>
        <tr>
          <td><a class="ticker-link" href="<?= url('index/' . e($i['slug'])) ?>"><?= e($i['symbol']) ?></a></td>
          <td class="asset-name"><a href="<?= url('index/' . e($i['slug'])) ?>"><?= e($i['name_he'] ?: $i['name']) ?></a></td>
          <td class="text-start"><?= num($i['value']) ?></td>
          <td class="text-start <?= change_class($i['day_change_pct']) ?>"><?= pct($i['day_change_pct']) ?></td>
          <td class="text-start d-none d-md-table-cell <?= change_class($i['ytd_return']) ?>"><?= pct($i['ytd_return']) ?></td>
          <td class="text-start d-none d-lg-table-cell <?= change_class($i['return_1y']) ?>"><?= pct($i['return_1y']) ?></td>
          <td class="text-start d-none d-lg-table-cell"><?= int_num($i['constituents_count']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
