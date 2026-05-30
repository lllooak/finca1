<?php
declare(strict_types=1);

namespace App\Providers;

/**
 * MarketstackProvider - maps the Marketstack v2 REST API to the
 * normalized DataProviderInterface. Used for on-demand refresh of
 * quotes / profiles / candles. Enabled via the api_sources table.
 */
final class MarketstackProvider extends HttpProvider
{
    public function name(): string
    {
        return 'marketstack';
    }

    public function getQuote(string $symbol): ?array
    {
        $sym = strtoupper($symbol);
        $d = $this->get('eod', ['symbols' => $sym, 'sort' => 'DESC', 'limit' => 2, 'access_key' => $this->apiKey]);
        $bars = $d['data'] ?? null;
        if (!$bars || !isset($bars[0]['close'])) {
            return null;
        }
        $price = (float) $bars[0]['close'];
        $prev = isset($bars[1]['close']) ? (float) $bars[1]['close'] : null;
        return [
            'symbol'     => $sym,
            'price'      => $price,
            'change'     => $prev !== null ? round($price - $prev, 4) : null,
            'change_pct' => ($prev !== null && $prev > 0) ? round(($price - $prev) / $prev * 100, 4) : null,
            'high'       => $bars[0]['high'] ?? null,
            'low'        => $bars[0]['low'] ?? null,
            'prev_close' => $prev,
            'date'       => isset($bars[0]['date']) ? substr((string) $bars[0]['date'], 0, 10) : null,
        ];
    }

    public function getProfile(string $symbol): ?array
    {
        $sym = strtoupper($symbol);
        $d = $this->get('tickerinfo', ['ticker' => $sym, 'access_key' => $this->apiKey]);
        $info = $d['data'] ?? null;
        if (!$info) {
            return null;
        }
        return [
            'symbol'    => $sym,
            'name'      => $info['name'] ?? null,
            'sector'    => $info['sector'] ?? null,
            'industry'  => $info['industry'] ?? null,
            'employees' => is_numeric($info['full_time_employees'] ?? null) ? (int) $info['full_time_employees'] : null,
        ];
    }

    public function getCandles(string $symbol, string $resolution = 'D', int $limit = 260): array
    {
        $sym = strtoupper($symbol);
        $d = $this->get('eod', ['symbols' => $sym, 'sort' => 'DESC', 'limit' => min($limit, 1000), 'access_key' => $this->apiKey]);
        $bars = $d['data'] ?? null;
        if (!$bars) {
            return [];
        }
        $out = [];
        foreach ($bars as $bar) {
            $out[] = [
                't' => substr((string) ($bar['date'] ?? ''), 0, 10),
                'o' => $bar['open'] ?? null, 'h' => $bar['high'] ?? null,
                'l' => $bar['low'] ?? null, 'c' => $bar['close'] ?? null, 'v' => $bar['volume'] ?? null,
            ];
        }
        return array_reverse($out); // ascending by date
    }
}
