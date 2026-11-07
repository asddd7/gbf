<?php

namespace App\Http\Controllers;

use App\Models\GbfCharacter;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $characterCounts = GbfCharacter::query()
            ->selectRaw('character_type, COUNT(*) as aggregate')
            ->groupBy('character_type')
            ->pluck('aggregate', 'character_type');

        return view('dashboard', compact('characterCounts'));
    }

    public function eternals(): View
    {
        return $this->category('eternal');
    }

    public function evokers(): View
    {
        return $this->category('evoker');
    }

    private function category(string $type): View
    {
        $characters = GbfCharacter::query()
            ->where('character_type', $type)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'character_type', 'element', 'description', 'image_path']);

        $category = $type === 'eternal'
            ? [
                'name' => 'Eternals',
                'description' => 'Jelajahi karakter Eternals dan pilih siapa yang ingin kamu bangun berikutnya.',
            ]
            : [
                'name' => 'Evokers',
                'description' => 'Jelajahi karakter Evokers dan pilih siapa yang ingin kamu bangun berikutnya.',
            ];

        return view('characters.index', compact('characters', 'category', 'type'));
    }
}
