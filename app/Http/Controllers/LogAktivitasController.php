<?php

namespace App\Http\Controllers;

use App\Models\LogAktivita;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = LogAktivita::with('user');

        if ($user->isAdminYayasan()) {
            $query->where('yayasan_id', $user->yayasan_id);
        }

        if ($user->isAdminLembaga()) {
            $query->where('lembaga_id', $user->lembaga_id);
        }

        $logs = $query->latest()->paginate($this->perPage($request));

        return view('log-aktivitas.index', compact('logs'));
    }
}
