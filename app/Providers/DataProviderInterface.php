<?php
declare(strict_types=1);

namespace App\Providers;

/**
 * DataProviderInterface - contract for all market data providers.
 * The application uses MysqlProvider (base) + MarketstackProvider;
 * additional providers (Polygon, Alpha Vantage, etc.) can be implemented
 * against this same interface without changing the rest of the app.
 */
interface DataProviderInterface
{
    public function name(): string;

    /** Return a normalized quote array for a symbol, or null. */
    public function getQuote(string $symbol): ?array;

    /** Return company/profile data for a symbol, or null. */
    public function getProfile(string $symbol): ?array;

    /** Return historical OHLC candles. */
    public function getCandles(string $symbol, string $resolution = 'D', int $limit = 260): array;

    /** Whether this provider is enabled/configured. */
    public function isEnabled(): bool;
}
