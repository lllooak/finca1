<header class="site-header">
  <div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="topbar-ticker" id="topTicker" aria-live="polite"></div>
      <div class="topbar-links d-none d-md-flex">
        <a href="<?= url('markets') ?>">סקירת שוק</a>
        <a href="<?= url('screener') ?>">סקרינרים</a>
        <a href="<?= url('news') ?>">חדשות</a>
      </div>
    </div>
  </div>

  <nav class="navbar navbar-expand-lg main-nav">
    <div class="container">
      <a class="navbar-brand" href="<?= url('/') ?>">
        <span class="brand-mark">xbt</span><span class="brand-dot">.co.il</span>
      </a>

      <form class="search-wrap d-none d-lg-flex" action="<?= url('search') ?>" method="get" role="search" autocomplete="off">
        <i class="bi bi-search"></i>
        <input type="text" name="q" id="globalSearch" class="form-control" placeholder="חפש מניה, ETF, מדד, סקטור או מונח..." aria-label="חיפוש">
        <div class="search-results" id="searchResults"></div>
      </form>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-label="תפריט">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainMenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">נכסים</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= url('stocks') ?>">מניות</a></li>
              <li><a class="dropdown-item" href="<?= url('etfs') ?>">קרנות סל (ETF)</a></li>
              <li><a class="dropdown-item" href="<?= url('bonds') ?>">אג"ח</a></li>
              <li><a class="dropdown-item" href="<?= url('indices') ?>">מדדים</a></li>
              <li><a class="dropdown-item" href="<?= url('reits') ?>">REIT</a></li>
              <li><a class="dropdown-item" href="<?= url('crypto') ?>">קריפטו</a></li>
              <li><a class="dropdown-item" href="<?= url('commodities') ?>">סחורות</a></li>
              <li><a class="dropdown-item" href="<?= url('currencies') ?>">מטבעות</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">קטגוריות</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= url('sectors') ?>">סקטורים</a></li>
              <li><a class="dropdown-item" href="<?= url('industries') ?>">תעשיות</a></li>
              <li><a class="dropdown-item" href="<?= url('themes') ?>">Themes</a></li>
              <li><a class="dropdown-item" href="<?= url('countries') ?>">מדינות</a></li>
              <li><a class="dropdown-item" href="<?= url('exchanges') ?>">בורסות</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= url('screener') ?>">סקרינרים</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('compare') ?>">השוואות</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('guides') ?>">מדריכים</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('glossary') ?>">מילון</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('news') ?>">חדשות</a></li>
        </ul>
      </div>
    </div>
  </nav>
</header>
