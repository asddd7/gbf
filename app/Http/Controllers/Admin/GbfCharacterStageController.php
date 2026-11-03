<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GbfCharacterStageController extends Controller
{
    public function index(): View
    {
        $stages = GbfCharacterStage::query()
            ->with('character')
            ->withCount('materials')
            ->orderBy('character_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.stages.index', compact('stages'));
    }

    public function create(): View
    {
        $characters = $this->availableCharacters();

        return view('admin.stages.create', compact('characters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStage($request);
        GbfCharacterStage::create($validated);

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Tahap karakter berhasil ditambahkan.');
    }

    public function edit(GbfCharacterStage $stage): View
    {
        $characters = $this->availableCharacters();

        return view('admin.stages.edit', compact('stage', 'characters'));
    }

    public function update(
        Request $request,
        GbfCharacterStage $stage
    ): RedirectResponse {
        $stage->update($this->validateStage($request, $stage));

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Tahap karakter berhasil diperbarui.');
    }

    public function destroy(GbfCharacterStage $stage): RedirectResponse
    {
        $stage->delete();

        return redirect()
            ->route('admin.stages.index')
            ->with('success', 'Tahap karakter berhasil dihapus.');
    }

    private function availableCharacters()
    {
        return GbfCharacter::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function validateStage(
        Request $request,
        ?GbfCharacterStage $stage = null
    ): array {
        return $request->validate([
            'character_id' => ['required', 'integer', 'exists:gbf_characters,id'],
            'stage_key' => [
                'required',
                'string',
                'max:80',
                Rule::unique('gbf_character_stages', 'stage_key')
                    ->where('character_id', $request->input('character_id'))
                    ->ignore($stage?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}
