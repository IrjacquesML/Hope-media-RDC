<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Gestion des comptes d'administration (super administrateur).
 */
class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderByRaw("CASE role WHEN 'super_admin' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")->orderBy('name');

        if ($search = $request->get('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'roles' => User::ROLES,
            'stats' => [
                'total'    => User::count(),
                'active'   => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => 'editor', 'is_active' => true]), 'roles' => User::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'      => ['required', Rule::in(array_keys(User::ROLES))],
            'password'  => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'is_active' => ['nullable', 'boolean'],
        ], [], $this->attributes());

        $data['is_active'] = $request->boolean('is_active');
        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', "Compte de {$data['name']} créé. Communiquez-lui son e-mail et son mot de passe ; il pourra le changer dans « Mon profil ».");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => User::ROLES]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in(array_keys(User::ROLES))],
            'password'  => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'is_active' => ['nullable', 'boolean'],
        ], [], $this->attributes());

        $data['is_active'] = $request->boolean('is_active');

        // Protections : on ne peut ni se rétrograder ni se désactiver soi-même,
        // et il reste toujours au moins un super administrateur actif.
        if ($user->is(Auth::user()) && ($data['role'] !== $user->role || !$data['is_active'])) {
            return back()->withInput()->with('error', 'Vous ne pouvez pas modifier votre propre rôle ni désactiver votre propre compte. Demandez-le à un autre super administrateur.');
        }
        if ($user->isSuperAdmin() && ($data['role'] !== 'super_admin' || !$data['is_active']) && $this->activeSuperAdmins() <= 1) {
            return back()->withInput()->with('error', 'Il doit rester au moins un super administrateur actif.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', "Compte de {$user->name} mis à jour.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(Auth::user())) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        if ($user->isSuperAdmin() && $this->activeSuperAdmins() <= 1) {
            return back()->with('error', 'Impossible de supprimer le dernier super administrateur.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "Compte de {$name} supprimé.");
    }

    private function activeSuperAdmins(): int
    {
        return User::where('role', 'super_admin')->where('is_active', true)->count();
    }

    private function attributes(): array
    {
        return ['name' => 'nom', 'email' => 'e-mail', 'role' => 'rôle', 'password' => 'mot de passe'];
    }
}
