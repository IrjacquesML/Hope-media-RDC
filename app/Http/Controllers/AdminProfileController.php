<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * « Mon profil » : chaque compte modifie son nom, son e-mail et son mot de passe.
 */
class AdminProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [], ['name' => 'nom', 'email' => 'e-mail']);

        $user->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
        ], ['current_password' => 'mot de passe actuel', 'password' => 'nouveau mot de passe']);

        Auth::user()->update(['password' => $request->input('password')]);
        $request->session()->regenerate();

        return back()->with('success', 'Mot de passe modifié.');
    }
}
