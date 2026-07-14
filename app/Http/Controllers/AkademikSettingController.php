<?php

namespace App\Http\Controllers;

use App\Models\AkademikSetting;
use App\Models\LogAktivita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkademikSettingController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $settings = AkademikSetting::where('lembaga_id', $lembagaId)->get()->keyBy('kunci');

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $hariEfektif = AkademikSetting::getHariEfektif($lembagaId);
        $kegiatanList = AkademikSetting::getKegiatanList($lembagaId);

        return view('akademik-settings.index', compact('settings', 'hariList', 'hariEfektif', 'kegiatanList'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $validated = $request->validate([
            'jam_mulai' => 'required|date_format:H:i',
            'durasi_jam_kbm' => 'required|integer|min:15|max:120',
            'durasi_istirahat' => 'required|integer|min:5|max:120',
            'hari_efektif' => 'required|array|min:1',
            'hari_efektif.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'kegiatan_nama' => 'nullable|array',
            'kegiatan_durasi' => 'nullable|array',
            'kegiatan_nama.*' => 'nullable|string|max:255',
            'kegiatan_durasi.*' => 'nullable|integer|min:5|max:120',
        ]);

        AkademikSetting::setSetting($lembagaId, AkademikSetting::KUNCI_JAM_MULAI, $validated['jam_mulai'], 'Jam Mulai');
        AkademikSetting::setSetting($lembagaId, AkademikSetting::KUNCI_DURASI_JAM_KBM, (string) $validated['durasi_jam_kbm'], 'Durasi Per Jam KBM');
        AkademikSetting::setSetting($lembagaId, AkademikSetting::KUNCI_DURASI_ISTIRAHAT, (string) $validated['durasi_istirahat'], 'Durasi Istirahat');
        AkademikSetting::setHariEfektif($lembagaId, $validated['hari_efektif']);

        // Kegiatan list
        $kegiatan = [];
        $namaList = $request->input('kegiatan_nama', []);
        $durasiList = $request->input('kegiatan_durasi', []);
        foreach ($namaList as $i => $nama) {
            if (!empty($nama) && !empty($durasiList[$i])) {
                $kegiatan[] = [
                    'nama' => $nama,
                    'durasi_menit' => (int) $durasiList[$i],
                ];
            }
        }
        AkademikSetting::setKegiatanList($lembagaId, $kegiatan);

        LogAktivita::log('update', 'Mengupdate pengaturan akademik');

        return redirect()->route('akademik-settings.index')->with('success', 'Pengaturan akademik berhasil disimpan.');
    }

    // === TIMETABLE (Drag-n-Drop) ===

    public function timetable(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $jamMulai = AkademikSetting::getSetting($lembagaId, AkademikSetting::KUNCI_JAM_MULAI, '07:00');
        $durasiKbm = (int) AkademikSetting::getSetting($lembagaId, AkademikSetting::KUNCI_DURASI_JAM_KBM, 45);
        $durasiIstirahat = (int) AkademikSetting::getSetting($lembagaId, AkademikSetting::KUNCI_DURASI_ISTIRAHAT, 30);
        $kegiatanList = AkademikSetting::getKegiatanList($lembagaId);
        $hariEfektif = AkademikSetting::getHariEfektif($lembagaId);
        $timetable = AkademikSetting::getAllTimetableWithTimes($lembagaId);

        return view('akademik-settings.timetable', compact(
            'jamMulai',
            'durasiKbm',
            'durasiIstirahat',
            'kegiatanList',
            'hariEfektif',
            'timetable'
        ));
    }

    public function saveTimetable(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $hariEfektif = AkademikSetting::getHariEfektif($lembagaId);
        $data = $request->input('timetable', []);

        foreach ($hariEfektif as $hari) {
            $items = $data[$hari] ?? [];
            $cleaned = [];
            foreach ($items as $item) {
                if (is_string($item)) {
                    $item = json_decode($item, true);
                }
                if (!$item)
                    continue;
                if (!empty($item['tipe']) && !empty($item['label'])) {
                    $cleaned[] = [
                        'tipe' => $item['tipe'],
                        'label' => $item['label'],
                        'durasi_menit' => (int) ($item['durasi_menit'] ?? 0),
                    ];
                }
            }
            AkademikSetting::setTimetable($lembagaId, $hari, $cleaned);
        }

        LogAktivita::log('update', 'Menyusun timetable akademik');

        return redirect()->route('akademik-settings.timetable')->with('success', 'Timetable berhasil disimpan.');
    }
}
