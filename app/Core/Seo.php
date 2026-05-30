<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Seo - builds meta tags, Open Graph, Twitter Cards, canonical, and JSON-LD schema.
 */
final class Seo
{
    public string $title = '';
    public string $description = SEO_DEFAULT_DESC;
    public string $canonical = '';
    public string $ogType = 'website';
    public string $ogImage = '';
    public ?string $robots = null;
    public array $breadcrumbs = []; // [['name' => , 'url' => ], ...]
    public array $schemas = [];      // array of JSON-LD arrays

    public function __construct()
    {
        $this->canonical = SITE_URL . ($_SERVER['REQUEST_URI'] ?? '/');
        // Strip query strings from canonical except for whitelisted pages
        $this->canonical = strtok($this->canonical, '?') ?: $this->canonical;
        $this->ogImage = SITE_URL . '/assets/img/og-default.png';
    }

    public function set(string $title, ?string $description = null, ?string $canonical = null): self
    {
        $this->title = $title;
        if ($description !== null) {
            $this->description = excerpt($description, 300);
        }
        if ($canonical !== null) {
            $this->canonical = $canonical;
        }
        return $this;
    }

    public function fullTitle(): string
    {
        if ($this->title === '') {
            return SITE_NAME . ' - ' . SITE_TAGLINE;
        }
        return $this->title . SEO_TITLE_SUFFIX;
    }

    public function addBreadcrumb(string $name, string $url): self
    {
        $this->breadcrumbs[] = ['name' => $name, 'url' => $url];
        return $this;
    }

    public function addSchema(array $schema): self
    {
        $this->schemas[] = $schema;
        return $this;
    }

    public function addFaqSchema(array $faqs): self
    {
        if (empty($faqs)) {
            return $this;
        }
        $items = [];
        foreach ($faqs as $faq) {
            $q = $faq['question'] ?? $faq['q'] ?? null;
            $a = $faq['answer'] ?? $faq['a'] ?? null;
            if (!$q || !$a) continue;
            $items[] = [
                '@type' => 'Question',
                'name'  => $q,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => strip_tags($a),
                ],
            ];
        }
        if ($items) {
            $this->addSchema([
                '@context' => 'https://schema.org',
                '@type'    => 'FAQPage',
                'mainEntity' => $items,
            ]);
        }
        return $this;
    }

    public function addBreadcrumbSchema(): self
    {
        if (empty($this->breadcrumbs)) {
            return $this;
        }
        $items = [];
        foreach ($this->breadcrumbs as $i => $bc) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $bc['name'],
                'item'     => $bc['url'],
            ];
        }
        $this->addSchema([
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => $items,
        ]);
        return $this;
    }

    public function addOrganizationSchema(): self
    {
        $this->addSchema([
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => SITE_NAME,
            'url'      => SITE_URL,
            'logo'     => SITE_URL . '/assets/img/logo.png',
            'description' => SITE_TAGLINE,
        ]);
        return $this;
    }

    public function addArticleSchema(array $a): self
    {
        $this->addSchema([
            '@context' => 'https://schema.org',
            '@type'    => 'Article',
            'headline' => $a['title'] ?? '',
            'description' => $a['description'] ?? '',
            'datePublished' => $a['published'] ?? date('c'),
            'dateModified'  => $a['modified'] ?? ($a['published'] ?? date('c')),
            'author'   => ['@type' => 'Organization', 'name' => SITE_NAME],
            'publisher'=> [
                '@type' => 'Organization',
                'name'  => SITE_NAME,
                'logo'  => ['@type' => 'ImageObject', 'url' => SITE_URL . '/assets/img/logo.png'],
            ],
            'mainEntityOfPage' => $this->canonical,
        ]);
        return $this;
    }

    public function addFinancialProductSchema(array $p): self
    {
        $this->addSchema([
            '@context' => 'https://schema.org',
            '@type'    => 'FinancialProduct',
            'name'     => $p['name'] ?? '',
            'description' => $p['description'] ?? '',
            'category' => $p['category'] ?? 'Security',
            'url'      => $this->canonical,
        ]);
        return $this;
    }

    /** Render all head meta tags. */
    public function renderHead(): string
    {
        $out  = '<title>' . e($this->fullTitle()) . '</title>' . "\n";
        $out .= '<meta name="description" content="' . e($this->description) . '">' . "\n";
        $out .= '<link rel="canonical" href="' . e($this->canonical) . '">' . "\n";
        if ($this->robots) {
            $out .= '<meta name="robots" content="' . e($this->robots) . '">' . "\n";
        }
        // Open Graph
        $out .= '<meta property="og:site_name" content="' . e(SITE_NAME) . '">' . "\n";
        $out .= '<meta property="og:locale" content="' . e(SITE_LOCALE) . '">' . "\n";
        $out .= '<meta property="og:type" content="' . e($this->ogType) . '">' . "\n";
        $out .= '<meta property="og:title" content="' . e($this->fullTitle()) . '">' . "\n";
        $out .= '<meta property="og:description" content="' . e($this->description) . '">' . "\n";
        $out .= '<meta property="og:url" content="' . e($this->canonical) . '">' . "\n";
        $out .= '<meta property="og:image" content="' . e($this->ogImage) . '">' . "\n";
        // Twitter
        $out .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $out .= '<meta name="twitter:site" content="' . e(SEO_TWITTER) . '">' . "\n";
        $out .= '<meta name="twitter:title" content="' . e($this->fullTitle()) . '">' . "\n";
        $out .= '<meta name="twitter:description" content="' . e($this->description) . '">' . "\n";
        $out .= '<meta name="twitter:image" content="' . e($this->ogImage) . '">' . "\n";
        // JSON-LD
        $this->addBreadcrumbSchema();
        foreach ($this->schemas as $schema) {
            $out .= '<script type="application/ld+json">'
                . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                . '</script>' . "\n";
        }
        return $out;
    }
}
