<?php use App\Core\View; ?>
<?= View::partial('partials/breadcrumbs', ['seo' => $seo]) ?>
<div class="container py-4"><div class="panel"><div class="panel-body prose">
  <h1>צור קשר</h1>
  <p>נשמח לשמוע מכם. לפניות, הצעות לשיפור או דיווח על אי-דיוקים בנתונים, ניתן לפנות אלינו:</p>
  <ul>
    <li>דוא"ל: <a href="mailto:info@xbt.co.il">info@xbt.co.il</a></li>
    <li>אתר: <a href="<?= url('/') ?>">xbt.co.il</a></li>
  </ul>
  <?= View::partial('partials/disclaimer') ?>
</div></div></div>
