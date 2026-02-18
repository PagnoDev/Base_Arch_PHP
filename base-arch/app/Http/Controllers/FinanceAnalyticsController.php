<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceAnalyticsController extends Controller
{
    public function portfolioRiskReport(Request $request): JsonResponse|View
    {
        $user = $request->attributes->get('auth_user');
        $isAdmin = $user && $user->isAdmin();

        $lookbackDays = (int) config('finance.lookback_days', 30);
        $annualRiskFreeRate = (float) config('finance.risk_free_rate_annual', 0.08);
        $tradingDays = (int) config('finance.trading_days_per_year', 252);

        $latestPriceSub = DB::table('price_histories as ph')
            ->select('ph.asset_id', DB::raw('MAX(ph.price_date) as latest_date'))
            ->groupBy('ph.asset_id');

        $positionSnapshotQuery = DB::table('portfolio_positions as pp')
            ->join('portfolios as p', 'p.id', '=', 'pp.portfolio_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->join('assets as a', 'a.id', '=', 'pp.asset_id')
            ->joinSub($latestPriceSub, 'lp', function ($join) {
                $join->on('lp.asset_id', '=', 'pp.asset_id');
            })
            ->join('price_histories as ph', function ($join) {
                $join->on('ph.asset_id', '=', 'lp.asset_id')
                    ->on('ph.price_date', '=', 'lp.latest_date');
            })
            ->select([
                'p.id as portfolio_id',
                'p.name as portfolio_name',
                'u.name as owner_name',
                'u.email as owner_email',
                'a.symbol',
                'a.asset_class',
                'pp.quantity',
                'pp.average_cost',
                'ph.close_price as current_price',
                DB::raw('(pp.quantity * pp.average_cost) as cost_basis'),
                DB::raw('(pp.quantity * ph.close_price) as market_value'),
            ])
            ->where('a.active', true)
            ->orderBy('p.id');

        if (! $isAdmin) {
            $positionSnapshotQuery->where('p.user_id', $user->id);
        }

        $positionSnapshot = $positionSnapshotQuery->get();

        $dailyValuesQuery = DB::table('portfolio_positions as pp')
            ->join('portfolios as p', 'p.id', '=', 'pp.portfolio_id')
            ->join('assets as a', 'a.id', '=', 'pp.asset_id')
            ->join('price_histories as ph', 'ph.asset_id', '=', 'a.id')
            ->select([
                'p.id as portfolio_id',
                'ph.price_date',
                DB::raw('SUM(pp.quantity * ph.close_price) as daily_value'),
            ])
            ->where('a.active', true)
            ->where('ph.price_date', '>=', now()->subDays($lookbackDays)->toDateString())
            ->groupBy(['p.id', 'ph.price_date'])
            ->orderBy('ph.price_date');

        if (! $isAdmin) {
            $dailyValuesQuery->where('p.user_id', $user->id);
        }

        $dailyValues = $dailyValuesQuery
            ->get()
            ->groupBy('portfolio_id');

        $report = $positionSnapshot
            ->groupBy('portfolio_id')
            ->map(function (Collection $rows, int $portfolioId) use ($dailyValues, $annualRiskFreeRate, $tradingDays) {
                $costBasis = (float) $rows->sum('cost_basis');
                $marketValue = (float) $rows->sum('market_value');
                $pnl = $marketValue - $costBasis;
                $totalReturn = $costBasis > 0 ? $pnl / $costBasis : 0;

                $classExposure = $rows
                    ->groupBy('asset_class')
                    ->map(fn (Collection $items) => round((float) $items->sum('market_value'), 2));

                $weights = $rows
                    ->map(function ($row) use ($marketValue) {
                        if ($marketValue <= 0) {
                            return 0;
                        }

                        return (float) $row->market_value / $marketValue;
                    });

                $concentrationHhi = (float) $weights->reduce(fn ($carry, $w) => $carry + ($w * $w), 0);

                $portfolioSeries = collect($dailyValues->get($portfolioId, []))
                    ->pluck('daily_value')
                    ->map(fn ($value) => (float) $value)
                    ->values();

                $returns = collect();
                for ($index = 1; $index < $portfolioSeries->count(); $index++) {
                    $previous = $portfolioSeries[$index - 1];
                    $current = $portfolioSeries[$index];

                    if ($previous > 0) {
                        $returns->push(($current - $previous) / $previous);
                    }
                }

                $meanReturn = $returns->count() > 0 ? $returns->avg() : 0;
                $variance = $returns->count() > 1
                    ? $returns->reduce(fn ($carry, $r) => $carry + (($r - $meanReturn) ** 2), 0) / ($returns->count() - 1)
                    : 0;
                $dailyVolatility = $variance > 0 ? sqrt($variance) : 0;

                $annualizedReturn = $meanReturn * $tradingDays;
                $annualizedVolatility = $dailyVolatility * sqrt($tradingDays);
                $sharpe = $annualizedVolatility > 0
                    ? (($annualizedReturn - $annualRiskFreeRate) / $annualizedVolatility)
                    : null;

                $first = $rows->first();

                return [
                    'portfolio_id' => $portfolioId,
                    'portfolio_name' => $first->portfolio_name,
                    'owner' => [
                        'name' => $first->owner_name,
                        'email' => $first->owner_email,
                    ],
                    'metrics' => [
                        'cost_basis' => round($costBasis, 2),
                        'market_value' => round($marketValue, 2),
                        'pnl' => round($pnl, 2),
                        'return_pct' => round($totalReturn * 100, 2),
                        'annualized_volatility_pct' => round($annualizedVolatility * 100, 2),
                        'annualized_return_pct' => round($annualizedReturn * 100, 2),
                        'sharpe_ratio' => $sharpe !== null ? round($sharpe, 4) : null,
                        'concentration_hhi' => round($concentrationHhi, 4),
                    ],
                    'asset_exposure' => $classExposure,
                ];
            })
            ->values();

        $payload = [
            'is_admin' => $isAdmin,
            'context' => [
                'lookback_days' => $lookbackDays,
                'annual_risk_free_rate' => $annualRiskFreeRate,
                'trading_days_per_year' => $tradingDays,
            ],
            'portfolios' => $report,
        ];

        if ($request->query('format') === 'json' || $request->expectsJson()) {
            return response()->json($payload);
        }

        return view('reports.finance-portfolios', $payload);
    }
}
