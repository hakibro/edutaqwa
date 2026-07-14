<?php

namespace App\Http\Controllers;

use App\Models\LogAktivita;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = LogAktivita::with('user');

        if ($user->isAdminYayasan()) {
            $query->where('yayasan_id', $user->yayasan_id);
        }

        $logs = $query->latest()->paginate(25);

        return view('log-aktivitas.index', compact('logs'));
    }
}
