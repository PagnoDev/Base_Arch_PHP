<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Usuários e Roles</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }
            body { background: #f3f4f6; padding: 24px; color: #111827; }
            .container { max-width: 1000px; margin: 0 auto; display: grid; gap: 16px; }
            .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; }
            h1 { font-size: 1.6rem; margin-bottom: 10px; }
            .links { display: flex; gap: 10px; flex-wrap: wrap; }
            .links a { background: #374151; color: #fff; text-decoration: none; border-radius: 8px; padding: 10px 12px; font-size: .9rem; }
            table { width: 100%; border-collapse: collapse; font-size: .92rem; }
            th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
            th { background: #f9fafb; }
            .badge { padding: 4px 8px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
            .badge.admin { background: #dbeafe; color: #1d4ed8; }
            .badge.user { background: #e5e7eb; color: #1f2937; }
            .muted { color: #6b7280; font-size: .9rem; }
            .status { margin-top: 12px; padding: 10px 12px; border-radius: 8px; background: #ecfeff; border: 1px solid #a5f3fc; color: #155e75; font-size: .9rem; }
            .danger { background: #b91c1c; color: #fff; border: none; border-radius: 8px; padding: 8px 10px; font-size: .82rem; cursor: pointer; }
            .danger:hover { background: #991b1b; }
            .danger:disabled { background: #9ca3af; cursor: not-allowed; }
        </style>
    </head>
    <body>
        <main class="container">
            <section class="card">
                <h1>Usuários Cadastrados e Roles</h1>
                <p class="muted">Total: {{ $summary['total'] }} | Admins: {{ $summary['admins'] }} | Usuários: {{ $summary['regular_users'] }}</p>
                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif
                <div class="links" style="margin-top: 12px;">
                    <a href="{{ route('welcome') }}">Voltar para Bem-vindo</a>
                    <a href="{{ route('admin.users.index', ['format' => 'json']) }}">Ver JSON</a>
                </div>
            </section>

            <section class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Role</th>
                            <th>Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'admin' : 'user' }}">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Nenhum usuário cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </body>
</html>
