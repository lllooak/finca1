<?php /** @var array $faqs */ ?>
<?php if (!empty($faqs)): ?>
<section class="faq-section" id="faq">
  <h2 class="section-title"><i class="bi bi-patch-question"></i> שאלות נפוצות</h2>
  <div class="accordion" id="faqAccordion">
    <?php foreach ($faqs as $i => $faq): ?>
    <div class="accordion-item">
      <h3 class="accordion-header">
        <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
          <?= e($faq['question']) ?>
        </button>
      </h3>
      <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
        <div class="accordion-body"><?= nl2br(e($faq['answer'])) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
