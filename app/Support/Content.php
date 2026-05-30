<?php
declare(strict_types=1);

namespace App\Support;

/**
 * Content - dynamic Hebrew financial content generator.
 * Builds rich, contextual analysis paragraphs from structured data
 * so detail pages reach professional depth (2,500-4,000+ words).
 */
final class Content
{
    /** Split plain-text (\n\n-separated) into HTML <p> blocks. */
    private static function renderParagraphs(string $text): string
    {
        $parts = array_filter(array_map('trim', preg_split('/\n{2,}/', $text) ?: []));
        if (empty($parts)) return '';
        return implode("\n", array_map(fn($p) => '<p>' . e($p) . '</p>', $parts));
    }

    public static function stockOverview(array $s): string
    {
        $name = $s['company_name_he'] ?: $s['company_name'];
        $ticker = $s['ticker'];
        $sector = $s['sector_name_he'] ?? $s['sector_name'] ?? 'הסקטור שלה';
        $country = $s['country_name_he'] ?? $s['country_name'] ?? 'בארה"ב';
        $cap = big_number($s['market_cap'] ?? 0, $s['currency_code']);
        $p = [];
        $p[] = "<p><strong>{$name}</strong> (סימול: <strong>{$ticker}</strong>) היא חברה הנסחרת בבורסה ומשתייכת לסקטור {$sector}. נכון לעדכון האחרון, שווי השוק של החברה עומד על כ-{$cap}, מה שממצב אותה כשחקנית משמעותית בתחומה. החברה פועלת בעיקר בשוק " . $country . " ומהווה נקודת עניין עבור משקיעים המחפשים חשיפה לתחום זה.</p>";

        if (!empty($s['description_he'])) {
            $p[] = self::renderParagraphs($s['description_he']);
        } elseif (!empty($s['description'])) {
            $p[] = self::renderParagraphs($s['description']);
        }

        $pe = $s['pe_ratio'] ?? null;
        $peText = $pe ? "מכפיל הרווח (P/E) הנוכחי עומד על " . num($pe) : "נתוני התמחור משתנים בהתאם לתנאי השוק";
        $p[] = "<p>מבחינת תמחור, {$peText}. נתון זה משקף את הציפיות של השוק לגבי קצב הצמיחה העתידי של החברה ואת רמת הסיכון הגלומה בהשקעה. השוואת המכפיל לחברות מקבילות באותו סקטור מספקת אינדיקציה לשאלה האם המניה נסחרת בפרמיה או בדיסקאונט יחסית למתחרותיה.</p>";

        return implode("\n", $p);
    }

    public static function stockProducts(array $s): string
    {
        $name = $s['company_name_he'] ?: $s['company_name'];
        if (!empty($s['products_services'])) {
            return self::renderParagraphs($s['products_services']);
        }
        return "<p>מוצרי ושירותי {$name} מכסים מגוון רחב של קטגוריות המשרתות לקוחות פרטיים ועסקיים. פורטפוליו המוצרים פותח לאורך השנים תוך שילוב חדשנות טכנולוגית עם הבנה מעמיקה של צרכי השוק. ההשקעה המתמדת בפיתוח מוצרים חדשים ובשיפור המוצרים הקיימים מאפשרת לחברה לשמור על רלוונטיות ועל יתרון תחרותי בשוק הדינמי בו היא פועלת.</p>";
    }

    public static function stockBusiness(array $s): string
    {
        $name = $s['company_name_he'] ?: $s['company_name'];
        if (!empty($s['business_model'])) {
            return self::renderParagraphs($s['business_model']);
        }
        return "<p>המודל העסקי של {$name} מבוסס על יצירת ערך ללקוחותיה תוך שמירה על יתרון תחרותי בר-קיימא. החברה משקיעה משאבים בפיתוח, בשיווק ובתפעול במטרה להרחיב את נתח השוק שלה ולשפר את שולי הרווחיות לאורך זמן. הבנת המודל העסקי היא קריטית להערכת איכות ההשקעה: חברות עם מקורות הכנסה חוזרים, נאמנות לקוחות גבוהה ויכולת תמחור חזקה נוטות להציג ביצועים יציבים יותר לאורך מחזורי שוק שונים.</p>";
    }

    public static function stockRevenueSources(array $s): string
    {
        if (!empty($s['revenue_sources'])) {
            return self::renderParagraphs($s['revenue_sources']);
        }
        $rev = big_number($s['revenue'] ?? 0, $s['currency_code']);
        return "<p>ההכנסות השנתיות של החברה מסתכמות בכ-{$rev}. מקורות ההכנסה מתחלקים בין מגזרי הפעילות השונים של החברה, כאשר פיזור רחב של מקורות הכנסה מפחית את התלות במוצר או בלקוח בודד ומגדיל את עמידות החברה בפני זעזועים. משקיעים נוהגים לבחון את שיעור הצמיחה של ההכנסות, את התמהיל בין ההכנסות החוזרות לחד-פעמיות, ואת התרומה היחסית של כל מגזר לשורה התחתונה.</p>";
    }

    public static function stockCompetitiveAdvantages(array $s): string
    {
        if (!empty($s['competitive_advantages'])) {
            return self::renderParagraphs($s['competitive_advantages']);
        }
        return "<p>היתרונות התחרותיים (Moat) של החברה עשויים לכלול מותג חזק, חסמי כניסה גבוהים, יתרונות לגודל (Economies of Scale), קניין רוחני, אפקט רשת או עלויות מעבר גבוהות עבור הלקוחות. יתרונות אלו מאפשרים לחברה לשמר רווחיות עודפת לאורך זמן ולהגן על נתח השוק שלה מפני מתחרים. ככל שהחפיר התחרותי רחב ועמיד יותר, כך גדלה הסבירות שהחברה תשמור על תשואה גבוהה על ההון המושקע (ROIC) לאורך שנים.</p>";
    }

    public static function stockGrowthDrivers(array $s): string
    {
        if (!empty($s['growth_drivers'])) {
            return self::renderParagraphs($s['growth_drivers']);
        }
        return "<p>מנועי הצמיחה המרכזיים כוללים הרחבה לשווקים גיאוגרפיים חדשים, השקת מוצרים ושירותים חדשניים, מיזוגים ורכישות אסטרטגיים, וכן מגמות מאקרו-כלכליות התומכות בביקוש למוצרי החברה. בנוסף, השקעות בטכנולוגיה ובדיגיטציה עשויות לשפר את היעילות התפעולית ולהרחיב את שולי הרווח. הערכת פוטנציאל הצמיחה דורשת ניתוח של גודל השוק הפוטנציאלי (TAM), קצב החדירה של החברה וההשקעות הנדרשות למימוש ההזדמנויות.</p>";
    }

    public static function stockRisks(array $s): string
    {
        if (!empty($s['risks'])) {
            return self::renderParagraphs($s['risks']);
        }
        return "<p>בין הסיכונים המרכזיים ניתן למנות תחרות גוברת בענף, שינויים רגולטוריים, תלות בלקוחות או ספקים מרכזיים, חשיפה למחזוריות כלכלית, וסיכוני שער חליפין עבור פעילות בינלאומית. כמו כן, רמת המינוף הפיננסי של החברה ויכולתה לשרת את חובותיה בסביבת ריבית גבוהה מהווים גורם סיכון מהותי. משקיעים צריכים לשקול סיכונים אלו מול פוטנציאל התשואה ולוודא שההשקעה תואמת את פרופיל הסיכון האישי שלהם.</p>";
    }

    public static function stockValuation(array $s): string
    {
        $pe = $s['pe_ratio'] ? num($s['pe_ratio']) : '—';
        $fpe = $s['forward_pe'] ? num($s['forward_pe']) : '—';
        $ps = $s['ps_ratio'] ? num($s['ps_ratio']) : '—';
        $pb = $s['pb_ratio'] ? num($s['pb_ratio']) : '—';
        return "<p>ניתוח התמחור של המניה משלב מספר מכפילים מרכזיים: מכפיל הרווח (P/E) עומד על {$pe}, מכפיל הרווח החזוי (Forward P/E) על {$fpe}, מכפיל המכירות (P/S) על {$ps} ומכפיל ההון (P/B) על {$pb}. השוואת מכפילים אלו לממוצע הענפי ולממוצע ההיסטורי של החברה מסייעת לקבוע האם המניה נסחרת בתמחור הוגן, זול או יקר. חשוב לזכור כי מכפילים גבוהים מוצדקים לעיתים בשל ציפיות צמיחה גבוהות, בעוד שמכפילים נמוכים עשויים להעיד על הזדמנות או על בעיות מבניות בעסק.</p>";
    }

    public static function stockDividend(array $s): string
    {
        $yield = $s['dividend_yield'] ?? 0;
        if ($yield > 0) {
            $y = num($yield) . '%';
            $payout = $s['payout_ratio'] ? num($s['payout_ratio']) . '%' : 'לא זמין';
            return "<p>החברה מחלקת דיבידנד עם תשואת דיבידנד נוכחית של {$y}. יחס חלוקת הרווחים (Payout Ratio) עומד על {$payout}, נתון המעיד על חלק הרווח המוחזר לבעלי המניות לעומת החלק המושקע מחדש בעסק. תשואת דיבידנד יציבה ועקבית לאורך זמן, בשילוב צמיחה בדיבידנד, מהווה מאפיין מבוקש עבור משקיעי הכנסה ומשקיעים סולידיים המחפשים תזרים מזומנים שוטף מהשקעותיהם.</p>";
        }
        return "<p>נכון לעדכון האחרון, החברה אינה מחלקת דיבידנד או מחלקת דיבידנד זניח. חברות צמיחה רבות בוחרות להשקיע מחדש את מלוא רווחיהן בהרחבת הפעילות במקום לחלקם כדיבידנד, מתוך הנחה שהשקעה זו תניב תשואה גבוהה יותר לבעלי המניות בטווח הארוך באמצעות עליית ערך המניה.</p>";
    }

    public static function stockPerformance(array $s): string
    {
        $ytd = isset($s['ytd_return']) ? pct($s['ytd_return']) : '—';
        $y1 = isset($s['return_1y']) ? pct($s['return_1y']) : '—';
        $hi = money($s['week52_high'] ?? null, $s['currency_code']);
        $lo = money($s['week52_low'] ?? null, $s['currency_code']);
        $beta = $s['beta'] ? num($s['beta']) : '—';
        return "<p>מתחילת השנה רשמה המניה תשואה של {$ytd}, ובמהלך 12 החודשים האחרונים תשואה של {$y1}. טווח המסחר ב-52 השבועות האחרונים נע בין שפל של {$lo} לשיא של {$hi}. מקדם הביטא (Beta) של המניה עומד על {$beta}, נתון המשקף את רמת התנודתיות של המניה ביחס לשוק הכללי: ביטא הגבוה מ-1 מעיד על תנודתיות גבוהה מהשוק, בעוד שביטא הנמוך מ-1 מעיד על תנודתיות מתונה יותר. ניתוח הביצועים ההיסטוריים מספק הקשר חשוב, אך אינו מהווה ערובה לביצועים עתידיים.</p>";
    }

    /** Generic FAQ generator when none exist in DB. */
    public static function stockFaqs(array $s): array
    {
        $name = $s['company_name_he'] ?: $s['company_name'];
        $ticker = $s['ticker'];
        $cap = big_number($s['market_cap'] ?? 0, $s['currency_code']);
        $faqs = [
            ['question' => "מהו סימול המניה של {$name}?", 'answer' => "סימול המניה של {$name} הוא {$ticker}."],
            ['question' => "מהו שווי השוק של {$name}?", 'answer' => "שווי השוק הנוכחי של {$name} עומד על כ-{$cap}."],
            ['question' => "האם {$name} מחלקת דיבידנד?", 'answer' => ($s['dividend_yield'] ?? 0) > 0
                ? "כן, {$name} מחלקת דיבידנד עם תשואה נוכחית של " . num($s['dividend_yield']) . "%."
                : "נכון לעכשיו {$name} אינה מחלקת דיבידנד משמעותי."],
            ['question' => "באיזו בורסה נסחרת {$name}?", 'answer' => "המניה נסחרת בבורסה " . ($s['exchange_name'] ?? 'הרלוונטית') . "."],
        ];
        return $faqs;
    }

    public static function etfOverview(array $e): string
    {
        $name = $e['name_he'] ?: $e['name'];
        $issuer = $e['issuer'] ?? 'מנהל הקרן';
        $aum = big_number($e['aum'] ?? 0, $e['currency_code']);
        $exp = $e['expense_ratio'] ? num($e['expense_ratio'], 2) . '%' : '—';
        $bench = $e['benchmark'] ?? 'מדד ייחוס';
        $out = "<p><strong>{$name}</strong> (סימול: <strong>" . e($e['ticker']) . "</strong>) היא קרן סל (ETF) המנוהלת על ידי {$issuer}. הקרן מנהלת נכסים בהיקף של כ-{$aum} ועוקבת אחר {$bench}. דמי הניהול השנתיים (Expense Ratio) עומדים על {$exp}, נתון מרכזי המשפיע ישירות על התשואה נטו של המשקיע לאורך זמן.</p>";
        if (!empty($e['description_he'])) {
            $out .= '<p>' . e($e['description_he']) . '</p>';
        }
        $out .= "<p>קרנות סל מציעות למשקיעים דרך יעילה וזולה להשגת פיזור רחב על פני עשרות או מאות ניירות ערך בעסקה אחת. במקום לבחור מניות בודדות, המשקיע רוכש יחידה אחת של הקרן וזוכה לחשיפה לכל אחזקותיה בהתאם למשקלן. יתרון זה הופך את קרנות הסל לכלי מועדף עבור משקיעים פסיביים ועבור בניית ליבת תיק השקעות מאוזנת.</p>";
        return $out;
    }

    public static function etfFaqs(array $e): array
    {
        $name = $e['name_he'] ?: $e['name'];
        return [
            ['question' => "מה דמי הניהול של {$name}?", 'answer' => "דמי הניהול השנתיים של הקרן עומדים על " . ($e['expense_ratio'] ? num($e['expense_ratio'], 2) . '%' : 'לא זמין') . "."],
            ['question' => "אחרי איזה מדד עוקבת {$name}?", 'answer' => "הקרן עוקבת אחר " . ($e['benchmark'] ?? 'מדד הייחוס שלה') . "."],
            ['question' => "מהו היקף הנכסים המנוהלים בקרן?", 'answer' => "היקף הנכסים המנוהלים (AUM) עומד על כ-" . big_number($e['aum'] ?? 0, $e['currency_code']) . "."],
        ];
    }

    /** Estimate word count for display. */
    public static function wordCount(string ...$texts): int
    {
        $all = strip_tags(implode(' ', $texts));
        return count(preg_split('/\s+/u', trim($all)) ?: []);
    }
}
