<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>
        <style>
            :root {
                color-scheme: light;
            }

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
                max-width: 400px;
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 24px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            }

            h1 {
                font-size: 1.5rem;
                margin-bottom: 8px;
            }

            p {
                color: #4b5563;
                margin-bottom: 20px;
                font-size: 0.95rem;
            }

            .status {
                margin-bottom: 16px;
                padding: 10px 12px;
                border-radius: 8px;
                background: #fef3c7;
                border: 1px solid #fcd34d;
                color: #92400e;
                font-size: 0.9rem;
            }

            form {
                display: grid;
                gap: 14px;
            }

            label {
                display: block;
                margin-bottom: 6px;
                font-size: 0.9rem;
                color: #111827;
            }

            input {
                width: 100%;
                height: 42px;
                padding: 0 12px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                font-size: 0.95rem;
            }

            input:focus {
                outline: 2px solid #93c5fd;
                border-color: #3b82f6;
            }

            .field-error {
                margin-top: 6px;
                font-size: 0.82rem;
                color: #b91c1c;
            }

            .hint {
                margin-top: 6px;
                color: #6b7280;
                font-size: 0.82rem;
                line-height: 1.4;
            }

            button {
                height: 42px;
                border: none;
                border-radius: 8px;
                background: #111827;
                color: #ffffff;
                font-size: 0.95rem;
                cursor: pointer;
            }

            button:hover {
                background: #1f2937;
            }

            .helper {
                margin-top: 14px;
                text-align: center;
                font-size: 0.9rem;
                color: #4b5563;
            }

            .helper a {
                color: #111827;
                font-weight: 600;
                text-decoration: none;
            }

            .helper a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <main class="card">
            <h1>Entrar</h1>
            <p>Faça login para acessar o sistema.</p>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="status" style="background: #fee2e2; border-color: #fca5a5; color: #991b1b;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div>
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" placeholder="voce@empresa.com" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password">Senha</label>
                    <input id="password" name="password" type="password" placeholder="********" required>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                    <div class="hint">Use sua senha com pelo menos 8 caracteres.</div>
                </div>

                <button type="submit">Entrar</button>
            </form>

            <p class="helper">
                Ainda não tem conta?
                <a href="{{ route('register') }}">Crie sua conta</a>
            </p>
        </main>
    </body>
</html>
