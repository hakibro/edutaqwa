<?php

namespace App\Http\Controllers;

use App\Models\AgendaMengajar;
use App\Models\Jadwal;
use App\Models\LogAktivita;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class AgendaMengajarController extends Controller
{
    use PerPageTrait;

    /**
     * Riwayat agenda guru yang login.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guruId = $user->guru_id;

        $agendas = AgendaMengajar::with(['jadwal.mapel', 'jadwal.kelas', 'kelas'])
            ->where('guru_id', $guruId)
            ->when($request->filled('tanggal'), fn($q) => $q->where('tanggal', $request->tanggal))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate($this->perPage($request));

        // Cek jadwal hari ini untuk bisa selfie
        $today = Carbon::today();
        $jadwalHariIni = Jadwal::with(['mapel', 'kelas'])
            ->where('lembaga_id', $lembagaId)
            ->where('guru_id', $guruId)
            ->where('hari', $today->locale('id')->dayName)
            ->orderBy('jam_ke')
            ->get();

        return view('agenda-mengajar.index', compact('agendas', 'jadwalHariIni'));
    }

    /**
     * Form selfie untuk jadwal tertentu.
     */
    public function create(Request $request): View
    {
        $jadwalId = $request->get('jadwal_id');
        $jadwal = Jadwal::with(['mapel', 'kelas'])->findOrFail($jadwalId);

        return view('agenda-mengajar.create', compact('jadwal'));
    }

    /**
     * Simpan selfie agenda mengajar.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guruId = $user->guru_id;

        $validated = $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'foto_base64' => 'nullable|string',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
        ]);

        if (!$request->hasFile('foto') && !$request->filled('foto_base64')) {
            return back()->withInput()->withErrors(['foto' => 'Foto selfie wajib diisi.']);
        }

        $jadwal = Jadwal::with(['mapel', 'kelas'])->findOrFail($validated['jadwal_id']);

        // Verifikasi: jadwal milik guru yang login
        if ($jadwal->guru_id != $guruId) {
            return back()->with('error', 'Jadwal tidak sesuai.');
        }

        $today = Carbon::today();

        // Verifikasi: hari jadwal sesuai hari ini
        if ($jadwal->hari !== $today->locale('id')->dayName) {
            return back()->with('error', 'Selfie hanya bisa dilakukan pada hari sesuai jadwal (' . $jadwal->hari . ').');
        }

        // Hitung pertemuan ke- berapa
        $lastPertemuan = AgendaMengajar::where('jadwal_id', $jadwal->id)
            ->max('pertemuan_ke') ?? 0;
        $pertemuanKe = $lastPertemuan + 1;

        // Simpan foto (file upload atau base64 from camera)
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('agenda/' . $lembagaId . '/' . $jadwal->id, 'public');
        } else {
            // Decode base64
            $base64 = $request->foto_base64;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);
                if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                    return back()->with('error', 'Format foto tidak didukung.');
                }
                $base64 = base64_decode($base64);
                $filename = 'agenda/' . $lembagaId . '/' . $jadwal->id . '/' . uniqid() . '.' . $type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $base64);
                $path = $filename;
            } else {
                return back()->with('error', 'Data foto tidak valid.');
            }
        }

        $agenda = AgendaMengajar::create([
            'jadwal_id' => $jadwal->id,
            'guru_id' => $guruId,
            'kelas_id' => $jadwal->kelas_id,
            'pertemuan_ke' => $pertemuanKe,
            'tanggal' => $today->toDateString(),
            'jam_mulai' => Carbon::now()->format('H:i'),
            'foto_path' => $path,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'metadata' => json_encode([
                'mapel' => $jadwal->mapel->nama,
                'kelas' => $jadwal->kelas->nama,
                'jam_ke' => $jadwal->jam_ke,
                'hari' => $jadwal->hari,
            ]),
        ]);

        LogAktivita::log('create', 'Selfie agenda mengajar - ' . $jadwal->mapel->nama . ' ' . $jadwal->kelas->nama . ' pertemuan ke-' . $pertemuanKe);

        return redirect()->route('agenda-mengajar.index')
            ->with('success', 'Selfie berhasil disimpan. Pertemuan ke-' . $pertemuanKe);
    }

    /**
     * Detail agenda.
     */
    public function show(AgendaMengajar $agenda): View
    {
        $agenda->load(['jadwal.mapel', 'jadwal.kelas', 'kelas', 'guru', 'verifikator']);
        return view('agenda-mengajar.show', compact('agenda'));
    }

    /**
     * Monitoring agenda (Kurikulum / Kepala Lembaga).
     */
    public function monitoring(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $query = AgendaMengajar::with(['jadwal.mapel', 'jadwal.kelas', 'guru', 'kelas'])
            ->whereHas('jadwal', fn($q) => $q->where('lembaga_id', $lembagaId));

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }
        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified === '1');
        }

        $agendas = $query->orderByDesc('tanggal')->orderByDesc('created_at')->paginate($this->perPage($request));

        $gurus = \App\Models\Guru::where('lembaga_id', $lembagaId)
            ->where('is_approved', true)
            ->orderBy('nama')
            ->get();

        return view('agenda-mengajar.monitoring', compact('agendas', 'gurus'));
    }

    /**
     * Verifikasi agenda (Kurikulum / Kepala Lembaga).
     */
    public function verify(AgendaMengajar $agenda): RedirectResponse
    {
        $agenda->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        LogAktivita::log('verify', 'Verifikasi agenda mengajar ID ' . $agenda->id);

        return back()->with('success', 'Agenda berhasil diverifikasi.');
    }

    /**
     * Hapus agenda (guru sendiri).
     */
    public function destroy(AgendaMengajar $agenda): RedirectResponse
    {
        // Hapus file foto
        if ($agenda->foto_path) {
            Storage::disk('public')->delete($agenda->foto_path);
        }

        $agenda->delete();
        LogAktivita::log('delete', 'Menghapus agenda mengajar ID ' . $agenda->id);

        return redirect()->route('agenda-mengajar.index')->with('success', 'Agenda berhasil dihapus.');
    }
}
