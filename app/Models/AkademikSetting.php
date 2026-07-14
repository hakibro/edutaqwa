<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkademikSetting extends Model
{
    use HasFactory;

    protected $fillable = ['lembaga_id', 'kunci', 'nilai', 'label', 'urutan'];

    /** Kunci yang dikenal */
    public const KUNCI_JAM_MULAI = 'jam_mulai';
    public const KUNCI_DURASI_JAM_KBM = 'durasi_jam_kbm';
    public const KUNCI_DURASI_ISTIRAHAT = 'durasi_istirahat';
    public const KUNCI_KEGIATAN_LIST = 'kegiatan_list';
    public const KUNCI_HARI_EFEKTIF = 'hari_efektif';

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public static function getSetting(int $lembagaId, string $kunci, $default = null): ?string
    {
        return static::where('lembaga_id', $lembagaId)->where('kunci', $kunci)->value('nilai') ?? $default;
    }

    public static function setSetting(int $lembagaId, string $kunci, string $nilai, ?string $label = null, int $urutan = 0): void
    {
        static::updateOrCreate(
            ['lembaga_id' => $lembagaId, 'kunci' => $kunci],
            ['nilai' => $nilai, 'label' => $label, 'urutan' => $urutan]
        );
    }

    /** Hari efektif: simpan sebagai CSV */
    public static function getHariEfektif(int $lembagaId): array
    {
        $nilai = static::getSetting($lembagaId, self::KUNCI_HARI_EFEKTIF, 'Senin,Selasa,Rabu,Kamis,Jumat');
        return array_filter(explode(',', $nilai));
    }

    public static function setHariEfektif(int $lembagaId, array $hari): void
    {
        static::setSetting($lembagaId, self::KUNCI_HARI_EFEKTIF, implode(',', $hari), 'Hari Efektif');
    }

    /** Kegiatan list: JSON [{nama, durasi_menit}] */
    public static function getKegiatanList(int $lembagaId): array
    {
        $nilai = static::getSetting($lembagaId, self::KUNCI_KEGIATAN_LIST, '[]');
        return json_decode($nilai, true) ?: [];
    }

    public static function setKegiatanList(int $lembagaId, array $list): void
    {
        static::setSetting($lembagaId, self::KUNCI_KEGIATAN_LIST, json_encode($list), 'Daftar Kegiatan');
    }

    /** Prefix kunci untuk timetable per hari: timetable_{Senin} */
    public const PREFIX_TIMETABLE = 'timetable_';

    public static function getTimetable(int $lembagaId, string $hari): array
    {
        $nilai = static::getSetting($lembagaId, self::PREFIX_TIMETABLE . $hari, '[]');
        return json_decode($nilai, true) ?: [];
    }

    public static function setTimetable(int $lembagaId, string $hari, array $items): void
    {
        static::setSetting($lembagaId, self::PREFIX_TIMETABLE . $hari, json_encode($items), 'Jadwal ' . $hari);
    }

    /** Ambil semua timetable untuk hari efektif */
    public static function getAllTimetable(int $lembagaId): array
    {
        $hariEfektif = static::getHariEfektif($lembagaId);
        $result = [];
        foreach ($hariEfektif as $hari) {
            $result[$hari] = static::getTimetable($lembagaId, $hari);
        }
        return $result;
    }

    /**
     * Hitung jam_mulai & jam_selesai untuk setiap item di timetable suatu hari.
     * Returns: [['tipe','label','jam_mulai','jam_selesai','durasi_menit'], ...]
     */
    public static function getTimetableWithTimes(int $lembagaId, string $hari): array
    {
        $items = static::getTimetable($lembagaId, $hari);
        if (empty($items))
            return [];

        $jamMulai = static::getSetting($lembagaId, self::KUNCI_JAM_MULAI, '07:00');
        $current = \Carbon\Carbon::createFromFormat('H:i', $jamMulai);

        $result = [];
        $kbmCount = 0;
        foreach ($items as $item) {
            $durasi = (int) ($item['durasi_menit'] ?? 0);
            $jamMulaiStr = $current->format('H:i');
            $current->addMinutes($durasi);
            $jamSelesaiStr = $current->format('H:i');

            $label = $item['label'];
            // Auto-rename KBM items to Jam 1, Jam 2, ...
            if (($item['tipe'] ?? '') === 'kbm') {
                $kbmCount++;
                $label = 'Jam ' . $kbmCount;
            }

            $result[] = [
                'tipe' => $item['tipe'],
                'label' => $label,
                'jam_mulai' => $jamMulaiStr,
                'jam_selesai' => $jamSelesaiStr,
                'durasi_menit' => $durasi,
            ];
        }
        return $result;
    }

    /** Ambil semua timetable dengan waktu terhitung */
    public static function getAllTimetableWithTimes(int $lembagaId): array
    {
        $hariEfektif = static::getHariEfektif($lembagaId);
        $result = [];
        foreach ($hariEfektif as $hari) {
            $result[$hari] = static::getTimetableWithTimes($lembagaId, $hari);
        }
        return $result;
    }

    /**
     * Ambil hanya slot KBM dengan nomor urut KBM (1-based).
     * Returns: [['kbm_number','label','jam_mulai','jam_selesai','durasi_menit'], ...]
     */
    public static function getKbmItems(int $lembagaId, string $hari): array
    {
        $all = static::getTimetableWithTimes($lembagaId, $hari);
        $kbm = [];
        $n = 0;
        foreach ($all as $item) {
            if ($item['tipe'] === 'kbm') {
                $n++;
                $kbm[$n] = $item;
            }
        }
        return $kbm;
    }
}
