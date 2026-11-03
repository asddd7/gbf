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
        $progressionCharacter = null;
        $progressionRows = [];
        $stages = $this->availableStages();
        $materials = GbfMaterial::query()
            ->orderBy('name')
            ->get(['id', 'name', 'unit']);

        return view('admin.progression.create', compact(
            'characters',
            'progressionCharacter',
            'progressionRows',
            'stages',
            'materials'
        ));
    }

    public function show(GbfCharacter $character): View
    {
        $character->load([
            'stages.materials.material',
        ]);

        abort_if($character->stages->isEmpty(), 404);

        return view('admin.progression.show', compact('character'));
    }

    public function edit(GbfCharacter $character): View
    {
        $character->load(['stages.materials.material']);
        abort_if($character->stages->isEmpty(), 404);

        $characters = collect();
        $progressionCharacter = $character;
        $progressionRows = $character->stages->map(fn (GbfCharacterStage $stage) => [
            'stage_id' => $stage->id,
            'materials' => $stage->materials->map(fn ($row) => [
                'material_id' => $row->material_id,
                'quantity' => $row->quantity,
            ])->values()->all(),
        ])->values()->all();
        $stages = $this->availableStages($character);
        $materials = GbfMaterial::query()
            ->orderBy('name')
            ->get(['id', 'name', 'unit']);

        return view('admin.progression.edit', compact(
            'characters',
            'progressionCharacter',
            'progressionRows',
            'stages',
            'materials'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProgression($request);

        $character = GbfCharacter::findOrFail($validated['character_id']);
        $this->saveStages($character, $validated['stages']);

        return redirect()
            ->route('admin.progression.index')
            ->with('success', 'Pengaturan progression karakter berhasil disimpan.');
    }

    public function update(Request $request, GbfCharacter $character): RedirectResponse
    {
        $validated = $this->validateProgression($request, allowEmptyStages: true);
        $this->saveStages($character, $validated['stages'] ?? []);

        if (empty($validated['stages'])) {
            return redirect()
                ->route('admin.progression.index')
                ->with('success', 'Semua tahap berhasil dilepas dari karakter.');
        }

        return redirect()
            ->route('admin.progression.show', $character)
            ->with('success', 'Pengaturan progression karakter berhasil diperbarui.');
    }

    private function validateProgression(
        Request $request,
        bool $allowEmptyStages = false
    ): array {
        $rules = [
            'stages' => $allowEmptyStages
                ? ['nullable', 'array']
                : ['required', 'array', 'min:1'],
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
        ];

        if (! $allowEmptyStages) {
            $rules['character_id'] = ['required', 'integer', 'exists:gbf_characters,id'];
        }

        return $request->validate($rules);
    }

    private function saveStages(GbfCharacter $character, array $stageRows): void
    {
        DB::transaction(function () use ($character, $stageRows): void {
            $stageIds = collect($stageRows)
                ->pluck('stage_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $stages = $stageIds === []
                ? collect()
                : GbfCharacterStage::query()
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

                if ($stage->character_id !== null && (int) $stage->character_id !== $character->id) {
                    throw ValidationException::withMessages([
                        "stages.{$index}.stage_id" => 'Tahap ini sudah digunakan oleh karakter lain.',
                    ]);
                }
            }

            $characterStages = $character->stages();
            if ($stageIds === []) {
                $characterStages->update(['character_id' => null]);
            } else {
                $characterStages->whereNotIn('id', $stageIds)
                    ->update(['character_id' => null]);
            }

            foreach ($stageRows as $stageRow) {
                $stage = $stages->get((int) $stageRow['stage_id']);
                if ($stage->character_id === null) {
                    $stage->update(['character_id' => $character->id]);
                }

                $materialRows = $stageRow['materials'];
                $materialIds = collect($materialRows)
                    ->pluck('material_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $stage->materials()
                    ->whereNotIn('material_id', $materialIds)
                    ->delete();

                foreach ($materialRows as $materialRow) {
                    $stage->materials()->updateOrCreate(
                        ['material_id' => $materialRow['material_id']],
                        ['quantity' => $materialRow['quantity']]
                    );
                }
            }
        });
    }

    private function availableStages(?GbfCharacter $character = null)
    {
        return GbfCharacterStage::query()
            ->with('character:id,name')
            ->when($character, function ($query) use ($character): void {
                $query->where(function ($query) use ($character): void {
                    $query->whereNull('character_id')
                        ->orWhere('character_id', $character->id);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'character_id', 'name', 'sort_order']);
    }
}
