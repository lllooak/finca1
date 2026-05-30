<?php
declare(strict_types=1);

/**
 * Global helper functions for xbt.co.il
 */

if (!function_exists('e')) {
    /** HTML-escape a string. */
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /** Build an absolute URL from a path. */
    function url(string $path = ''): string
    {
        return SITE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return SITE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = function_exists('iconv') ? (@iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text) : $text;
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text !== '' ? $text : 'n-a';
    }
}

if (!function_exists('money')) {
    /** Format a money value with currency symbol. */
    function money($value, string $currency = 'USD', int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $symbols = ['USD' => '$', 'HKD' => 'HK$', 'EUR' => '€', 'GBP' => '£', 'CNY' => '¥', 'JPY' => '¥', 'ILS' => '₪'];
        $sym = $symbols[$currency] ?? ($currency . ' ');
        return $sym . number_format((float) $value, $decimals);
    }
}

if (!function_exists('big_number')) {
    /** Format large numbers as B/M/K (market cap, revenue, etc.). */
    function big_number($value, string $currency = ''): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $value = (float) $value;
        $abs = abs($value);
        $sym = '';
        if ($currency !== '') {
            $symbols = ['USD' => '$', 'HKD' => 'HK$', 'EUR' => '€', 'GBP' => '£', 'CNY' => '¥', 'ILS' => '₪'];
            $sym = $symbols[$currency] ?? ($currency . ' ');
        }
        if ($abs >= 1e12) {
            return $sym . number_format($value / 1e12, 2) . 'T';
        }
        if ($abs >= 1e9) {
            return $sym . number_format($value / 1e9, 2) . 'B';
        }
        if ($abs >= 1e6) {
            return $sym . number_format($value / 1e6, 2) . 'M';
        }
        if ($abs >= 1e3) {
            return $sym . number_format($value / 1e3, 2) . 'K';
        }
        return $sym . number_format($value, 2);
    }
}

if (!function_exists('pct')) {
    /** Format a percentage value. */
    function pct($value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        $value = (float) $value;
        $sign = $value > 0 ? '+' : '';
        return $sign . number_format($value, $decimals) . '%';
    }
}

if (!function_exists('change_class')) {
    /** Return a CSS class based on positive/negative/neutral value. */
    function change_class($value): string
    {
        $value = (float) $value;
        if ($value > 0) return 'text-up';
        if ($value < 0) return 'text-down';
        return 'text-flat';
    }
}

if (!function_exists('num')) {
    function num($value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float) $value, $decimals);
    }
}

if (!function_exists('int_num')) {
    function int_num($value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float) $value, 0);
    }
}

if (!function_exists('he_date')) {
    function he_date($date): string
    {
        if (!$date) return '—';
        $ts = is_numeric($date) ? (int) $date : strtotime((string) $date);
        if (!$ts) return '—';
        return date('d/m/Y', $ts);
    }
}

if (!function_exists('he_datetime')) {
    function he_datetime($date): string
    {
        if (!$date) return '—';
        $ts = is_numeric($date) ? (int) $date : strtotime((string) $date);
        if (!$ts) return '—';
        return date('d/m/Y H:i', $ts);
    }
}

if (!function_exists('excerpt')) {
    function excerpt(?string $text, int $length = 160): string
    {
        $text = trim(strip_tags((string) $text));
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $length)) . '…';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $code = 302): void
    {
        header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)), true, $code);
        exit;
    }
}

if (!function_exists('request_param')) {
    function request_param(string $key, $default = null)
    {
        $val = $_GET[$key] ?? $default;
        if (is_string($val)) {
            $val = trim($val);
        }
        return $val === '' ? $default : $val;
    }
}

if (!function_exists('logger')) {
    function logger(string $message, string $level = 'INFO'): void
    {
        $line = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $message);
        @file_put_contents(LOG_PATH . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
