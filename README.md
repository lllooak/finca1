# xbt.co.il — פורטל פיננסי מקצועי

מאגר מידע פיננסי מעמיק בעברית על שוק ההון בארה"ב ובבורסת הונג קונג: מניות, ETF, אג"ח, מדדים, קריפטו, מטבעות, סחורות, REIT, אופציות, חוזים עתידיים, סקטורים, תעשיות, Themes, מדריכים, מילון מונחים, חדשות, השוואות וסקרינרים.

נבנה ב-**PHP 8.2+ · MySQL 8+ · PDO · Bootstrap 5 (RTL) · Chart.js · Apache .htaccess** — ללא פריימוורקים חיצוניים, ללא Node, ללא WordPress.

---

## ✨ יכולות

- **100,000+ עמודים דינמיים** מתוך מסד הנתונים (מניות, ETF, אג"ח, מדדים, קריפטו, סחורות, מטבעות, REIT, אופציות, חוזים, סקטורים, תעשיות, Themes, מדינות, בורסות, מדריכים, מילון, השוואות, חדשות).
- **ארכיטקטורת MVC קלה** עם Router, View engine, PDO Database (Prepared Statements בלבד), Cache מבוסס-קבצים, ושכבת SEO.
- **תוכן עברי עשיר ודינמי** לכל עמוד נכס (סקירה, מודל עסקי, מקורות הכנסה, יתרונות תחרותיים, מנועי צמיחה, סיכונים, תמחור, דיבידנד, ביצועים).
- **סקרינרים** מרובים (מניות, דיבידנד, AI, רובוטיקה, הונג קונג, צמיחה, ערך) עם פילטרים דינמיים.
- **מנוע חיפוש** מהיר עם השלמה אוטומטית (AJAX) על כל סוגי הנכסים.
- **השוואות דינמיות** בין מניות בכתובת `/vs/AAPL/MSFT`.
- **SEO מלא**: Meta, Canonical, Open Graph, Twitter Cards, Breadcrumbs, ו-JSON-LD Schema (Organization, Article, FAQPage, BreadcrumbList, FinancialProduct), Sitemap XML דינמי + HTML, robots.txt.
- **נתונים אמיתיים מ-Marketstack** (Marketstack v2 API): מחירי EOD, היסטוריית מחירים, דיבידנדים, פיצולים, בורסות, מטבעות, מדדים, מידע על חברות. שכבת Data Provider מודולרית (`app/Providers/`) עם `MarketstackProvider` פעיל לרענון על-פי דרישה.
- **ביצועים**: אינדקסים, Fulltext, Pagination, Caching, נכסים מוקטנים מ-CDN.

---

## 📁 מבנה הפרויקט

```
xbt.co.il/
├── public/                 # שורש האתר (DocumentRoot)
│   ├── index.php           # Front Controller
│   ├── .htaccess           # Rewrite + caching + security headers
│   ├── robots.txt
│   └── assets/{css,js,img}
├── app/
│   ├── bootstrap.php        # Autoloader + config + helpers
│   ├── routes.php           # הגדרת כל הנתיבים
│   ├── Core/                # Database, Cache, Router, View, Seo, helpers
│   ├── Controllers/         # בקרים לכל סוגי העמודים
│   ├── Views/               # תבניות (layout, partials, עמודים)
│   ├── Support/Content.php  # מחולל תוכן עברי דינמי
│   └── Providers/           # שכבת ספקי נתונים (MySQL + חיבורים עתידיים)
├── config/
│   ├── config.php           # הגדרות אפליקציה
│   └── database.php         # פרטי חיבור למסד הנתונים
├── database/
│   ├── install.sql          # סכימת מסד הנתונים המלאה
│   ├── import.php           # מייבא נתונים אמיתיים מ-Marketstack
│   ├── seed_content.php     # מילוי תוכן בלבד (מדריכים/מילון/חדשות) ללא API
│   └── seeders/             # ms_stocks, ms_assets, content
├── storage/{cache,logs}/    # קבצי קאש ולוגים
└── bin/cache-clear.php      # ניקוי קאש מ-CLI
```

---

## 🚀 התקנה

### דרישות מקדימות
- PHP 8.2+ עם תוספי `pdo_mysql`, `mbstring`
- MySQL 8.0+
- Apache עם `mod_rewrite` (או Nginx עם rewrite מתאים)

### שלב 1 — קבצים
העתק את כל תיקיית הפרויקט לשרת. הגדר את ה-**DocumentRoot** של Apache לתיקיית `public/`.

> אם אינך יכול לשנות את ה-DocumentRoot, קובץ `.htaccess` בשורש מפנה אוטומטית ל-`public/`.

### שלב 2 — מסד הנתונים
צור את הסכימה (הקובץ יוצר את מסד הנתונים `xbt_finance`):

```bash
mysql -u root -p < database/install.sql
```

### שלב 3 — הגדרת חיבור
ערוך את `config/database.php` (או הגדר משתני סביבה `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`):

```php
'host'     => '127.0.0.1',
'database' => 'xbt_finance',
'username' => 'root',
'password' => 'your_password',
```

### שלב 4 — ייבוא נתונים אמיתיים מ-Marketstack
הגדר את מפתח ה-API והרץ את המייבא (מושך נתונים אמיתיים מ-Marketstack v2):

```bash
# Windows (PowerShell)
$env:MARKETSTACK_API_KEY='YOUR_KEY'; php database/import.php

# Linux/Mac
MARKETSTACK_API_KEY=YOUR_KEY php database/import.php
```

> **נתונים אמיתיים בלבד.** המייבא מושך מ-Marketstack מחירי EOD, היסטוריית מחירים, דיבידנדים ופיצולים, רשימת בורסות ומטבעות, מדדים ומידע על חברות (סקטור/תעשייה/עובדים). שדות שאינם מסופקים על-ידי Marketstack (מכפילים, שולי רווח וכו') נשארים ריקים — אין נתונים מומצאים. המייבא מכבד את מגבלת 10,000 בקשות/חודש (ריצה אחת ≈ 1,400 בקשות).

נתונים שנטענים (ריצה אופיינית):

| נתון | כמות |
|------|------|
| בורסות | 2,850+ |
| מטבעות | 43 |
| סקטורים / תעשיות | 20 / 100+ |
| מניות (ארה"ב + הונג קונג) | ~2,400 |
| היסטוריית מחירים | ~66,000 שורות |
| דיבידנדים | ~12,000 |
| פיצולי מניות | ~650 |
| מדדים | 124 |
| ETF | ~106 |
| Themes | 12 |
| מדריכים / מילון / חדשות / השוואות | 110 / 210 / 110 / 30 |

> **הערה:** אג"ח, סחורות, קריפטו, אופציות וחוזים עתידיים אינם זמינים בתוכנית ה-Basic של Marketstack ולכן אינם נטענים.
>
> לרענון התוכן בלבד (מדריכים/מילון/חדשות) ללא קריאות API: `php database/seed_content.php`.

זמן ריצה אופייני: ~25-30 דקות (תלוי בקצב ה-API).

### שלב 5 — הרשאות
ודא שתיקיית `storage/` ניתנת לכתיבה על-ידי שרת האינטרנט:

```bash
chmod -R 775 storage
```

### שלב 6 — גלישה
פתח את האתר בדפדפן. עמוד הבית יציג Market Overview, Trending, Top Gainers/Losers, סקציות Themes, חדשות ועוד.

---

## 🧪 הרצה מקומית מהירה (ללא Apache)

```bash
php -S localhost:8000 -t public
```

גלוש ל-`http://localhost:8000`.

> בשרת המובנה של PHP, ה-`.htaccess` אינו פעיל; ה-Front Controller (`public/index.php`) מטפל בניתוב, אך ייתכן שתצטרך לגלוש דרך `index.php`. מומלץ Apache לפרודקשן.

---

## 🔌 שכבת ספקי נתונים

המערכת בנויה עם שכבת ספקים מודולרית (`app/Providers/`). הנתונים נטענים מ-Marketstack למסד הנתונים, ו-`MarketstackProvider` משמש לרענון על-פי דרישה.
כדי לחבר ספק נוסף:

1. עדכן את טבלת `api_sources`: הגדר `api_key` ו-`is_enabled = 1` עבור השורה המתאימה.
2. `ProviderManager` יזהה את הספק ויעדיף אותו על פני MySQL.
3. ספקים נוספים (Polygon, Alpha Vantage וכו') מתווספים ב-`ProviderManager::factory()` לפי אותו `DataProviderInterface`.

---

## ⚙️ ניהול קאש

```bash
php bin/cache-clear.php
```

הקאש נשמר ב-`storage/cache/` (מחולק ל-shards). ניתן לכבות אותו ב-`config/config.php` (`CACHE_ENABLED`).

---

## 🔍 נתיבים מרכזיים

| נתיב | תיאור |
|------|-------|
| `/` | עמוד הבית |
| `/stocks`, `/stock/{ticker}` | מניות |
| `/etfs`, `/etf/{ticker}` | ETF |
| `/bonds`, `/bond/{slug}` | אג"ח |
| `/indices`, `/index/{slug}` | מדדים |
| `/reits`, `/reit/{ticker}` | REIT |
| `/crypto`, `/crypto/{slug}` | קריפטו |
| `/commodities`, `/currencies` | סחורות ומטבעות |
| `/sector/{slug}`, `/theme/{slug}` | סקטורים ו-Themes |
| `/screener`, `/screener/{type}` | סקרינרים |
| `/compare`, `/vs/{a}/{b}` | השוואות |
| `/guides`, `/glossary`, `/news` | תוכן |
| `/search`, `/api/search` | חיפוש |
| `/sitemap.xml`, `/sitemap` | מפת אתר |

---

## ⚠️ גילוי נאות

המידע באתר נועד למטרות מידע ולימוד בלבד ואינו מהווה ייעוץ השקעות, שיווק השקעות, המלצה או תחליף לייעוץ מקצועי המותאם לצרכיו האישיים של כל אדם. נתוני השוק מסופקים על-ידי Marketstack ועשויים להיות מושהים (End-of-Day). התוכן המערכתי (מדריכים, מילון, חדשות) נכתב למטרות לימוד.

---

© xbt.co.il
