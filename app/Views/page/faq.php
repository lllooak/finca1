<?php use App\Core\View; /** @var array $faqs */ ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4">
  <h1 class="mb-3"><i class="bi bi-patch-question"></i> שאלות נפוצות</h1>
  <div class="panel"><div class="panel-body">
    <?= View::partial('partials/faq', ['faqs' => $faqs]) ?>
  </div></div>
  <?= View::partial('partials/disclaimer') ?>
</div>
