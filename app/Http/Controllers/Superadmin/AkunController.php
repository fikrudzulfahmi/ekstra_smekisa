<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AkunController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get()->map(function ($user) {
            return [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ];
        });

        return inertia('Superadmin/Akun/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role'     => ['required', Rule::in(['admin', 'pelatih', 'pembimbing'])],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return back()->with('success', "Akun {$validated['role']} berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        // Superadmin tidak bisa diubah dari sini
        if ($user->role === 'superadmin') {
            return back()->with('error', 'Akun superadmin tidak dapat diubah di sini.');
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return back()->with('success', 'Data akun berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->role === 'superadmin') {
            return back()->with('error', 'Password superadmin tidak dapat direset dari sini.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', "Password akun \"{$user->name}\" berhasil direset.");
    }

    public function destroy(User $user)
    {
        if ($user->role === 'superadmin') {
            return back()->with('error', 'Akun superadmin tidak dapat dihapus.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang login.');
        }

        $user->delete();

        return back()->with('success', "Akun \"{$user->name}\" berhasil dihapus.");
    }
}
