<?php

return [
    'risk_free_rate_annual' => (float) env('FIN_RISK_FREE_RATE_ANNUAL', 0.08),
    'trading_days_per_year' => (int) env('FIN_TRADING_DAYS_PER_YEAR', 252),
    'lookback_days' => (int) env('FIN_LOOKBACK_DAYS', 30),
];
