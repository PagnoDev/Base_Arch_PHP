<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bem-vindo</title>
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
                font-family: Arial, Helvetica, sans-serif;
            }

            body {
                min-height: 100vh;
                display: grid;
                place-items: center;
                background: #f3f4f6;
                padding: 16px;
            }

            .card {
                width: 100%;
                max-width: 560px;
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 24px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            }

            h1 {
                font-size: 1.7rem;
                margin-bottom: 10px;
                color: #111827;
            }

            p {
                color: #374151;
                line-height: 1.5;
                margin-bottom: 16px;
            }

            .user {
                padding: 10px 12px;
                border-radius: 8px;
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                color: #1d4ed8;
                margin-bottom: 18px;
                font-size: 0.95rem;
            }

            button {
                height: 42px;
                border: none;
                border-radius: 8px;
                background: #111827;
                color: #ffffff;
                padding: 0 14px;
                cursor: pointer;
            }

            button:hover {
                background: #1f2937;
            }

            .actions {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-bottom: 16px;
            }

            .actions a {
                display: inline-block;
                height: 42px;
                line-height: 42px;
                padding: 0 14px;
                border-radius: 8px;
                text-decoration: none;
                background: #374151;
                color: #ffffff;
                font-size: 0.92rem;
            }

            .actions a:hover {
                background: #4b5563;
            }
        </style>
    </head>
    <body>
        <main class="card">
            <h1>Bem-vindo!</h1>
            <p>Seu login com JWT foi realizado com sucesso.</p>

            <div class="user">
                Usuário autenticado: <strong>{{ $user->name }}</strong> ({{ $user->email }})
            </div>

            <div class="actions">
                <a href="{{ route('reports.finance.portfolios') }}">Relatório Financeiro</a>
                <a href="{{ route('reports.orders.complex') }}">Relatório de Pedidos</a>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Sair</button>
            </form>
        </main>

    </body>
</html>
