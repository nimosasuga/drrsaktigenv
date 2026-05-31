<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Http/Controllers/UserController.php
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $users = User::when($search, function ($query) use ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('nrpp', 'like', "%{$search}%")
                    ->orWhere('branch', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        })
            ->latest()
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nrpp' => 'required|string|max:255|unique:users,nrpp',
            'password' => 'required|string|min:8',
            'status_user' => 'required|string|in:mekanik,koordinator,sect_head,super_admin',
            'branch' => 'nullable|string|max:255',
            'position' => 'nullable|string|in:FIELD,FMC',
            'department' => 'nullable|string|in:RENTAL,SERVICE',
            'is_verified' => 'required|boolean',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->nrpp = $validated['nrpp'];
        $user->password = Hash::make($validated['password']);
        $user->status_user = $validated['status_user'];
        $user->branch = $validated['branch'] ?? null;
        $user->position = $validated['position'] ?? null;
        $user->department = $validated['department'] ?? null;
        $user->is_verified = (bool) $validated['is_verified'];
        $user->verified_at = $user->is_verified ? now() : null;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nrpp' => 'required|string|max:255|unique:users,nrpp,' . $user->id,
            'status_user' => 'required|string|in:mekanik,koordinator,sect_head,super_admin',
            'branch' => 'nullable|string|max:255',
            'position' => 'nullable|string|in:FIELD,FMC',
            'department' => 'nullable|string|in:RENTAL,SERVICE',
            'is_verified' => 'required|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $newVerificationStatus = (bool) $validated['is_verified'];
        $oldVerificationStatus = (bool) $user->is_verified;

        $user->name = $validated['name'];
        $user->nrpp = $validated['nrpp'];
        $user->status_user = $validated['status_user'];
        $user->branch = $validated['branch'] ?? null;
        $user->position = $validated['position'] ?? null;
        $user->department = $validated['department'] ?? null;
        $user->is_verified = $newVerificationStatus;

        if ($newVerificationStatus && !$oldVerificationStatus) {
            $user->verified_at = now();
        }

        if (!$newVerificationStatus) {
            $user->verified_at = null;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (\Illuminate\Support\Facades\Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus dari sistem.');
    }
}
