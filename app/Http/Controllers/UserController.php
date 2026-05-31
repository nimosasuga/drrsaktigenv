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
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $sort = $request->sort;
        $department = $request->department;
        $position = $request->position;

        $users = User::when($search, function ($query) use ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('nrpp', 'like', "%{$search}%")
                    ->orWhere('branch', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        })
        ->when($department, function ($query) use ($department) {
            $query->where('department', $department);
        })
        ->when($position, function ($query) use ($position) {
            $query->where('position', $position);
        })
        ->when($sort, function ($query) use ($sort) {
            if ($sort === 'az') {
                $query->orderBy('name', 'asc');
            } elseif ($sort === 'za') {
                $query->orderBy('name', 'desc');
            } else {
                $query->latest();
            }
        }, function ($query) {
            // Default jika sort tidak dipilih
            $query->latest();
        })
        ->paginate(10)
        ->withQueryString(); // Memastikan parameter URL terbawa saat pindah halaman (pagination)

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
            'status_user' => 'required|string|in:mekanik,koordinator,sect_head,admin,super_admin',
            'branch' => 'nullable|string',
            'position' => 'nullable|string|in:FIELD,FMC',
            'department' => 'nullable|string|in:RENTAL,SERVICE',
            'is_verified' => 'required|boolean',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->nrpp = $validated['nrpp'];
        $user->status_user = $validated['status_user'];
        $user->branch = $validated['branch'] ?? null;
        $user->position = $validated['position'] ?? null;
        $user->department = $validated['department'] ?? null;
        $user->is_verified = (bool) $validated['is_verified'];

        if ($user->is_verified) {
            $user->verified_at = now();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
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
            'status_user' => 'required|string|in:mekanik,koordinator,sect_head,admin,super_admin',
            'branch' => 'nullable|string',
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
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
