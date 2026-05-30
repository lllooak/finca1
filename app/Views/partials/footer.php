<footer class="site-footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4">
        <a class="footer-brand" href="<?= url('/') ?>"><span class="brand-mark">xbt</span><span class="brand-dot">.co.il</span></a>
        <p class="footer-about"><?= e(SITE_TAGLINE) ?></p>
        <p class="footer-disclaimer-short"><?= e(DISCLAIMER_TEXT) ?></p>
      </div>
      <div class="col-6 col-lg-2">
        <h6>נכסים</h6>
        <ul>
          <li><a href="<?= url('stocks') ?>">מניות</a></li>
          <li><a href="<?= url('etfs') ?>">ETF</a></li>
          <li><a href="<?= url('bonds') ?>">אג"ח</a></li>
          <li><a href="<?= url('indices') ?>">מדדים</a></li>
          <li><a href="<?= url('reits') ?>">REIT</a></li>
          <li><a href="<?= url('crypto') ?>">קריפטו</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>כלים</h6>
        <ul>
          <li><a href="<?= url('screener') ?>">סקרינרים</a></li>
          <li><a href="<?= url('compare') ?>">השוואות</a></li>
          <li><a href="<?= url('markets') ?>">סקירת שוק</a></li>
          <li><a href="<?= url('search') ?>">חיפוש</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>תוכן</h6>
        <ul>
          <li><a href="<?= url('guides') ?>">מדריכים</a></li>
          <li><a href="<?= url('glossary') ?>">מילון מונחים</a></li>
          <li><a href="<?= url('news') ?>">חדשות</a></li>
          <li><a href="<?= url('faq') ?>">שאלות נפוצות</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6>אודות</h6>
        <ul>
          <li><a href="<?= url('about') ?>">אודות האתר</a></li>
          <li><a href="<?= url('disclaimer') ?>">גילוי נאות</a></li>
          <li><a href="<?= url('contact') ?>">צור קשר</a></li>
          <li><a href="<?= url('sitemap') ?>">מפת אתר</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> xbt.co.il — כל הזכויות שמורות. המידע באתר אינו מהווה ייעוץ השקעות.</p>
    </div>
  </div>
</footer>
