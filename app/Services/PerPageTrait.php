<?php

namespace App\Services;

use Illuminate\Http\Request;

trait PerPageTrait
{
    protected function perPage(Request $request, int $default = 15): int
    {
        $perPage = (int) $request->input('per_page', $default);
        $allowed = [15, 30, 50, 100];

        return in_array($perPage, $allowed, true) ? $perPage : $default;
    }
}
