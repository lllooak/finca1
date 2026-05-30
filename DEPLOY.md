# העלאה ל-cPanel — xbt.co.il

מדריך קצר להעלאת האתר ומסד הנתונים לאחסון cPanel.

## מה כלול בחבילה
- כל קבצי האתר (`app/`, `public/`, `config/`, `database/`, ...).
- `database/xbt_finance.sql` — **dump מלא של מסד הנתונים האמיתי** (Marketstack).
- `database/install.sql` — סכימה ריקה (לא חובה אם מייבאים את ה-dump).

## דרישות בשרת
- PHP 8.1+ עם התוספים `pdo_mysql`, `mbstring` (וב-cPanel בדרך כלל זמינים כברירת מחדל).
- MySQL / MariaDB.
- Apache עם `mod_rewrite` (פעיל כברירת מחדל ב-cPanel).

---

## שלב 1 — יצירת מסד נתונים ב-cPanel
1. cPanel → **MySQL® Databases**.
2. צור Database חדש (למשל `xbtcoil_finance`). cPanel יוסיף תחילית של שם המשתמש.
3. צור **MySQL User** עם סיסמה חזקה.
4. שייך את המשתמש למסד הנתונים והענק **ALL PRIVILEGES**.
5. רשום לעצמך: שם DB מלא, שם משתמש מלא, סיסמה. (Host = `localhost`).

## שלב 2 — ייבוא הנתונים
1. cPanel → **phpMyAdmin** → בחר את מסד הנתונים שיצרת.
2. לשונית **Import** → העלה את `database/xbt_finance.sql` → **Go**.
3. אם הקובץ גדול מהמגבלה (כ-50MB) — דחוס אותו ל-`.sql.gz` והעלה את הגרסה הדחוסה, או השתמש ב-SSH:
   ```bash
   mysql -u DB_USER -p DB_NAME < database/xbt_finance.sql
   ```

> ה-dump **אינו** כולל `CREATE DATABASE`, כך שהוא נטען ישירות לתוך מסד הנתונים שבחרת.

## שלב 3 — העלאת קבצי האתר
חלץ את ה-ZIP והעלה את התוכן (cPanel → **File Manager** או FTP). בחר אחת משתי האפשרויות:

**אפשרות א' (מומלצת):** הצב את **Document Root** של הדומיין על תיקיית `public/` של הפרויקט.
- cPanel → **Domains** → ערוך את ה-Document Root ל-`.../xbt.co.il/public`.

**אפשרות ב':** העלה את כל תוכן הפרויקט ישירות ל-`public_html/`.
- קובץ ה-`.htaccess` בשורש מנתב אוטומטית את כל הבקשות אל `public/` וחוסם גישה ישירה ל-`app/`, `config/`, `database/`, `storage/`.

## שלב 4 — הגדרת חיבור למסד הנתונים
ערוך את `config/database.php` והזן את פרטי ה-DB מ-cPanel:
```php
'host'     => 'localhost',
'database' => 'xbtcoil_finance',   // השם המלא מ-cPanel
'username' => 'xbtcoil_user',
'password' => 'YOUR_PASSWORD',
```
> לחלופין ניתן להגדיר משתני סביבה `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

## שלב 5 — הרשאות
ודא שתיקיית `storage/` ותתי-התיקיות שלה ניתנות לכתיבה (ה-cache והלוגים):
```
storage/        755 (או 775)
storage/cache/  755
storage/logs/   755
```

## שלב 6 — בדיקה
- גלוש לדומיין. עמוד הבית אמור להציג מניות, מדדים, ETF וחדשות.
- בדוק עמוד מניה: `/stock/AAPL`, מדד: `/indices`, מפת אתר: `/sitemap.xml`.

---

## רענון נתונים (אופציונלי, בשרת עם SSH + מפתח Marketstack)
```bash
# נתוני שוק מלאים (~25-30 דק', ~1,400 קריאות API)
MARKETSTACK_API_KEY=YOUR_KEY php database/import.php

# תוכן בלבד (מדריכים/מילון/חדשות) ללא API
php database/seed_content.php

# ניקוי קאש
php bin/cache-clear.php
```

## הערות
- מצב הריצה הוא `production` כברירת מחדל (שגיאות לא מוצגות למשתמש; נכתבות ל-`storage/logs/`).
- אג"ח, סחורות, קריפטו, אופציות וחוזים אינם זמינים בתוכנית ה-Basic של Marketstack ולכן הטבלאות שלהם ריקות.
