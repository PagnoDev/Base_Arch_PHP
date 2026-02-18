<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $token = $request->cookie('auth_token');

        if (! $token) {
            return redirect()->route('login')->with('status', 'Faça login para continuar.');
        }

        try {
            $decoded = JWT::decode(
                $token,
                new Key((string) config('jwt.secret'), (string) config('jwt.algorithm', 'HS256'))
            );
        } catch (ExpiredException) {
            return redirect()
                ->route('login')
                ->withCookie(cookie()->forget('auth_token'))
                ->with('status', 'Sua sessão expirou. Faça login novamente.');
        } catch (\Throwable) {
            return redirect()
                ->route('login')
                ->withCookie(cookie()->forget('auth_token'))
                ->with('status', 'Sessão inválida. Faça login novamente.');
        }

        $userId = isset($decoded->sub) ? (int) $decoded->sub : null;

        if (! $userId) {
            return redirect()
                ->route('login')
                ->withCookie(cookie()->forget('auth_token'))
                ->with('status', 'Token inválido.');
        }

        $user = User::query()->find($userId);

        if (! $user) {
            return redirect()
                ->route('login')
                ->withCookie(cookie()->forget('auth_token'))
                ->with('status', 'Usuário não encontrado.');
        }

        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
