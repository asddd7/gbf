<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
use App\Models\GbfMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GbfProgressionController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'character_id' => ['nullable', 'integer', 'exists:gbf_characters,id'],
        ]);

        $characters = GbfCharacter::query()
            ->orderBy('name')
            ->get(['id', 'name', 'character_type', 'element']);

        $character = isset($validated['character_id'])
            ? GbfCharacter::query()
                ->with(['stages.materials.material'])
                ->findOrFail($validated['character_id'])
            : null;

        $materials = GbfMaterial::query()
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'category']);

        return view('admin.progression.index', compact(
            'characters',
            'character',
            'materials'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'character_id' => ['required', 'integer', 'exists:gbf_characters,id'],
            'stages' => ['nullable', 'array'],
            'stages.*.id' => [
                'nullable',
                'integer',
                'distinct',
                Rule::exists('gbf_character_stages', 'id')
                    ->where('character_id', $request->input('character_id')),
            ],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],
            'stages.*.materials' => [
                'nullable',
                'array',
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
                        $fail('Material tidak boleh dipilih lebih dari sekali dalam satu tahap.');
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

        $character = GbfCharacter::findOrFail($validated['character_id']);
        $stages = $validated['stages'] ?? [];

        DB::transaction(function () use ($character, $stages): void {
            $stageIds = collect($stages)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($stageIds === []) {
                $character->stages()->delete();
            } else {
                $character->stages()->whereNotIn('id', $stageIds)->delete();
            }

            foreach ($stages as $stageData) {
                $materialRows = $stageData['materials'] ?? [];
                unset($stageData['materials']);

                $stageId = $stageData['id'] ?? null;
                unset($stageData['id']);

                if ($stageId) {
                    $stage = $character->stages()
                        ->whereKey($stageId)
                        ->firstOrFail();
                    $stage->update($stageData);
                } else {
                    $stage = $character->stages()->create([
                        ...$stageData,
                        'stage_key' => $this->makeUniqueStageKey(
                            $character,
                            $stageData['name']
                        ),
                    ]);
                }

                $this->syncMaterials($stage, $materialRows);
            }
        });

        return redirect()
            ->route('admin.progression.index', [
                'character_id' => $character->id,
            ])
            ->with('success', 'Tahap dan kebutuhan material berhasil disimpan.');
    }

    private function syncMaterials(GbfCharacterStage $stage, array $materialRows): void
    {
        $materialIds = collect($materialRows)
            ->pluck('material_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($materialIds === []) {
            $stage->materials()->delete();

            return;
        }

        $stage->materials()
            ->whereNotIn('material_id', $materialIds)
            ->delete();

        foreach ($materialRows as $row) {
            $stage->materials()->updateOrCreate(
                ['material_id' => $row['material_id']],
                ['quantity' => $row['quantity']]
            );
        }
    }

    private function makeUniqueStageKey(GbfCharacter $character, string $name): string
    {
        $baseKey = Str::limit(Str::slug($name) ?: 'stage', 80, '');
        $stageKey = $baseKey;
        $counter = 1;

        while (
            GbfCharacterStage::query()
                ->where('character_id', $character->id)
                ->where('stage_key', $stageKey)
                ->exists()
        ) {
            $suffix = '-'.$counter;
            $stageKey = Str::limit($baseKey, 80 - strlen($suffix), '').$suffix;
            $counter++;
        }

        return $stageKey;
    }
}
