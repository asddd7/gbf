<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfCharacterStage;
use App\Models\GbfMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GbfMaterialController extends Controller
{
    public function index(): View
    {
        $materials = GbfMaterial::query()
            ->with('stageMaterials.stage.character')
            ->withCount('stageMaterials')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.materials.index', compact('materials'));
    }

    public function create(): View
    {
        $stages = $this->availableStages();

        return view('admin.materials.create', compact('stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMaterial($request);
        $stageIds = $validated['stage_ids'] ?? [];
        unset($validated['stage_ids'], $validated['quantities'], $validated['notes']);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        DB::transaction(function () use ($validated, $request, $stageIds): void {
            $material = GbfMaterial::create($validated);
            $this->syncStageMaterials($material, $stageIds, $request);
        });

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    public function edit(GbfMaterial $material): View
    {
        $material->load('stageMaterials');
        $stages = $this->availableStages();

        return view('admin.materials.edit', compact('material', 'stages'));
    }

    public function update(Request $request, GbfMaterial $material): RedirectResponse
    {
        $validated = $this->validateMaterial($request, $material);
        $stageIds = $validated['stage_ids'] ?? [];
        unset($validated['stage_ids'], $validated['quantities'], $validated['notes']);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name'],
            $material->id
        );

        DB::transaction(function () use ($validated, $material, $request, $stageIds): void {
            $material->update($validated);
            $this->syncStageMaterials($material, $stageIds, $request);
        });

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(GbfMaterial $material): RedirectResponse
    {
        $material->delete();

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Material berhasil dihapus.');
    }

    private function availableStages()
    {
        return GbfCharacterStage::query()
            ->with('character')
            ->orderBy('character_id')
            ->orderBy('sort_order')
            ->get();
    }

    private function validateMaterial(
        Request $request,
        ?GbfMaterial $material = null
    ): array {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('gbf_materials', 'slug')->ignore($material?->id),
            ],
            'category' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:30'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'stage_ids' => ['nullable', 'array'],
            'stage_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:gbf_character_stages,id',
            ],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string'],
        ]);
    }

    private function syncStageMaterials(
        GbfMaterial $material,
        array $stageIds,
        Request $request
    ): void {
        if ($stageIds === []) {
            $material->stageMaterials()->delete();

            return;
        }

        $material->stageMaterials()
            ->whereNotIn('character_stage_id', $stageIds)
            ->delete();

        foreach ($stageIds as $stageId) {
            $material->stageMaterials()->updateOrCreate(
                ['character_stage_id' => $stageId],
                [
                    'quantity' => $request->input("quantities.{$stageId}", 1),
                    'notes' => $request->input("notes.{$stageId}"),
                ]
            );
        }
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value) ?: 'material';
        $slug = $baseSlug;
        $counter = 1;

        while (
            GbfMaterial::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
