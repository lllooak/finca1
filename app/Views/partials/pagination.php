<?php
/** @var array $pagination */
/** @var string $baseUrl */
$p = $pagination;
$base = $baseUrl ?? '';
$qs = $_GET;
$mk = function (int $page) use ($base, $qs) {
    $qs['page'] = $page;
    return $base . '?' . http_build_query($qs);
};
if (($p['pages'] ?? 1) <= 1) return;
$cur = $p['current'];
$start = max(1, $cur - 2);
$end = min($p['pages'], $cur + 2);
?>
<nav aria-label="עימוד" class="d-flex justify-content-center my-4">
  <ul class="pagination">
    <li class="page-item <?= $p['hasPrev'] ? '' : 'disabled' ?>">
      <a class="page-link" href="<?= $p['hasPrev'] ? e($mk($cur - 1)) : '#' ?>" aria-label="הקודם">&rsaquo;</a>
    </li>
    <?php if ($start > 1): ?>
      <li class="page-item"><a class="page-link" href="<?= e($mk(1)) ?>">1</a></li>
      <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
    <?php endif; ?>
    <?php for ($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?= $i === $cur ? 'active' : '' ?>">
        <a class="page-link" href="<?= e($mk($i)) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <?php if ($end < $p['pages']): ?>
      <?php if ($end < $p['pages'] - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
      <li class="page-item"><a class="page-link" href="<?= e($mk($p['pages'])) ?>"><?= $p['pages'] ?></a></li>
    <?php endif; ?>
    <li class="page-item <?= $p['hasNext'] ? '' : 'disabled' ?>">
      <a class="page-link" href="<?= $p['hasNext'] ? e($mk($cur + 1)) : '#' ?>" aria-label="הבא">&lsaquo;</a>
    </li>
  </ul>
</nav>
