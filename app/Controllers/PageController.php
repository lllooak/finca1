<?php
declare(strict_types=1);

namespace App\Controllers;

class PageController extends BaseController
{
    public function about(array $params = []): void
    {
        $this->seo->set('אודות xbt.co.il', 'אודות xbt.co.il - מאגר המידע הפיננסי המעמיק בעברית על שוק ההון בארה"ב ובהונג קונג.');
        $this->seo->addBreadcrumb('אודות', SITE_URL . '/about');
        $this->render('page/about');
    }

    public function disclaimer(array $params = []): void
    {
        $this->seo->set('גילוי נאות', 'גילוי נאות ותנאי שימוש באתר xbt.co.il.');
        $this->seo->addBreadcrumb('גילוי נאות', SITE_URL . '/disclaimer');
        $this->render('page/disclaimer');
    }

    public function contact(array $params = []): void
    {
        $this->seo->set('צור קשר', 'צור קשר עם צוות xbt.co.il.');
        $this->seo->addBreadcrumb('צור קשר', SITE_URL . '/contact');
        $this->render('page/contact');
    }

    public function faq(array $params = []): void
    {
        $this->seo->set('שאלות נפוצות', 'שאלות ותשובות נפוצות על השקעות, שוק ההון ושימוש באתר xbt.co.il.');
        $this->seo->addBreadcrumb('שאלות נפוצות', SITE_URL . '/faq');
        $faqs = $this->faqs('home');
        if (empty($faqs)) {
            $faqs = [
                ['question' => 'האם המידע באתר מהווה ייעוץ השקעות?', 'answer' => DISCLAIMER_TEXT],
                ['question' => 'מאיפה מגיעים הנתונים?', 'answer' => 'הנתונים נשמרים במסד נתונים ומיועדים למטרות מידע ולימוד. האתר בנוי לתמוך בחיבור עתידי לספקי נתונים בזמן אמת.'],
                ['question' => 'אילו שווקים מכוסים באתר?', 'answer' => 'האתר מתמקד בשוק ההון בארה"ב ובבורסת הונג קונג, וכולל מניות, ETF, אג"ח, מדדים, קריפטו, סחורות ועוד.'],
            ];
        }
        $this->seo->addFaqSchema($faqs);
        $this->render('page/faq', ['faqs' => $faqs]);
    }
}
