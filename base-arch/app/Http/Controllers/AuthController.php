<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->cookie('auth_token')) {
            return redirect()->route('welcome');
        }

        return view('auth.login');
    }

    public function showRegister(Request $request)
    {
        if ($request->cookie('auth_token')) {
            return redirect()->route('welcome');
        }

        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string', 'min:8', 'regex:/^\S+$/'],
        ], [
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'Informe a senha.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.regex' => 'A senha não pode conter espaços.',
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Credenciais inválidas.'])
                ->onlyInput('email');
        }

        $token = $this->createJwtToken($user);
        $ttlMinutes = (int) config('jwt.ttl_minutes', 120);

        return redirect()
            ->route('welcome')
            ->cookie(
                'auth_token',
                $token,
                $ttlMinutes,
                '/',
                null,
                request()->isSecure(),
                true,
                false,
                'Lax'
            )
            ->with('status', 'Login efetuado com sucesso.');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'regex:/^\S+$/',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'name.required' => 'Informe seu nome.',
            'name.min' => 'O nome deve ter pelo menos :min caracteres.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'Informe a senha.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.regex' => 'A senha não pode conter espaços.',
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $this->createJwtToken($user);
        $ttlMinutes = (int) config('jwt.ttl_minutes', 120);

        return redirect()
            ->route('welcome')
            ->cookie(
                'auth_token',
                $token,
                $ttlMinutes,
                '/',
                null,
                request()->isSecure(),
                true,
                false,
                'Lax'
            )
            ->with('status', 'Cadastro realizado com sucesso.');
    }

    public function logout(): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->withCookie(cookie()->forget('auth_token'))
            ->with('status', 'Você saiu da sessão.');
    }

    public function welcome(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        return view('welcome', ['user' => $user]);
    }

    private function createJwtToken(User $user): string
    {
        $now = now();
        $ttlMinutes = (int) config('jwt.ttl_minutes', 120);

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => $now->timestamp,
            'exp' => $now->addMinutes($ttlMinutes)->timestamp,
        ];

        return JWT::encode($payload, (string) config('jwt.secret'), (string) config('jwt.algorithm', 'HS256'));
    }
}
