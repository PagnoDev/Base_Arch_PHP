<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registrar</title>
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
                max-width: 420px;
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
                background: #fee2e2;
                border: 1px solid #fca5a5;
                color: #991b1b;
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

            .strength {
                margin-top: 8px;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .strength.weak {
                color: #b91c1c;
            }

            .strength.medium {
                color: #b45309;
            }

            .strength.strong {
                color: #166534;
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
            <h1>Criar conta</h1>
            <p>Preencha os dados para se registrar no sistema.</p>

            @if ($errors->any())
                <div class="status">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}">
                @csrf
                <div>
                    <label for="name">Nome</label>
                    <input id="name" name="name" type="text" placeholder="Seu nome" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

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
                    <div class="hint">A senha deve ter no mínimo 8 caracteres, com letras maiúsculas, minúsculas, números, caracteres especiais e sem espaços.</div>
                    <div id="password-strength" class="strength weak">Força da senha: fraca</div>
                </div>

                <div>
                    <label for="password_confirmation">Confirmar senha</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="********" required>
                </div>

                <button type="submit">Registrar</button>
            </form>

            <p class="helper">
                Já possui conta?
                <a href="{{ route('login') }}">Entrar</a>
            </p>
        </main>
        <script>
            const passwordInput = document.getElementById('password');
            const strengthElement = document.getElementById('password-strength');

            const hasMinLength = (value) => value.length >= 8;
            const hasUpperAndLower = (value) => /[a-z]/.test(value) && /[A-Z]/.test(value);
            const hasNumber = (value) => /\d/.test(value);
            const hasSymbol = (value) => /[^A-Za-z0-9]/.test(value);
            const hasNoSpaces = (value) => !/\s/.test(value);

            const updateStrength = () => {
                const value = passwordInput.value;

                const score = [
                    hasMinLength(value),
                    hasUpperAndLower(value),
                    hasNumber(value),
                    hasSymbol(value),
                    hasNoSpaces(value),
                ].filter(Boolean).length;

                strengthElement.classList.remove('weak', 'medium', 'strong');

                if (score <= 2) {
                    strengthElement.classList.add('weak');
                    strengthElement.textContent = 'Força da senha: fraca';
                    return;
                }

                if (score <= 4) {
                    strengthElement.classList.add('medium');
                    strengthElement.textContent = 'Força da senha: média';
                    return;
                }

                strengthElement.classList.add('strong');
                strengthElement.textContent = 'Força da senha: forte';
            };

            passwordInput.addEventListener('input', updateStrength);
            updateStrength();
        </script>
    </body>
</html>
