<?php use App\Core\View; /** @var array $rows,$pagination; @var string $baseUrl */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-1">איגרות חוב (אג"ח)</h1>
  <p class="text-soft">סה"כ <?= int_num($pagination['total']) ?> אג"ח</p>
  <div class="panel"><div class="panel-body p-0"><div class="table-responsive">
    <table class="table table-hover asset-table mb-0">
      <thead><tr><th>שם</th><th class="text-start">סוג</th><th class="text-start">תשואה</th><th class="text-start d-none d-md-table-cell">קופון</th><th class="text-start d-none d-md-table-cell">מח"מ</th><th class="text-start">דירוג</th><th class="text-start d-none d-lg-table-cell">סיכון</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $b): ?>
        <tr>
          <td class="asset-name"><a href="<?= url('bond/' . e($b['slug'])) ?>"><?= e($b['name_he'] ?: $b['name']) ?></a></td>
          <td class="text-start"><span class="tag-pill"><?= e($b['bond_type']) ?></span></td>
          <td class="text-start text-up"><?= num($b['yield']) ?>%</td>
          <td class="text-start d-none d-md-table-cell"><?= num($b['coupon']) ?>%</td>
          <td class="text-start d-none d-md-table-cell"><?= num($b['maturity_years'], 1) ?></td>
          <td class="text-start"><span class="rating-badge"><?= e($b['credit_rating']) ?></span></td>
          <td class="text-start d-none d-lg-table-cell text-soft"><?= e($b['risk_level']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div></div>
  <?= View::partial('partials/pagination', ['pagination' => $pagination, 'baseUrl' => $baseUrl]) ?>
  <?= View::partial('partials/disclaimer') ?>
</div>
