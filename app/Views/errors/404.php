<div class="container text-center py-5">
  <div style="font-size:5rem;font-weight:800;color:var(--primary)">404</div>
  <h1>הדף לא נמצא</h1>
  <p class="text-soft">ייתכן שהקישור שגוי או שהדף הוסר. נסה לחפש או חזור לעמוד הבית.</p>
  <form class="hero-search mx-auto" action="<?= url('search') ?>" method="get" style="max-width:520px">
    <i class="bi bi-search"></i>
    <input type="text" name="q" class="form-control" placeholder="חפש מניה, ETF, מדד או מונח...">
  </form>
  <div class="mt-4"><a class="btn btn-primary" href="<?= url('/') ?>">חזרה לעמוד הבית</a></div>
</div>
