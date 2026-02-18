<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->orderByDesc('id')
            ->get();

        $payload = [
            'users' => $users,
            'summary' => [
                'total' => $users->count(),
                'admins' => $users->where('role', 'admin')->count(),
                'regular_users' => $users->where('role', 'user')->count(),
            ],
        ];

        if ($request->query('format') === 'json' || $request->expectsJson()) {
            return response()->json($payload);
        }

        return view('admin.users-index', $payload);
    }

    public function destroy(Request $request, User $user): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $authUser = $request->attributes->get('auth_user');

        if ($authUser && $authUser->id === $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Você não pode remover sua própria conta.');
        }

        if ($user->role === 'admin' && User::query()->where('role', 'admin')->count() <= 1) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Não é permitido remover o último administrador.');
        }

        $deletedUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $user->delete();

        if ($request->query('format') === 'json' || $request->expectsJson()) {
            return response()->json([
                'message' => 'Usuário removido com sucesso.',
                'deleted_user' => $deletedUser,
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuário removido com sucesso.');
    }
}
