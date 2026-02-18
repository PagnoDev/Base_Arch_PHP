<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Relatório Financeiro</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }
            body { background: #f3f4f6; padding: 24px; color: #111827; }
            .container { max-width: 1100px; margin: 0 auto; display: grid; gap: 16px; }
            .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; }
            h1 { font-size: 1.6rem; margin-bottom: 10px; }
            h2 { font-size: 1.1rem; margin-bottom: 10px; }
            .links { display: flex; gap: 10px; flex-wrap: wrap; }
            .links a { background: #111827; color: #fff; text-decoration: none; border-radius: 8px; padding: 10px 12px; font-size: .9rem; }
            .links a.alt { background: #374151; }
            table { width: 100%; border-collapse: collapse; font-size: .92rem; }
            th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
            th { background: #f9fafb; }
            .muted { color: #6b7280; font-size: .9rem; }
            ul { list-style: none; display: grid; gap: 6px; }
        </style>
    </head>
    <body>
        <main class="container">
            <section class="card">
                <h1>Relatório de Risco de Carteiras</h1>
                <p class="muted">
                    @if ($is_admin)
                        Métricas de retorno, volatilidade, Sharpe e concentração (HHI) por carteira (visão geral).
                    @else
                        Métricas de retorno, volatilidade, Sharpe e concentração (HHI) das suas carteiras.
                    @endif
                </p>
                <p class="muted" style="margin-top:8px;">Lookback: {{ $context['lookback_days'] }} dias | Taxa livre de risco anual: {{ number_format($context['annual_risk_free_rate'] * 100, 2, ',', '.') }}%</p>
                <div class="links" style="margin-top: 12px;">
                    <a href="{{ route('welcome') }}" class="alt">Voltar para Bem-vindo</a>
                    <a href="{{ route('reports.orders.complex') }}" class="alt">Ir para Relatório de Pedidos</a>
                    <a href="{{ route('reports.finance.portfolios', ['format' => 'json']) }}">Ver JSON</a>
                </div>
            </section>

            <section class="card">
                <h2>Carteiras</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Carteira</th>
                            <th>Dono</th>
                            <th>Retorno</th>
                            <th>Volatilidade (anual)</th>
                            <th>Sharpe</th>
                            <th>P&L</th>
                            <th>Concentração (HHI)</th>
                            <th>Exposição por classe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($portfolios as $portfolio)
                            <tr>
                                <td>{{ $portfolio['portfolio_name'] }}</td>
                                <td>{{ $portfolio['owner']['name'] }}<br><span class="muted">{{ $portfolio['owner']['email'] }}</span></td>
                                <td>{{ number_format($portfolio['metrics']['return_pct'], 2, ',', '.') }}%</td>
                                <td>{{ number_format($portfolio['metrics']['annualized_volatility_pct'], 2, ',', '.') }}%</td>
                                <td>{{ $portfolio['metrics']['sharpe_ratio'] ?? 'N/A' }}</td>
                                <td>R$ {{ number_format($portfolio['metrics']['pnl'], 2, ',', '.') }}</td>
                                <td>{{ number_format($portfolio['metrics']['concentration_hhi'], 4, ',', '.') }}</td>
                                <td>
                                    <ul>
                                        @foreach ($portfolio['asset_exposure'] as $class => $value)
                                            <li>{{ $class }}: R$ {{ number_format($value, 2, ',', '.') }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8">Sem dados de carteira.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </body>
</html>
