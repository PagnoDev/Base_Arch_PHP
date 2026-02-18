<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Relatório de Pedidos</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }
            body { background: #f3f4f6; padding: 24px; color: #111827; }
            .container { max-width: 1100px; margin: 0 auto; display: grid; gap: 16px; }
            .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; }
            h1 { font-size: 1.6rem; margin-bottom: 10px; }
            h2 { font-size: 1.1rem; margin-bottom: 10px; }
            .links { display: flex; gap: 10px; flex-wrap: wrap; }
            .links a, .links button { background: #111827; color: #fff; border: none; text-decoration: none; border-radius: 8px; padding: 10px 12px; font-size: .9rem; cursor: pointer; }
            .links a.alt { background: #374151; }
            table { width: 100%; border-collapse: collapse; font-size: .92rem; }
            th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
            th { background: #f9fafb; }
            .muted { color: #6b7280; font-size: .9rem; }
        </style>
    </head>
    <body>
        <main class="container">
            <section class="card">
                <h1>Relatório de Pedidos (Query Complexa)</h1>
                <p class="muted">Pedidos pagos com agregações, joins e ranking de clientes.</p>
                <div class="links" style="margin-top: 12px;">
                    <a href="{{ route('welcome') }}" class="alt">Voltar para Bem-vindo</a>
                    <a href="{{ route('reports.finance.portfolios') }}" class="alt">Ir para Relatório Financeiro</a>
                    <a href="{{ route('reports.orders.complex', ['format' => 'json']) }}">Ver JSON</a>
                </div>
            </section>

            <section class="card">
                <h2>Resumo</h2>
                <p class="muted">Pedidos analisados: <strong>{{ $summary['paid_orders_analyzed'] }}</strong> | Clientes no ranking: <strong>{{ $summary['top_customers_count'] }}</strong></p>
            </section>

            <section class="card">
                <h2>Pedidos Pagos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Itens</th>
                            <th>Produtos únicos</th>
                            <th>Total calculado</th>
                            <th>Preço médio item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paid_orders as $order)
                            <tr>
                                <td>{{ $order->code }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->total_items }}</td>
                                <td>{{ $order->unique_products }}</td>
                                <td>R$ {{ number_format($order->calculated_total, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($order->average_item_price, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Sem dados para o critério atual.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="card">
                <h2>Top Clientes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>E-mail</th>
                            <th>Pedidos pagos</th>
                            <th>Receita bruta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($top_customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->paid_orders_count }}</td>
                                <td>R$ {{ number_format($customer->gross_revenue, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Sem dados para exibir.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </body>
</html>
