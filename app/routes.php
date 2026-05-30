<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\StockController;
use App\Controllers\EtfController;
use App\Controllers\BondController;
use App\Controllers\IndexController;
use App\Controllers\CryptoController;
use App\Controllers\CommodityController;
use App\Controllers\CurrencyController;
use App\Controllers\ReitController;
use App\Controllers\CategoryController;
use App\Controllers\GuideController;
use App\Controllers\GlossaryController;
use App\Controllers\NewsController;
use App\Controllers\ComparisonController;
use App\Controllers\ScreenerController;
use App\Controllers\SearchController;
use App\Controllers\MarketController;
use App\Controllers\PageController;
use App\Controllers\SitemapController;

/** @var Router $router */

// Home
$router->get('/', [HomeController::class, 'index']);

// Search
$router->get('/search', [SearchController::class, 'index']);
$router->get('/api/search', [SearchController::class, 'autocomplete']);

// Stocks
$router->get('/stocks', [StockController::class, 'index']);
$router->get('/stocks/exchange/{slug}', [StockController::class, 'byExchange']);
$router->get('/stocks/country/{slug}', [StockController::class, 'byCountry']);
$router->get('/stock/{ticker}', [StockController::class, 'show']);
$router->get('/api/stock/{ticker}/chart', [StockController::class, 'chart']);

// ETFs
$router->get('/etfs', [EtfController::class, 'index']);
$router->get('/etf/{ticker}', [EtfController::class, 'show']);

// Bonds
$router->get('/bonds', [BondController::class, 'index']);
$router->get('/bond/{slug}', [BondController::class, 'show']);

// Indices
$router->get('/indices', [IndexController::class, 'index']);
$router->get('/index/{slug}', [IndexController::class, 'show']);

// Crypto
$router->get('/crypto', [CryptoController::class, 'index']);
$router->get('/crypto/{slug}', [CryptoController::class, 'show']);

// Commodities
$router->get('/commodities', [CommodityController::class, 'index']);
$router->get('/commodity/{slug}', [CommodityController::class, 'show']);

// Currencies
$router->get('/currencies', [CurrencyController::class, 'index']);
$router->get('/currency/{slug}', [CurrencyController::class, 'show']);

// REITs
$router->get('/reits', [ReitController::class, 'index']);
$router->get('/reit/{ticker}', [ReitController::class, 'show']);

// Categories (sectors / industries / themes)
$router->get('/sectors', [CategoryController::class, 'sectors']);
$router->get('/sector/{slug}', [CategoryController::class, 'sector']);
$router->get('/industries', [CategoryController::class, 'industries']);
$router->get('/industry/{slug}', [CategoryController::class, 'industry']);
$router->get('/themes', [CategoryController::class, 'themes']);
$router->get('/theme/{slug}', [CategoryController::class, 'theme']);
$router->get('/countries', [CategoryController::class, 'countries']);
$router->get('/country/{slug}', [CategoryController::class, 'country']);
$router->get('/exchanges', [CategoryController::class, 'exchanges']);
$router->get('/exchange/{slug}', [CategoryController::class, 'exchange']);

// Guides
$router->get('/guides', [GuideController::class, 'index']);
$router->get('/guides/category/{slug}', [GuideController::class, 'byCategory']);
$router->get('/guide/{slug}', [GuideController::class, 'show']);

// Glossary
$router->get('/glossary', [GlossaryController::class, 'index']);
$router->get('/glossary/{slug}', [GlossaryController::class, 'show']);

// News
$router->get('/news', [NewsController::class, 'index']);
$router->get('/news/category/{slug}', [NewsController::class, 'byCategory']);
$router->get('/news/{slug}', [NewsController::class, 'show']);

// Comparisons
$router->get('/compare', [ComparisonController::class, 'index']);
$router->get('/compare/{slug}', [ComparisonController::class, 'show']);
$router->get('/vs/{a}/{b}', [ComparisonController::class, 'dynamic']);

// Screeners
$router->get('/screener', [ScreenerController::class, 'index']);
$router->get('/screener/{type}', [ScreenerController::class, 'show']);
$router->get('/api/screener/{type}', [ScreenerController::class, 'data']);

// Market overview
$router->get('/markets', [MarketController::class, 'index']);
$router->get('/api/market/movers', [MarketController::class, 'movers']);

// Static / content pages
$router->get('/about', [PageController::class, 'about']);
$router->get('/disclaimer', [PageController::class, 'disclaimer']);
$router->get('/contact', [PageController::class, 'contact']);
$router->get('/faq', [PageController::class, 'faq']);

// SEO
$router->get('/sitemap.xml', [SitemapController::class, 'index']);
$router->get('/sitemap-{type}.xml', [SitemapController::class, 'section']);
$router->get('/sitemap', [SitemapController::class, 'html']);
