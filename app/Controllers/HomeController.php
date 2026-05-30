<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Cache;

class HomeController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->seo->set(
            'xbt.co.il - מידע פיננסי מעמיק על שוק ההון בארה"ב והונג קונג',
            SEO_DEFAULT_DESC,
            SITE_URL . '/'
        );

        $data = Cache::remember('home:v1', CACHE_TTL_LIST, function () {
            $db = $this->db;
            $base = "SELECT id, ticker, company_name, company_name_he, slug, price, currency_code,
                     day_change_pct, market_cap, pe_ratio, dividend_yield, volume FROM stocks WHERE status = 1";
            return [
                'stats' => [
                    'stocks'  => $db->count('SELECT COUNT(*) FROM stocks WHERE status=1'),
                    'etfs'    => $db->count('SELECT COUNT(*) FROM etfs WHERE status=1'),
                    'indices' => $db->count('SELECT COUNT(*) FROM indices WHERE status=1'),
                    'guides'  => $db->count('SELECT COUNT(*) FROM guides WHERE status=1'),
                    'glossary'=> $db->count('SELECT COUNT(*) FROM glossary_terms WHERE status=1'),
                ],
                'indices'  => $db->all('SELECT name, name_he, slug, symbol, value, day_change_pct FROM indices WHERE status=1 ORDER BY is_featured DESC, id ASC LIMIT 6'),
                'trending' => $db->all("$base AND market_cap IS NOT NULL ORDER BY volume DESC LIMIT 10"),
                'gainers'  => $db->all("$base AND day_change_pct IS NOT NULL ORDER BY day_change_pct DESC LIMIT 10"),
                'losers'   => $db->all("$base AND day_change_pct IS NOT NULL ORDER BY day_change_pct ASC LIMIT 10"),
                'active'   => $db->all("$base AND volume IS NOT NULL ORDER BY volume DESC LIMIT 10"),
                'dividend' => $db->all("$base AND dividend_yield > 0 ORDER BY dividend_yield DESC LIMIT 10"),
                'topus'    => $db->all("$base ORDER BY market_cap DESC LIMIT 10"),
                'tophk'    => $db->all("SELECT s.id, s.ticker, s.company_name, s.company_name_he, s.slug, s.price, s.currency_code, s.day_change_pct, s.market_cap, s.pe_ratio, s.dividend_yield, s.volume FROM stocks s JOIN exchanges e ON s.exchange_id=e.id WHERE s.status=1 AND e.code IN ('HKEX','SEHK') ORDER BY s.market_cap DESC LIMIT 10"),
                'themeStocks' => [
                    'ai'    => $this->themeStocks('artificial-intelligence'),
                    'robot' => $this->themeStocks('robotics'),
                    'semi'  => $this->themeStocks('semiconductors'),
                    'quantum' => $this->themeStocks('quantum-computing'),
                ],
                'etfs'     => $db->all('SELECT ticker, name, name_he, slug, price, currency_code, day_change_pct, aum, expense_ratio, dividend_yield FROM etfs WHERE status=1 ORDER BY aum DESC LIMIT 8'),
                'reits'    => $db->all('SELECT ticker, name, name_he, slug, price, currency_code, day_change_pct, market_cap, dividend_yield FROM reits WHERE status=1 ORDER BY market_cap DESC LIMIT 8'),
                'bonds'    => $db->all('SELECT name, name_he, slug, bond_type, yield, credit_rating, maturity_years FROM bonds WHERE status=1 ORDER BY yield DESC LIMIT 8'),
                'crypto'   => $db->all('SELECT name, name_he, slug, symbol, price, day_change_pct, market_cap FROM cryptos WHERE status=1 ORDER BY market_cap DESC LIMIT 8'),
                'commodities' => $db->all('SELECT name, name_he, slug, symbol, price, currency_code, day_change_pct, category FROM commodities WHERE status=1 ORDER BY id ASC LIMIT 8'),
                'sectors'  => $db->all('SELECT name, name_he, slug, icon FROM sectors WHERE status=1 ORDER BY id ASC LIMIT 12'),
                'themes'   => $db->all('SELECT name, name_he, slug, icon, category FROM themes WHERE status=1 ORDER BY is_featured DESC, id ASC LIMIT 12'),
                'guides_l' => $db->all('SELECT title, slug, summary, reading_time, level FROM guides WHERE status=1 ORDER BY is_featured DESC, views DESC LIMIT 6'),
                'comparisons' => $db->all('SELECT title, slug, summary FROM comparisons WHERE status=1 ORDER BY is_featured DESC, id DESC LIMIT 6'),
                'news'     => $db->all('SELECT n.title, n.slug, n.summary, n.image_url, n.published_at, c.name_he AS cat_he, c.name AS cat FROM news n LEFT JOIN news_categories c ON n.category_id=c.id WHERE n.status=1 ORDER BY n.published_at DESC LIMIT 8'),
                'faqs'     => $this->faqs('home'),
            ];
        });

        $this->seo->addFaqSchema($data['faqs']);
        $this->render('home/index', $data);
    }

    private function themeStocks(string $slug): array
    {
        return $this->db->all(
            "SELECT s.ticker, s.company_name, s.company_name_he, s.slug, s.price, s.currency_code,
                    s.day_change_pct, s.market_cap, s.pe_ratio, s.dividend_yield, s.volume
             FROM stocks s
             JOIN stock_themes st ON s.id = st.stock_id
             JOIN themes t ON st.theme_id = t.id
             WHERE t.slug = ? AND s.status = 1
             ORDER BY s.market_cap DESC LIMIT 8",
            [$slug]
        );
    }
}
