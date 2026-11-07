<?php

namespace App\Http\Controllers;

use App\Models\GbfCharacter;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $charactersByType = GbfCharacter::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'character_type', 'element', 'description', 'image_path'])
            ->groupBy('character_type');

        return view('dashboard', compact('charactersByType'));
    }
}
