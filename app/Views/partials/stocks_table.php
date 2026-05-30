<?php
/** @var array $rows */
/** @var bool $showDividend */
$showDividend = $showDividend ?? false;
?>
<div class="table-responsive">
  <table class="table table-hover asset-table align-middle mb-0">
    <thead>
      <tr>
        <th>סימול</th>
        <th>שם</th>
        <th class="text-start">מחיר</th>
        <th class="text-start">שינוי יומי</th>
        <th class="text-start d-none d-md-table-cell">שווי שוק</th>
        <th class="text-start d-none d-lg-table-cell">מכפיל P/E</th>
        <?php if ($showDividend): ?><th class="text-start d-none d-lg-table-cell">תשואת דיב'</th><?php endif; ?>
        <th class="text-start d-none d-md-table-cell">נפח</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><a class="ticker-link" href="<?= url('stock/' . e($r['ticker'])) ?>"><?= e($r['ticker']) ?></a></td>
        <td class="asset-name"><a href="<?= url('stock/' . e($r['ticker'])) ?>"><?= e($r['company_name_he'] ?: $r['company_name']) ?></a></td>
        <td class="text-start"><?= money($r['price'] ?? null, $r['currency_code'] ?? 'USD') ?></td>
        <td class="text-start <?= change_class($r['day_change_pct'] ?? 0) ?>"><?= pct($r['day_change_pct'] ?? 0) ?></td>
        <td class="text-start d-none d-md-table-cell"><?= big_number($r['market_cap'] ?? null, $r['currency_code'] ?? 'USD') ?></td>
        <td class="text-start d-none d-lg-table-cell"><?= num($r['pe_ratio'] ?? null) ?></td>
        <?php if ($showDividend): ?><td class="text-start d-none d-lg-table-cell"><?= $r['dividend_yield'] ? num($r['dividend_yield']) . '%' : '—' ?></td><?php endif; ?>
        <td class="text-start d-none d-md-table-cell"><?= int_num($r['volume'] ?? null) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
      <tr><td colspan="8" class="text-center text-muted py-4">לא נמצאו נתונים</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
