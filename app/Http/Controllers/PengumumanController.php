<?php

namespace App\Http\Controllers;

use App\Models\LogAktivita;
use App\Models\Pengumuman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $query = Pengumuman::with('creator')
            ->forLembaga($lembagaId)
            ->latest();

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $pengumumans = $query->paginate($perPage)->appends($request->except('page'));

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('pengumuman._table', compact('pengumumans'))->render();
            return response()->json(['html' => $html, 'pagination' => $pengumumans->links()->toHtml()]);
        }

        return view('pengumuman.index', compact('pengumumans'));
    }

    public function create(): View
    {
        return view('pengumuman.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'konten_json' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['lembaga_id'] = $user->lembaga_id;
        $validated['created_by'] = $user->id;
        $validated['published_at'] = $validated['is_active'] ?? true ? now() : null;

        $pengumuman = Pengumuman::create($validated);

        LogAktivita::log('create', 'Menambah pengumuman "' . $pengumuman->judul . '"', $pengumuman);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Pengumuman $pengumuman): View
    {
        return view('pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, Pengumuman $pengumuman): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'konten_json' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['published_at'] = ($validated['is_active'] ?? false) && !$pengumuman->published_at
            ? now()
            : $pengumuman->published_at;

        $pengumuman->update($validated);

        LogAktivita::log('update', 'Mengupdate pengumuman "' . $pengumuman->judul . '"', $pengumuman);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus pengumuman "' . $pengumuman->judul . '"', $pengumuman);
        $pengumuman->delete();
        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * API: get active pengumuman for guru dashboard popup
     */
    public function popup(): JsonResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $pengumuman = Pengumuman::active()
            ->forLembaga($lembagaId)
            ->latest('published_at')
            ->first();

        if (!$pengumuman) {
            return response()->json(['has_pengumuman' => false]);
        }

        return response()->json([
            'has_pengumuman' => true,
            'id' => $pengumuman->id,
            'judul' => $pengumuman->judul,
            'konten' => $pengumuman->konten,
            'published_at' => $pengumuman->published_at?->diffForHumans(),
        ]);
    }

    /**
     * API: mark pengumuman as read by guru
     */
    public function markRead(Pengumuman $pengumuman): JsonResponse
    {
        session()->put("pengumuman_read_{$pengumuman->id}", true);
        return response()->json(['ok' => true]);
    }

    /**
     * Upload image for Editor.js Image Tool — by file
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $file = $request->file('image');
        $path = $file->store('pengumuman', 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'success' => 1,
            'file' => ['url' => $url],
        ]);
    }

    /**
     * Upload image for Editor.js Image Tool — by URL
     */
    public function uploadImageByUrl(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        return response()->json([
            'success' => 1,
            'file' => ['url' => $request->url],
        ]);
    }
}
