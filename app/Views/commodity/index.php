<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1">סחורות</h1>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> סחורות</p>
  <div class="panel"><div class="panel-body p-0"><div class="table-responsive">
    <table class="table table-hover asset-table mb-0">
      <thead><tr><th>שם</th><th class="text-start">קטגוריה</th><th class="text-start">מחיר</th><th class="text-start d-none d-md-table-cell">יחידה</th><th class="text-start">שינוי</th><th class="text-start d-none d-lg-table-cell">YTD</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $c): ?>
        <tr>
          <td class="asset-name"><a href="<?= url('commodity/' . e($c['slug'])) ?>"><?= e($c['name_he'] ?: $c['name']) ?></a></td>
          <td class="text-start"><span class="tag-pill"><?= e($c['category']) ?></span></td>
          <td class="text-start"><?= money($c['price'], $c['currency_code']) ?></td>
          <td class="text-start d-none d-md-table-cell text-soft"><?= e($c['unit']) ?></td>
          <td class="text-start <?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></td>
          <td class="text-start d-none d-lg-table-cell <?= change_class($c['ytd_return']) ?>"><?= pct($c['ytd_return']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
