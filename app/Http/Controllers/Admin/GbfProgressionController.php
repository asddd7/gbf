<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
use App\Models\GbfMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GbfProgressionController extends Controller
{
    public function index(): View
    {
        $characters = GbfCharacter::query()
            ->whereHas('stages')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.progression.index', compact('characters'));
    }

    public function create(): View
    {
        $characters = GbfCharacter::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $stages = GbfCharacterStage::query()
            ->with('character:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'character_id', 'name', 'sort_order']);
        $materials = GbfMaterial::query()
            ->orderBy('name')
            ->get(['id', 'name', 'unit']);

        return view('admin.progression.create', compact(
            'characters',
            'stages',
            'materials'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'character_id' => ['required', 'integer', 'exists:gbf_characters,id'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.stage_id' => [
                'required',
                'integer',
                'distinct',
                'exists:gbf_character_stages,id',
            ],
            'stages.*.materials' => [
                'required',
                'array',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_array($value)) {
                        return;
                    }

                    $materialIds = collect($value)
                        ->filter(fn ($row) => is_array($row) && isset($row['material_id']))
                        ->pluck('material_id')
                        ->map(fn ($id) => (string) $id)
                        ->all();

                    if (count($materialIds) !== count(array_unique($materialIds))) {
                        $fail('Item tidak boleh dipilih lebih dari sekali dalam satu tahap.');
                    }
                },
            ],
            'stages.*.materials.*.material_id' => [
                'required',
                'integer',
                'exists:gbf_materials,id',
            ],
            'stages.*.materials.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:4294967295',
            ],
        ]);

        DB::transaction(function () use ($validated): void {
            $characterId = (int) $validated['character_id'];
            $stageRows = $validated['stages'];
            $stageIds = collect($stageRows)
                ->pluck('stage_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $stages = GbfCharacterStage::query()
                ->whereIn('id', $stageIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($stages->count() !== count($stageIds)) {
                throw ValidationException::withMessages([
                    'stages' => 'Salah satu tahap tidak lagi tersedia. Silakan muat ulang halaman.',
                ]);
            }

            foreach ($stageRows as $index => $stageRow) {
                $stage = $stages->get((int) $stageRow['stage_id']);

                if ($stage->character_id !== null && (int) $stage->character_id !== $characterId) {
                    throw ValidationException::withMessages([
                        "stages.{$index}.stage_id" => 'Tahap ini sudah digunakan oleh karakter lain.',
                    ]);
                }

                if ($stage->character_id === null) {
                    $stage->update(['character_id' => $characterId]);
                }

                foreach ($stageRow['materials'] as $materialRow) {
                    $stage->materials()->updateOrCreate(
                        ['material_id' => $materialRow['material_id']],
                        ['quantity' => $materialRow['quantity']]
                    );
                }
            }
        });

        return redirect()
            ->route('admin.progression.index')
            ->with('success', 'Pengaturan progression karakter berhasil disimpan.');
    }
}
