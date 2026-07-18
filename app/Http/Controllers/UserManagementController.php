<?php

namespace App\Http\Controllers;

use App\Models\LogAktivita;
use App\Models\User;
use App\Models\Yayasan;
use App\Models\Lembaga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    use PerPageTrait;

    public function indexYayasan(Request $request, Yayasan $yayasan): View
    {
        $this->authorizeYayasanAccess($yayasan);

        $users = User::where('yayasan_id', $yayasan->id)
            ->whereNull('lembaga_id')
            ->latest()
            ->paginate($this->perPage($request));
        return view('users.index-yayasan', compact('yayasan', 'users'));
    }

    public function indexLembaga(Request $request, Lembaga $lembaga): View
    {
        $this->authorizeLembagaAccess($lembaga);

        $users = User::where('lembaga_id', $lembaga->id)->latest()->paginate($this->perPage($request));
        return view('users.index-lembaga', compact('lembaga', 'users'));
    }

    public function createYayasan(Yayasan $yayasan): View
    {
        $this->authorizeYayasanAccess($yayasan);

        return view('users.create-yayasan', compact('yayasan'));
    }

    public function createLembaga(Lembaga $lembaga): View
    {
        $this->authorizeLembagaAccess($lembaga);

        return view('users.create-lembaga', compact('lembaga'));
    }

    public function storeYayasan(Request $request, Yayasan $yayasan): RedirectResponse
    {
        $this->authorizeYayasanAccess($yayasan);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin_yayasan',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'yayasan_id' => $yayasan->id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        LogAktivita::log('create', 'Super admin menambah user "' . $user->name . '" (' . $user->role . ') untuk yayasan "' . $yayasan->nama . '"', $user);

        return redirect()->route('user-management.yayasan', $yayasan)
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function storeLembaga(Request $request, Lembaga $lembaga): RedirectResponse
    {
        $this->authorizeLembagaAccess($lembaga);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin_lembaga,kepala_lembaga,kurikulum,kesiswaan',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'lembaga_id' => $lembaga->id,
            'yayasan_id' => $lembaga->yayasan_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        LogAktivita::log('create', 'Super admin menambah user "' . $user->name . '" (' . $user->role . ') untuk lembaga "' . $lembaga->nama . '"', $user);

        return redirect()->route('user-management.lembaga', $lembaga)
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $this->authorizeUserAccess($user);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUserAccess($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => $user->isSuperAdmin() ? 'required|string|in:super_admin' : 'required|string|in:admin_yayasan,admin_lembaga,kepala_lembaga,kurikulum,kesiswaan,guru,siswa,orang_tua',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($validated['password']) {
            $data['password'] = bcrypt($validated['password']);
        }

        $user->update($data);

        $actor = auth()->user()->name;
        LogAktivita::log('update', $actor . ' mengupdate user "' . $user->name . '"', $user);

        $redirect = $user->lembaga_id
            ? route('user-management.lembaga', $user->lembaga_id)
            : route('user-management.yayasan', $user->yayasan_id);

        return redirect($redirect)->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeUserAccess($user);

        $redirect = $user->lembaga_id
            ? route('user-management.lembaga', $user->lembaga_id)
            : route('user-management.yayasan', $user->yayasan_id);

        $actor = auth()->user()->name;
        LogAktivita::log('delete', $actor . ' menghapus user "' . $user->name . '"', $user);
        $user->delete();

        return redirect($redirect)->with('success', 'User berhasil dihapus.');
    }

    /**
     * Admin yayasan only sees users in their yayasan. Super admin sees all.
     */
    private function authorizeYayasanAccess(Yayasan $yayasan): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin())
            return;
        abort_unless($user->isAdminYayasan() && $user->yayasan_id === $yayasan->id, 403);
    }

    /**
     * Admin yayasan only sees users in lembagas under their yayasan. Super admin sees all.
     */
    private function authorizeLembagaAccess(Lembaga $lembaga): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin())
            return;
        abort_unless($user->isAdminYayasan() && $user->yayasan_id === $lembaga->yayasan_id, 403);
    }

    /**
     * Admin yayasan can only edit/delete users within their yayasan. Super admin sees all.
     */
    private function authorizeUserAccess(User $target): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin())
            return;
        abort_unless($user->isAdminYayasan() && $target->yayasan_id === $user->yayasan_id, 403);
    }
}