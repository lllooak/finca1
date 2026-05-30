<?php
/**
 * Seeder 4/4: guides, glossary, news, comparisons, faqs, api_sources, settings
 * @var PDO $pdo  @var \App\Core\Database $db  @var callable $log
 */

// ---------------- Guide categories ----------------
$gcats = [
    ['Beginner Investing','השקעות למתחילים'],['Stocks','מניות'],['ETFs','קרנות סל'],['Bonds','אג"ח'],
    ['Indices','מדדים'],['Dividend Investing','השקעות דיבידנד'],['Growth Investing','השקעות צמיחה'],
    ['Value Investing','השקעות ערך'],['China Investing','השקעות בסין'],['Hong Kong Investing','השקעות בהונג קונג'],
    ['AI Investing','השקעות בבינה מלאכותית'],['Robotics Investing','השקעות ברובוטיקה'],
];
$usedSlug = [];
$rows = [];
foreach ($gcats as $c) { $rows[] = ['name'=>$c[0],'name_he'=>$c[1],'slug'=>uslug($c[0],$usedSlug),'description'=>"מדריכים בנושא {$c[1]}.",'status'=>1]; }
batchInsert($pdo,'guide_categories',['name','name_he','slug','description','status'],$rows);
$gcatList = $db->all('SELECT id, name_he FROM guide_categories');
$log('Guide categories: ' . count($rows));

// ---------------- Guides (100+) ----------------
$guideTopics = [
    'איך להתחיל להשקיע בשוק ההון','מהי מניה וכיצד היא נסחרת','המדריך המלא לקרנות סל ETF','איך לקרוא דוח כספי של חברה',
    'מהו מכפיל רווח P/E וכיצד להשתמש בו','אסטרטגיית השקעות דיבידנד','השקעות ערך בשיטת וורן באפט','המדריך להשקעה במדד S&P 500',
    'איך להשקיע בבורסת הונג קונג','השקעה במניות סין - הזדמנויות וסיכונים','המדריך להשקעה במניות בינה מלאכותית','השקעה במניות מוליכים למחצה',
    'מהו פיזור תיק השקעות וכיצד לבנותו','ניהול סיכונים בתיק ההשקעות','השקעה לטווח ארוך מול מסחר','מהי ריבית דריבית וכוחה',
    'איך לבחור ברוקר למסחר','הבנת תשואת הדיבידנד','מהו שווי שוק ומדוע הוא חשוב','אסטרטגיית DCA - ממוצע עלות דולרי',
    'השקעה ב-REIT - נדל"ן מניב','מהן אג"ח וכיצד הן עובדות','עקום התשואות והשפעתו','אינפלציה והשפעתה על השקעות',
    'מהי תנודתיות ומקדם הביטא','ניתוח טכני מול ניתוח פונדמנטלי','השקעה במניות צמיחה','מהו תזרים מזומנים חופשי',
    'הבנת מאזן החברה','השקעה במגזר הטכנולוגיה','מדריך למשקיע במניות רובוטיקה','השקעה במחשוב קוונטי',
    'מהו ETF ממונף ומתי להשתמש בו','השקעה בקריפטו - מבוא','מטבעות יציבים והשימוש בהם','מדריך למשקיע במניות אנרגיה ירוקה',
];
$rows = [];
$usedSlug = [];
$gi = 0;
while (count($rows) < 110) {
    $base = $guideTopics[$gi % count($guideTopics)];
    $title = $gi < count($guideTopics) ? $base : $base . ' - חלק ' . (intdiv($gi, count($guideTopics)) + 1);
    $cat = pick($gcatList);
    $content = guideContent($title);
    $rows[] = ['category_id'=>(int)$cat['id'],'title'=>$title,'slug'=>uslug($title,$usedSlug),
        'summary'=>"מדריך מקיף בנושא: {$title}. כל מה שצריך לדעת, בשפה ברורה ובעברית, כולל דוגמאות ושאלות נפוצות.",
        'content'=>$content,'reading_time'=>rndi(6,22),'level'=>pick(['מתחילים','בינוני','מתקדם']),
        'is_featured'=>$gi<8?1:0,'views'=>rndi(50,9000),
        'meta_title'=>$title,'meta_desc'=>"מדריך: {$title}. הסבר מלא, דוגמאות ושאלות נפוצות.",
        'status'=>1,'published_at'=>date('Y-m-d H:i:s', strtotime('-'.rndi(1,400).' days'))];
    $gi++;
}
batchInsert($pdo,'guides',['category_id','title','slug','summary','content','reading_time','level','is_featured','views','meta_title','meta_desc','status','published_at'],$rows,10);
$log('Guides: ' . count($rows));

// ---------------- Glossary (200+) ----------------
$terms = [
    ['P/E Ratio','מכפיל רווח','מכפיל הרווח (P/E) הוא היחס בין מחיר המניה לרווח למניה.','Valuation','מחיר מניה / רווח למניה'],
    ['EPS','רווח למניה','רווח למניה הוא הרווח הנקי של החברה מחולק במספר המניות.','Earnings','רווח נקי / מספר מניות'],
    ['EBITDA','רווח לפני ריבית מס פחת והפחתות','מדד רווחיות תפעולית המנטרל השפעות מימון וחשבונאות.','Earnings',''],
    ['ROE','תשואה על ההון','היחס בין הרווח הנקי להון העצמי.','Profitability','רווח נקי / הון עצמי'],
    ['ROIC','תשואה על ההון המושקע','מדד ליעילות החברה בהפקת תשואה מההון המושקע.','Profitability',''],
    ['Free Cash Flow','תזרים מזומנים חופשי','המזומן שנותר לאחר הוצאות הוניות.','Cash Flow','תזרים תפעולי - השקעות הוניות'],
    ['Market Cap','שווי שוק','שווי החברה = מחיר מניה × מספר מניות.','Valuation','מחיר × מספר מניות'],
    ['Beta','ביטא','מדד לתנודתיות המניה ביחס לשוק.','Risk',''],
    ['Yield Curve','עקום תשואות','גרף המתאר תשואות אג"ח לפי מח"מ.','Bonds',''],
    ['Treasury','אג"ח ממשלתי','איגרת חוב בהנפקת ממשלת ארה"ב.','Bonds',''],
    ['Inflation','אינפלציה','עלייה כללית ברמת המחירים במשק.','Macro',''],
    ['Liquidity','נזילות','הקלות שבה ניתן להמיר נכס למזומן.','Markets',''],
    ['Volatility','תנודתיות','מידת השינוי במחיר נכס לאורך זמן.','Risk',''],
    ['Dividend','דיבידנד','חלוקת רווחים לבעלי המניות.','Income',''],
    ['P/B Ratio','מכפיל הון','היחס בין מחיר המניה להון העצמי למניה.','Valuation','מחיר / הון עצמי למניה'],
    ['P/S Ratio','מכפיל מכירות','היחס בין שווי השוק להכנסות.','Valuation','שווי שוק / הכנסות'],
    ['PEG Ratio','מכפיל PEG','מכפיל רווח מחולק בקצב הצמיחה.','Valuation','P/E / צמיחה'],
    ['Gross Margin','שולי רווח גולמי','אחוז הרווח הגולמי מההכנסות.','Profitability',''],
    ['Operating Margin','שולי רווח תפעולי','אחוז הרווח התפעולי מההכנסות.','Profitability',''],
    ['Net Margin','שולי רווח נקי','אחוז הרווח הנקי מההכנסות.','Profitability',''],
    ['Debt to Equity','יחס חוב להון','היחס בין סך החוב להון העצמי.','Leverage',''],
    ['Current Ratio','יחס שוטף','יכולת החברה לכסות התחייבויות שוטפות.','Liquidity',''],
    ['Bull Market','שוק שורי','תקופת עליות בשוק.','Markets',''],
    ['Bear Market','שוק דובי','תקופת ירידות בשוק.','Markets',''],
    ['Short Selling','מכירה בחסר','אסטרטגיה לרווח מירידת מחיר.','Trading',''],
    ['Index Fund','קרן מדד','קרן העוקבת אחר מדד.','Funds',''],
    ['Expense Ratio','דמי ניהול','עלות שנתית של קרן באחוזים.','Funds',''],
    ['AUM','נכסים מנוהלים','סך הנכסים שמנהלת קרן.','Funds',''],
    ['Coupon','קופון','תשלום הריבית התקופתי של אג"ח.','Bonds',''],
    ['Duration','מח"מ','מדד לרגישות אג"ח לשינויי ריבית.','Bonds',''],
];
$glossaryExtra = ['Alpha'=>'אלפא','Sharpe Ratio'=>'יחס שארפ','Standard Deviation'=>'סטיית תקן','Correlation'=>'מתאם','Diversification'=>'פיזור','Asset Allocation'=>'הקצאת נכסים','Rebalancing'=>'איזון מחדש','Capital Gains'=>'רווחי הון','Bid-Ask Spread'=>'מרווח קנייה-מכירה','Market Order'=>'הוראת שוק','Limit Order'=>'הוראת לימיט','IPO'=>'הנפקה ראשונית','Stock Split'=>'פיצול מניה','Buyback'=>'רכישה עצמית','Payout Ratio'=>'יחס חלוקה','Yield'=>'תשואה','Ex-Dividend Date'=>'יום האקס','Blue Chip'=>'בלו צ׳יפ','Penny Stock'=>'מניית אגורה','Float'=>'מניות במחזור','Short Interest'=>'ריבית שורט','Margin'=>'מרג׳ין','Leverage'=>'מינוף','Hedge'=>'גידור','Derivative'=>'נגזר','Option'=>'אופציה','Call Option'=>'אופציית רכש','Put Option'=>'אופציית מכר','Strike Price'=>'מחיר מימוש','Implied Volatility'=>'תנודתיות גלומה','Futures'=>'חוזה עתידי','Commodity'=>'סחורה','Forex'=>'מסחר במט"ח','Fiscal Year'=>'שנת כספים','Quarter'=>'רבעון','Guidance'=>'תחזית','Earnings Call'=>'שיחת רווחים','Analyst Rating'=>'דירוג אנליסט','Price Target'=>'מחיר יעד','Moat'=>'חפיר תחרותי','TAM'=>'שוק פוטנציאלי','CAGR'=>'צמיחה שנתית מורכבת','WACC'=>'עלות הון משוקללת','DCF'=>'היוון תזרים','Book Value'=>'ערך בספרים','Enterprise Value'=>'שווי פעילות','Goodwill'=>'מוניטין','Amortization'=>'הפחתה','Depreciation'=>'פחת','Working Capital'=>'הון חוזר'];
$usedSlug = [];
$rows = [];
foreach ($terms as $t) { $rows[] = glossaryRow($t[0],$t[1],$t[2],$t[3],$t[4],$usedSlug); }
$ti = 0;
foreach ($glossaryExtra as $en => $he) {
    $rows[] = glossaryRow($en,$he,"$he ($en) הוא מונח פיננסי חשוב בעולם ההשקעות.",pick(['Valuation','Risk','Markets','Trading','Profitability','Macro','Funds','Bonds']),'',$usedSlug);
}
// pad to 200+
$baseTerms = array_keys($glossaryExtra);
while (count($rows) < 210) {
    $en = pick($baseTerms) . ' ' . pick(['Index','Ratio','Model','Method','Strategy','Analysis']);
    $rows[] = glossaryRow($en,$en,"$en הוא מושג פיננסי המשמש בניתוח השקעות.",pick(['Valuation','Risk','Markets']),'',$usedSlug);
}
batchInsert($pdo,'glossary_terms',['term','term_he','slug','letter','category','definition','explanation','example','formula','meta_title','meta_desc','views','status'],$rows,40);
$log('Glossary: ' . count($rows));

// ---------------- News ----------------
require_once __DIR__ . '/../news_generator.php';
$ncats = [['US Stocks','מניות ארה"ב'],['Hong Kong','הונג קונג'],['AI','בינה מלאכותית'],['Robotics','רובוטיקה'],['Semiconductors','מוליכים למחצה'],['ETF','קרנות סל'],['Bonds','אג"ח'],['Crypto','קריפטו'],['Commodities','סחורות'],['Macro','מאקרו'],['Federal Reserve','הפדרל ריזרב'],['China','סין']];
$usedSlug = [];
$rows = [];
foreach ($ncats as $c) { $rows[] = ['name'=>$c[0],'name_he'=>$c[1],'slug'=>uslug($c[0],$usedSlug),'status'=>1]; }
batchInsert($pdo,'news_categories',['name','name_he','slug','status'],$rows);
$ncatList = $db->all('SELECT id, name_he FROM news_categories');

$headlines = ['השווקים עולים על רקע נתוני אינפלציה','מניות הטכנולוגיה מובילות את המסחר','הפד שומר על הריבית ללא שינוי','דוחות הרבעון מכים את התחזיות','מניות ה-AI ממשיכות בראלי','בורסת הונג קונג רושמת עליות','ביקושים חזקים למוליכים למחצה','משקיעים מגיבים לנתוני התעסוקה','הזהב שובר שיא חדש','הנפט מתייצב לאחר תנודתיות','מניות הרכב החשמלי תחת לחץ','דיבידנדים גדלים בסקטור הפיננסים','הקריפטו מתאושש','אג"ח ממשלתיות במוקד','סין מכריזה על תמריצים כלכליים','ענקיות הטכנולוגיה מדווחות צמיחה','שוק ה-IPO מתעורר','אנליסטים מעלים מחירי יעד'];
$usedSlug = [];
$rows = [];
for ($i = 0; $i < 110; $i++) {
    $cat = pick($ncatList);
    $title = pick($headlines) . ' - ' . date('d/m', strtotime('-'.rndi(0,120).' days')) . ' (' . ($i+1) . ')';
    $article = generate_news_article($title, (string)$cat['name_he'], $i + 1);
    $rows[] = ['category_id'=>(int)$cat['id'],'title'=>$title,'slug'=>uslug($title,$usedSlug),
        'summary'=>$article['summary'],
        'content'=>$article['content'],
        'source'=>pick(['xbt Research','Market Wire','Global Finance','Reuters','Bloomberg']),'author'=>pick(['מערכת xbt','צוות אנליסטים']),
        'tags'=>(function($list,$own){ $names=array_values(array_unique(array_map(fn($c)=>$c['name_he'],$list))); shuffle($names); $sel=[$own]; foreach($names as $nm){ if($nm!==$own){$sel[]=$nm;} if(count($sel)>=3)break; } return implode(',',$sel); })($ncatList,(string)$cat['name_he']),
        'is_featured'=>$i<6?1:0,'views'=>rndi(20,15000),
        'meta_title'=>$title,'meta_desc'=>'חדשות פיננסיות: '.$title,
        'status'=>1,'published_at'=>date('Y-m-d H:i:s', strtotime('-'.rndi(0,120).' days -'.rndi(0,23).' hours'))];
}
batchInsert($pdo,'news',['category_id','title','slug','summary','content','source','author','tags','is_featured','views','meta_title','meta_desc','status','published_at'],$rows,20);
$log('News: ' . count($rows));

// ---------------- Comparisons (50+) ----------------
$pairs = $db->all('SELECT id, ticker, company_name, company_name_he, sector_id FROM stocks WHERE is_featured=1 OR market_cap > 50000000000 ORDER BY market_cap DESC LIMIT 80');
$usedSlug = [];
$rows = [];
$made = 0;
for ($i = 0; $i < count($pairs) - 1 && $made < 55; $i++) {
    $a = $pairs[$i]; $b = $pairs[$i+1];
    $na = $a['company_name_he'] ?: $a['company_name']; $nb = $b['company_name_he'] ?: $b['company_name'];
    $title = "{$a['ticker']} מול {$b['ticker']}: {$na} או {$nb}?";
    $rows[] = ['title'=>$title,'slug'=>uslug($a['ticker'].'-vs-'.$b['ticker'],$usedSlug),'asset_type'=>'stock',
        'entity_a_type'=>'stock','entity_a_id'=>(int)$a['id'],'entity_a_label'=>"$na ({$a['ticker']})",
        'entity_b_type'=>'stock','entity_b_id'=>(int)$b['id'],'entity_b_label'=>"$nb ({$b['ticker']})",
        'summary'=>"השוואה מקיפה בין $na ל-$nb: תמחור, צמיחה, רווחיות וביצועים. מי עדיפה להשקעה?",
        'content'=>'','pros_a'=>"מובילות שוק\nמותג חזק\nתזרים מזומנים יציב",'cons_a'=>"תמחור גבוה\nתחרות גוברת",
        'pros_b'=>"פוטנציאל צמיחה\nתמחור אטרקטיבי",'cons_b'=>"תנודתיות גבוהה\nסיכון ענפי",
        'verdict'=>"הבחירה תלויה בפרופיל הסיכון ובאסטרטגיית ההשקעה.",
        'is_featured'=>$made<6?1:0,'views'=>rndi(30,8000),
        'meta_title'=>$title,'meta_desc'=>"השוואה: $na מול $nb.",'status'=>1];
    $made++; $i++; // skip to avoid overlap-heavy
}
batchInsert($pdo,'comparisons',['title','slug','asset_type','entity_a_type','entity_a_id','entity_a_label','entity_b_type','entity_b_id','entity_b_label','summary','content','pros_a','cons_a','pros_b','cons_b','verdict','is_featured','views','meta_title','meta_desc','status'],$rows,25);
$log('Comparisons: ' . count($rows));

// ---------------- FAQs (home + general) ----------------
$faqRows = [];
$homeFaqs = [
    ['מה זה xbt.co.il?','xbt.co.il הוא פורטל פיננסי מקיף בעברית המספק מידע על מניות, ETF, אג"ח, מדדים, קריפטו, סקטורים, מדריכים והשוואות בשוק ההון בארה"ב ובהונג קונג.'],
    ['האם המידע באתר מהווה ייעוץ השקעות?', DISCLAIMER_TEXT],
    ['אילו שווקים מכוסים באתר?','האתר מתמקד בשוק ההון בארה"ב ובבורסת הונג קונג, וכולל מגוון רחב של סוגי נכסים.'],
    ['כיצד ניתן לחפש מניה או נכס?','ניתן להשתמש במנוע החיפוש בראש העמוד כדי למצוא מניות, ETF, מדדים, מונחים ומדריכים במהירות.'],
    ['האם השימוש באתר בחינם?','כן, כל תכני המידע והמדריכים באתר זמינים לשימוש חופשי.'],
];
foreach ($homeFaqs as $i=>$f){ $faqRows[] = ['entity_type'=>'home','entity_id'=>null,'question'=>$f[0],'answer'=>$f[1],'position'=>$i,'status'=>1]; }
batchInsert($pdo,'faqs',['entity_type','entity_id','question','answer','position','status'],$faqRows);
$log('FAQs: ' . count($faqRows));

// ---------------- API sources (providers, disabled) ----------------
$apis = [
    ['Marketstack','marketstack','https://api.marketstack.com/v2','stocks,eod,exchanges,currencies,indices,dividends,splits,etf'],
    ['Polygon','polygon','https://api.polygon.io','stocks,options,forex,crypto'],
    ['Alpha Vantage','alpha-vantage','https://www.alphavantage.co/query','stocks,forex,crypto'],
    ['Twelve Data','twelve-data','https://api.twelvedata.com','stocks,forex,crypto,etf'],
    ['Yahoo Finance','yahoo-finance','https://query1.finance.yahoo.com','stocks,quotes'],
    ['Financial Modeling Prep','fmp','https://financialmodelingprep.com/api/v3','stocks,financials'],
    ['HKEX','hkex','https://www.hkex.com.hk','hk-stocks,indices'],
];
$msKey = getenv('MARKETSTACK_API_KEY') ?: '';
$rows = [];
foreach ($apis as $i=>$a) {
    $isMs = $a[1] === 'marketstack';
    $rows[] = ['name'=>$a[0],'slug'=>$a[1],'base_url'=>$a[2],'api_key'=>($isMs ? $msKey : ''),'priority'=>($i+1)*10,'rate_limit'=>($isMs ? 10000 : 60),'supports'=>$a[3],'is_enabled'=>($isMs && $msKey !== '') ? 1 : 0,'status'=>1];
}
batchInsert($pdo,'api_sources',['name','slug','base_url','api_key','priority','rate_limit','supports','is_enabled','status'],$rows);
$log('API sources: ' . count($rows));

// ---------------- Site settings ----------------
$settings = [['site_name','xbt.co.il'],['default_currency','USD'],['seed_version','1.0'],['seeded_at',date('c')]];
$rows = [];
foreach ($settings as $s) { $rows[] = ['setting_key'=>$s[0],'setting_value'=>$s[1],'autoload'=>1]; }
batchInsert($pdo,'site_settings',['setting_key','setting_value','autoload'],$rows);
$log('Settings: ' . count($rows));

// ---- helpers ----
function glossaryRow($en,$he,$def,$cat,$formula,&$usedSlug){
    $expl = "$he ($en) הוא מונח מרכזי בעולם ההשקעות והפיננסים. הבנת המונח מסייעת למשקיעים לקבל החלטות מושכלות. " . str_repeat("בפועל, המונח משמש אנליסטים ומשקיעים לצורך ניתוח והערכת נכסים פיננסיים. ", 3);
    $example = "לדוגמה, בעת ניתוח מניה, ניתן להשתמש ב$he כחלק מהערכת השווי וקבלת ההחלטה.";
    return ['term'=>$en,'term_he'=>$he,'slug'=>uslug($en,$usedSlug),'letter'=>strtoupper(substr($en,0,1)),'category'=>$cat,
        'definition'=>$def,'explanation'=>$expl,'example'=>$example,'formula'=>$formula ?: null,
        'meta_title'=>"$he ($en) - הגדרה והסבר",'meta_desc'=>substr($def,0,150),'views'=>rndi(10,5000),'status'=>1];
}
function guideContent($title){
    $secs = ['מבוא','עקרונות יסוד','כיצד זה עובד בפועל','דוגמה מספרית','טעויות נפוצות שכדאי להימנע מהן','אסטרטגיות מתקדמות','סיכום והמלצות'];
    $html = "<p>מדריך זה יעסוק בנושא <strong>{$title}</strong> בצורה מקיפה וברורה. נסקור את כל מה שמשקיע צריך לדעת, צעד אחר צעד.</p>";
    $html .= "<h2 id=\"toc\">תוכן העניינים</h2><ul>";
    foreach ($secs as $i=>$s){ $html .= "<li><a href=\"#s$i\">".e($s)."</a></li>"; }
    $html .= "</ul>";
    foreach ($secs as $i=>$s){
        $html .= "<h2 id=\"s$i\">".e($s)."</h2>";
        $html .= "<p>" . str_repeat("בחלק זה נעמיק בנושא {$s}. הבנת ההיבטים הללו חיונית לכל משקיע המעוניין לפעול בצורה מושכלת בשוק ההון. נתייחס למושגי המפתח, נסביר את ההיגיון מאחורי כל עיקרון ונדגים כיצד ליישם זאת בפועל. ", 4) . "</p>";
        $html .= "<p>" . str_repeat("חשוב לזכור כי כל החלטת השקעה צריכה להתבסס על ניתוח מעמיק, הבנת רמת הסיכון והתאמה ליעדים האישיים. ", 3) . "</p>";
    }
    return $html;
}
