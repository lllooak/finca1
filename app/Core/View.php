<?php
declare(strict_types=1);

namespace App\Core;

/**
 * View - renders PHP templates within the main layout.
 */
final class View
{
    private static array $shared = [];
    private static array $scripts = [];

    public static function share(string $key, $value): void
    {
        self::$shared[$key] = $value;
    }

    /** Queue JS to be output before </body>. */
    public static function pushScript(string $js): void
    {
        self::$scripts[] = $js;
    }

    public static function scripts(): string
    {
        if (empty(self::$scripts)) {
            return '';
        }
        return '<script>' . implode("\n", self::$scripts) . '</script>';
    }

    /**
     * Render a view inside the main layout.
     *
     * @param string $template e.g. 'stock/show'
     * @param array  $data     variables for the view
     */
    public static function render(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        $data = array_merge(self::$shared, $data);
        $content = self::capture($template, $data);
        $data['content'] = $content;
        echo self::capture($layout, $data);
    }

    /** Render a partial and return as string. */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, array_merge(self::$shared, $data));
    }

    /** Render without a layout (e.g. XML, AJAX fragments). */
    public static function renderRaw(string $template, array $data = []): void
    {
        echo self::capture($template, array_merge(self::$shared, $data));
    }

    private static function capture(string $template, array $data): string
    {
        $file = VIEW_PATH . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: {$template}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}
