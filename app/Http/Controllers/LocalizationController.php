<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocalizationController extends Controller
{
    public function changeLocale(string $lang): RedirectResponse
    {
            session(['locale' => $lang]);

            return redirect()->back();
    }
}
