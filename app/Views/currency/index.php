<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1">מטבעות ושערי חליפין</h1>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> מטבעות</p>
  <div class="panel"><div class="panel-body p-0"><div class="table-responsive">
    <table class="table table-hover asset-table mb-0">
      <thead><tr><th>זוג</th><th>שם</th><th class="text-start">שער</th><th class="text-start">שינוי</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $c): ?>
        <tr>
          <td><a class="ticker-link" href="<?= url('currency/' . e($c['slug'])) ?>"><?= e($c['pair'] ?: $c['code']) ?></a></td>
          <td class="asset-name"><a href="<?= url('currency/' . e($c['slug'])) ?>"><?= e($c['name_he'] ?: $c['name']) ?></a></td>
          <td class="text-start"><?= num($c['rate'], 4) ?></td>
          <td class="text-start <?= change_class($c['day_change_pct']) ?>"><?= pct($c['day_change_pct']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
